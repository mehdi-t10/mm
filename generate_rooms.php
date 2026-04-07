<?php
/**
 * Script pour générer la structure complète des chambres
 * Génère 100 chambres simples (1-100), 50 doubles (101-150), 50 triples (151-200)
 */

$rooms_data = [
    "types" => [
        [
            "id" => 1,
            "type" => "simple",
            "name" => "Chambre Simple",
            "capacity" => 1,
            "total" => 100,
            "price_per_night" => 60,
            "description" => "Chambre confortable pour 1 personne"
        ],
        [
            "id" => 2,
            "type" => "double",
            "name" => "Chambre Double",
            "capacity" => 2,
            "total" => 50,
            "price_per_night" => 100,
            "description" => "Chambre spacieuse pour 2 personnes"
        ],
        [
            "id" => 3,
            "type" => "triple",
            "name" => "Chambre Triple",
            "capacity" => 3,
            "total" => 50,
            "price_per_night" => 140,
            "description" => "Chambre parfaite pour 3 personnes"
        ]
    ],
    "rooms" => []
];

// Générer les chambres simples (1-100)
for ($i = 1; $i <= 100; $i++) {
    $rooms_data["rooms"][] = [
        "number" => $i,
        "type" => "simple",
        "floor" => 1 + intval(($i - 1) / 25)  // 4 étages de 25 chambres
    ];
}

// Générer les chambres doubles (101-150)
for ($i = 101; $i <= 150; $i++) {
    $rooms_data["rooms"][] = [
        "number" => $i,
        "type" => "double",
        "floor" => 1 + intval(($i - 101) / 13)  // ~13 par étage
    ];
}

// Générer les chambres triples (151-200)
for ($i = 151; $i <= 200; $i++) {
    $rooms_data["rooms"][] = [
        "number" => $i,
        "type" => "triple",
        "floor" => 1 + intval(($i - 151) / 13)  // ~13 par étage
    ];
}

// Écrire dans rooms.json
$json_output = json_encode($rooms_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
file_put_contents(__DIR__ . '/data/rooms.json', $json_output);

echo "✓ Generated 200 rooms:\n";
echo "  - 100 Simple rooms (1-100)\n";
echo "  - 50 Double rooms (101-150)\n";
echo "  - 50 Triple rooms (151-200)\n";
echo "\nFile written to: data/rooms.json\n";
