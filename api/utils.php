<?php

function readJson($fileName) {
    $path = __DIR__ . '/../data/' . $fileName;

    if (!file_exists($path)) {
        return [];
    }

    $content = file_get_contents($path);
    $data = json_decode($content, true);

    return is_array($data) ? $data : [];
}

function writeJson($fileName, $data) {
    $path = __DIR__ . '/../data/' . $fileName;
    file_put_contents(
        $path,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

function jsonResponse($data) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function nextId($items) {
    if (empty($items)) {
        return 1;
    }

    $ids = array_column($items, 'id');
    return max($ids) + 1;
}

function nightsCount($dateStart, $dateEnd) {
    $d1 = new DateTime($dateStart);
    $d2 = new DateTime($dateEnd);
    $diff = $d1->diff($d2)->days;

    return max(1, $diff);
}

function generatePassword($firstName) {
    return strtolower($firstName) . '2026';
}