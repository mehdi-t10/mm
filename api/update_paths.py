#!/usr/bin/env python3
import os
import glob

# Directories to process
subdirs = ['auth', 'reservations', 'rooms', 'admin', 'billing', 'services', 'email']

api_dir = os.path.dirname(os.path.abspath(__file__))

for subdir in subdirs:
    subdir_path = os.path.join(api_dir, subdir)
    if not os.path.isdir(subdir_path):
        continue

    for php_file in glob.glob(os.path.join(subdir_path, '*.php')):
        try:
            with open(php_file, 'r', encoding='utf-8') as f:
                content = f.read()

            # Replace require_once 'utils.php'
            content = content.replace(
                "require_once 'utils.php';",
                "require_once __DIR__ . '/../utils.php';"
            )

            with open(php_file, 'w', encoding='utf-8') as f:
                f.write(content)

            print(f"✅ Updated: {os.path.relpath(php_file, api_dir)}")
        except Exception as e:
            print(f"❌ Error updating {php_file}: {e}")

print("\n✅ Tous les chemins utils.php ont été mis à jour!")

