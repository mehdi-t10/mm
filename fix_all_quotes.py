#!/usr/bin/env python3
import re

# Lire le fichier
with open('admin-dashboard.html', 'r', encoding='utf-8') as f:
    content = f.read()

# Patterns d'erreurs à corriger
# Pattern 1: ": "<i class=" -> ': '<i class="
content = re.sub(r'": "<i class="', "': '<i class=\"", content)

# Pattern 2: ? "<i class=" -> ? '<i class="
content = re.sub(r'\? "<i class="', "? '<i class=\"", content)

# Pattern 3: = "<i class=" -> = '<i class="
content = re.sub(r'= "<i class="', "= '<i class=\"", content)

# Pattern 4: , "<i class=" -> , '<i class="
content = re.sub(r', "<i class="', ", '<i class=\"", content)

# Écrire le fichier corrigé
with open('admin-dashboard.html', 'w', encoding='utf-8') as f:
    f.write(content)

print("✅ Fichier corrigé!")

