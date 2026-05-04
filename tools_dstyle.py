from pathlib import Path
lines = Path("dashboard.php").read_text().splitlines()
for i,l in enumerate(lines):
    if "<style" in l:
        for j in range(i, min(len(lines), i+160)):
            print(f"{j+1}: {lines[j]}")
        break
