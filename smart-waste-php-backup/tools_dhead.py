from pathlib import Path
lines = Path("dashboard.php").read_text().splitlines()
for i in range(0, min(len(lines), 80)):
    print(f"{i+1}: {lines[i]}")
