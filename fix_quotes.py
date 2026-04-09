#!/usr/bin/env python3
import re

# Lire le fichier
with open('admin-dashboard.html', 'r', encoding='utf-8') as f:
    lines = f.readlines()

errors = []
for i, line in enumerate(lines, 1):
    # Chercher les patterns problématiques
    if re.search(r'": "<i class="', line) or re.search(r'= "<i class="', line) or re.search(r'\? "<i class="', line):
        errors.append((i, line.strip()))

print(f"Nombre d'erreurs trouvées: {len(errors)}")
for line_num, content in errors:
    print(f"Ligne {line_num}: {content[:100]}")

