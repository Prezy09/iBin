<?php
namespace App\Controllers;

use Core\Controller;
use Throwable;

class AdminController extends Controller {

    public function index() {
        require_once __DIR__ . '/../../includes/auth.php';
        require_once __DIR__ . '/../../config/firebase.php';
        
        auth_require_master_admin();
        $isMasterAdmin = true;

        $errors = [];
        $success = '';
        $name = '';
        $email = '';
        $admins = [];
        $operators = [];
        $users = [];
        $listError = '';
        $requests = [];
        $requestsError = '';
        $requestMessage = '';
        $requestMessageType = '';
        $accountMessage = '';
        $accountMessageType = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['approve_request', 'reject_request'], true)) {
            $action = $_POST['action'];
            $requestId = trim($_POST['request_id'] ?? '');
            $reason = trim($_POST['reason'] ?? '');

            if ($requestId === '') {
                $requestMessageType = 'danger';
                $requestMessage = 'Invalid access request.';
            } else {
                try {
                    $request = firebase_access_request_get($requestId);
                    if (!$request) {
                        $requestMessageType = 'danger';
                        $requestMessage = 'Access request not found.';
                    } elseif (strcasecmp($request['status'] ?? '', 'Pending') !== 0) {
                        $requestMessageType = 'warning';
                        $requestMessage = 'This request has already been processed.';
                    } elseif ($action === 'reject_request') {
                        firebase_access_request_update_status($requestId, 'Rejected', [
                            'decided_by' => $_SESSION['user_email'] ?? '',
                            'reason' => $reason !== '' ? $reason : 'Rejected by master admin',
                        ]);
                        $requestMessageType = 'success';
                        $requestMessage = 'Access request rejected.';
                    } else {
                        $existing = firebase_users_find_by_email($request['email'] ?? '');
                        if ($existing) {
                            $requestMessageType = 'danger';
                            $requestMessage = 'An account with this email already exists.';
                        } elseif (empty($request['password_hash'])) {
                            $requestMessageType = 'danger';
                            $requestMessage = 'Cannot approve request: missing password.';
                        } else {
                            $userId = firebase_users_create([
                                'name' => $request['name'] ?? 'User',
                                'email' => $request['email'] ?? '',
                                'password_hash' => $request['password_hash'],
                                'role' => $request['role'] ?? 'User',
                                'status' => 'Active',
                                'created_at' => gmdate('c'),
                                'last_login_at' => null,
                            ]);
                            firebase_access_request_update_status($requestId, 'Approved', [
                                'decided_by' => $_SESSION['user_email'] ?? '',
                                'approved_user_id' => $userId,
                            ]);
                            $requestMessageType = 'success';
                            $requestMessage = 'Access request approved and account created.';
                        }
                    }
                } catch (Throwable $e) {
                    $requestMessageType = 'danger';
                    $requestMessage = 'Failed to process request: ' . $e->getMessage();
                }
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'delete_user') {
                $userId = trim($_POST['user_id'] ?? '');
                if ($userId === '') {
                    $accountMessageType = 'danger';
                    $accountMessage = 'Invalid user account.';
                } else {
                    try {
                        $user = firebase_users_get($userId);
                        if (!$user) {
                            $accountMessageType = 'warning';
                            $accountMessage = 'Account not found.';
                        } elseif (strcasecmp($user['email'] ?? '', auth_master_admin_email()) === 0) {
                            $accountMessageType = 'danger';
                            $accountMessage = 'Cannot delete the master admin account.';
                        } else {
                            firebase_users_delete($userId);
                            $accountMessageType = 'success';
                            $accountMessage = 'Account deleted successfully.';
                        }
                    } catch (Throwable $e) {
                        $accountMessageType = 'danger';
                        $accountMessage = 'Failed to delete account: ' . $e->getMessage();
                    }
                }
            } else {
                $name = trim($_POST['name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                $confirm = $_POST['confirm_password'] ?? '';

                if ($name === '') $errors[] = 'Name is required.';
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
                if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
                if ($password !== $confirm) $errors[] = 'Passwords do not match.';
                if (!firebase_is_ready()) $errors[] = 'Authentication service is unavailable.';

                if (empty($errors)) {
                    try {
                        $existing = firebase_users_find_by_email($email);
                        if ($existing) {
                            $errors[] = 'An account with this email already exists.';
                        } else {
                            $hash = password_hash($password, PASSWORD_DEFAULT);
                            firebase_users_create([
                                'name' => $name,
                                'email' => $email,
                                'password_hash' => $hash,
                                'role' => 'Admin',
                                'status' => 'Active',
                                'created_at' => gmdate('c'),
                                'last_login_at' => null,
                            ]);
                            $success = 'Admin account created successfully.';
                            $name = '';
                            $email = '';
                        }
                    } catch (Throwable $e) {
                        $errors[] = 'Failed to create admin: ' . $e->getMessage();
                    }
                }
            }
        }

        try {
            $allUsers = firebase_db_get(firebase_users_path());
            if (is_array($allUsers)) {
                foreach ($allUsers as $id => $user) {
                    if (!is_array($user)) continue;
                    $normalized = firebase_users_normalize($user, $id);
                    $roleValue = strtolower($normalized['role'] ?? '');
                    if ($roleValue === 'admin') {
                        $admins[] = $normalized;
                    } elseif ($roleValue === 'operator') {
                        $operators[] = $normalized;
                    } else {
                        $users[] = $normalized;
                    }
                }
                usort($admins, static function ($a, $b) {
                    return strcasecmp($a['email'] ?? '', $b['email'] ?? '');
                });
                usort($operators, static function ($a, $b) {
                    return strcasecmp($a['email'] ?? '', $b['email'] ?? '');
                });
                usort($users, static function ($a, $b) {
                    return strcasecmp($a['email'] ?? '', $b['email'] ?? '');
                });
            }
        } catch (Throwable $e) {
            $listError = $e->getMessage();
        }

        try {
            $requests = firebase_access_requests_pending();
        } catch (Throwable $e) {
            $requestsError = $e->getMessage();
        }

        $data = [
            'title' => 'Manage Accounts',
            'active' => 'manage_admins',
            'errors' => $errors,
            'success' => $success,
            'name' => $name,
            'email' => $email,
            'admins' => $admins,
            'operators' => $operators,
            'users' => $users,
            'listError' => $listError,
            'requests' => $requests,
            'requestsError' => $requestsError,
            'requestMessage' => $requestMessage,
            'requestMessageType' => $requestMessageType,
            'accountMessage' => $accountMessage,
            'accountMessageType' => $accountMessageType,
        ];

        $this->render('admin/index', $data);
    }
}
