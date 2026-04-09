#!/usr/bin/env python3
# -*- coding: utf-8 -*-

# Dictionnaire de remplacement emoji -> Font Awesome
emoji_map = {
    '📅': '<i class="fas fa-calendar"></i>',
    '💰': '<i class="fas fa-wallet"></i>',
    '🏠': '<i class="fas fa-home"></i>',
    '👥': '<i class="fas fa-users"></i>',
    '⏳': '<i class="fas fa-hourglass-start"></i>',
    '✅': '<i class="fas fa-check-circle"></i>',
    '❌': '<i class="fas fa-times-circle"></i>',
    '🚪': '<i class="fas fa-sign-out-alt"></i>',
    '📧': '<i class="fas fa-envelope"></i>',
    '🛏️': '<i class="fas fa-bed"></i>',
    '📋': '<i class="fas fa-list"></i>',
    '🔍': '<i class="fas fa-search"></i>',
    '⚽': '<i class="fas fa-futbol"></i>',
    '📥': '<i class="fas fa-download"></i>',
    '✕': '<i class="fas fa-times"></i>',
    '📄': '<i class="fas fa-file"></i>',
    '💾': '<i class="fas fa-save"></i>',
}

files_to_process = [
    'D:\\parisss\\WEB\\mm\\client-dashboard.html',
    'D:\\parisss\\WEB\\mm\\admin-dashboard.html',
    'D:\\parisss\\WEB\\mm\\index.html',
    'D:\\parisss\\WEB\\mm\\assets\\auto-redirect.js',
    'D:\\parisss\\WEB\\mm\\assets\\session-manager.js',
    'D:\\parisss\\WEB\\mm\\assets\\client.js',
    'D:\\parisss\\WEB\\mm\\assets\\admin.js',
]

for filepath in files_to_process:
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()

        original_content = content
        for emoji, fa_icon in emoji_map.items():
            content = content.replace(emoji, fa_icon)

        if content != original_content:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f'✓ {filepath.split(chr(92))[-1]}: emojis remplacés')
        else:
            print(f'- {filepath.split(chr(92))[-1]}: aucun emoji trouvé')
    except Exception as e:
        print(f'✗ {filepath}: {str(e)}')

print('\nDone!')



