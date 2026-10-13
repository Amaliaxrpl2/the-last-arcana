<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['player'])) {
    $_SESSION['player'] = [
        'created' => false,
        'name' => '',
        'class' => '',
        'level' => 1,
        'exp' => 0,
        'exp_next' => 100,
        'hp' => 100,
        'max_hp' => 100,
        'mana' => 50,
        'max_mana' => 50,
        'attack' => 10,
        'defense' => 5,
        'stage' => 1,
        'gold' => 0,
        'cards' => []
    ];
}

function arcana_escape($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function arcana_redirect($page) {
    header('Location: ' . $page);
    exit;
}
