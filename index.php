<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>THE LAST ARCANA</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="stars"></div>

<header class="topbar">
    <a href="index.php" class="logo">
        <span class="logo-symbol">✦</span>
        THE LAST ARCANA
    </a>

    <button class="language-btn" onclick="changeLanguage()">
        ID / EN
    </button>
</header>

<main class="hero">

    <div class="arcana-seal">
        <div class="seal-ring"></div>
        <div class="seal-inner">
            <span class="moon">☾</span>
            <span class="seal-star">✦</span>
            <span class="seal-eye">◉</span>
            <span class="seal-bottom">✧</span>
        </div>
    </div>

    <p class="eyebrow" id="subtitle">
        OCCULT SURVIVAL RPG
    </p>

    <h1>THE LAST<br><span>ARCANA</span></h1>

    <div class="ornament">
        <span></span> ✦ <span></span>
    </div>

    <p class="description" id="description">
        Ketika takdir mulai runtuh, kartu terakhir akan menentukan
        siapa yang bertahan dan siapa yang lenyap dalam bayangan.
    </p>

    <div class="quote">
        "Even fate fears the final card."
    </div>

    <a href="character.php" class="start-button" id="startButton">
        <span>✧</span>
        <span id="startText">MULAI PERJALANAN</span>
        <span>➜</span>
    </a>

    <p class="start-note" id="note">
        YOUR DESTINY AWAITS BEYOND THE VEIL
    </p>

</main>

<footer class="footer">
    <span>✦</span> THE LAST ARCANA
    <p>THE WORLD IS NOT WHAT IT SEEMS</p>
</footer>

<script>
let language = "id";

function changeLanguage() {
    const subtitle = document.getElementById("subtitle");
    const description = document.getElementById("description");
    const startText = document.getElementById("startText");
    const note = document.getElementById("note");

    if (language === "id") {
        subtitle.textContent = "OCCULT SURVIVAL RPG";
        description.textContent =
            "When fate begins to collapse, the final card will decide who survives and who disappears into the shadows.";
        startText.textContent = "START YOUR JOURNEY";
        note.textContent = "YOUR DESTINY AWAITS BEYOND THE VEIL";
        language = "en";
    } else {
        description.textContent =
            "Ketika takdir mulai runtuh, kartu terakhir akan menentukan siapa yang bertahan dan siapa yang lenyap dalam bayangan.";
        startText.textContent = "MULAI PERJALANAN";
        note.textContent = "TAKDIRMU MENANTI DI BALIK TABIR";
        language = "id";
    }
}
</script>

</body>
</html>
