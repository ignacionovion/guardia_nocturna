#!/usr/bin/env python3
import os, sys
BASE = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT = os.path.join(BASE, 'resources', 'views', 'admin', 'reports', 'attendance.blade.php')
parts_dir = os.path.join(BASE, 'scripts', 'attendance_parts')
os.makedirs(parts_dir, exist_ok=True)
files = sorted(f for f in os.listdir(parts_dir) if f.endswith('.txt'))
with open(OUT, 'w') as out:
    for f in files:
        with open(os.path.join(parts_dir, f)) as p:
            out.write(p.read())
print(f"Written {len(files)} parts to {OUT}")
