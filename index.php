<?php
session_start();

$cardsFile = __DIR__ . '/data/cards.json';
$cards = json_decode(file_get_contents($cardsFile), true) ?? [];
$collectionFile = __DIR__ . '/data/.arcana/collections.json';
$cardArtwork = [
    'fool' => 'assets/cards/the-fool.jpg',
    'magician' => 'assets/cards/the-magician.jpg',
    'moon' => 'assets/cards/the-moon.jpg',
    'tower' => 'assets/cards/the-tower.jpg',
    'hermit' => 'assets/cards/the-hermit.jpg',
    'high-priestess' => 'assets/cards/the-high-priestess.jpg',
];
$cardArtworkFallback = [
    'fool' => 'assets/cards/the-fool.svg',
    'magician' => 'assets/cards/the-magician.svg',
    'moon' => 'assets/cards/the-moon.svg',
    'tower' => 'assets/cards/the-tower.svg',
    'hermit' => 'assets/cards/the-hermit.svg',
    'high-priestess' => 'assets/cards/the-high-priestess.svg',
];

function renderCardArtwork(array $card, array $artworkPaths, array $fallbackPaths, string $loading = 'lazy'): void {
    $name = htmlspecialchars($card['name'], ENT_QUOTES, 'UTF-8');
    $artwork = $artworkPaths[$card['id']] ?? null;
    if ($artwork !== null && !is_file(__DIR__ . '/' . $artwork)) {
        $artwork = $fallbackPaths[$card['id']] ?? null;
    }
    ?>
    <div class="card-artwork">
        <?php if ($artwork !== null): ?>
            <img src="<?= htmlspecialchars($artwork, ENT_QUOTES, 'UTF-8') ?>"
                 alt="<?= $name ?> — original tarot illustration"
                 data-i18n-alt="alt.tarotIllustration"
                 data-i18n-alt-params="<?= htmlspecialchars(json_encode(['name' => $card['name']], JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>"
                 loading="<?= htmlspecialchars($loading, ENT_QUOTES, 'UTF-8') ?>" decoding="async">
        <?php else: ?>
            <div class="card-artwork-fallback" role="img" aria-label="<?= $name ?> tarot symbol">
                <span><?= htmlspecialchars($card['symbol'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

function rarityKey(string $rarity): string {
    $rarityName = strtolower((string) strtok(trim($rarity), ' '));
    return in_array($rarityName, ['common', 'uncommon', 'rare', 'epic', 'legendary', 'mythic'], true)
        ? $rarityName
        : 'common';
}

function collectionSaveId(): string {
    $cookieName = 'arcana_save_id';
    $saveId = $_COOKIE[$cookieName] ?? '';

    if (!preg_match('/\A[a-f0-9]{64}\z/', $saveId)) {
        $saveId = bin2hex(random_bytes(32));
        $forwardedHttps = ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
        $directHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

        setcookie($cookieName, $saveId, [
            'expires' => time() + (5 * 365 * 24 * 60 * 60),
            'path' => '/',
            'secure' => $directHttps || $forwardedHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        $_COOKIE[$cookieName] = $saveId;
    }

    return hash('sha256', $saveId);
}

function readCollectionStore($handle): array {
    if (!rewind($handle)) {
        throw new RuntimeException('Could not read the Arcana collection.');
    }

    $contents = stream_get_contents($handle);
    if ($contents === false) {
        throw new RuntimeException('Could not read the Arcana collection.');
    }
    if (trim($contents) === '') {
        return [];
    }

    try {
        $store = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        throw new RuntimeException('The Arcana collection file contains invalid JSON.', 0, $exception);
    }

    if (!is_array($store)) {
        throw new RuntimeException('The Arcana collection file has an invalid format.');
    }

    return $store;
}

function writeCollectionStore($handle, array $store): void {
    $json = json_encode($store, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . PHP_EOL;
    if (!rewind($handle) || !ftruncate($handle, 0)) {
        throw new RuntimeException('Could not save the Arcana collection.');
    }

    $offset = 0;
    $length = strlen($json);
    while ($offset < $length) {
        $written = fwrite($handle, substr($json, $offset));
        if ($written === false || $written === 0) {
            throw new RuntimeException('Could not save the Arcana collection.');
        }
        $offset += $written;
    }

    if (!fflush($handle)) {
        throw new RuntimeException('Could not save the Arcana collection.');
    }
}

function loadCollectionCounts(string $file, string $owner): array {
    $handle = fopen($file, 'c+');
    if ($handle === false) {
        throw new RuntimeException('Could not open the Arcana collection file.');
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            throw new RuntimeException('Could not lock the Arcana collection file.');
        }
        $store = readCollectionStore($handle);
        if (fstat($handle)['size'] === 0) {
            writeCollectionStore($handle, $store);
        }
        $counts = $store[$owner] ?? [];

        if (!is_array($counts)) {
            throw new RuntimeException('The saved Arcana collection has an invalid format.');
        }

        foreach ($counts as $cardId => $count) {
            if (!is_string($cardId) || !is_int($count) || $count < 1) {
                throw new RuntimeException('The saved Arcana collection has an invalid card count.');
            }
        }

        return $counts;
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}

function addCardToCollection(string $file, string $owner, string $cardId): int {
    $handle = fopen($file, 'c+');
    if ($handle === false) {
        throw new RuntimeException('Could not open the Arcana collection file.');
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            throw new RuntimeException('Could not lock the Arcana collection file.');
        }

        $store = readCollectionStore($handle);
        $counts = $store[$owner] ?? [];
        if (!is_array($counts)) {
            throw new RuntimeException('The saved Arcana collection has an invalid format.');
        }

        $currentCount = $counts[$cardId] ?? 0;
        if (!is_int($currentCount) || $currentCount < 0) {
            throw new RuntimeException('The saved Arcana collection has an invalid card count.');
        }

        $counts[$cardId] = $currentCount + 1;
        $store[$owner] = $counts;
        writeCollectionStore($handle, $store);

        return $counts[$cardId];
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}

$collectionOwner = collectionSaveId();
$collectionCounts = loadCollectionCounts($collectionFile, $collectionOwner);


if (!isset($_SESSION['player'])) {
    $_SESSION['player'] = [
        'created' => false,
        'name' => '',
        'class' => '',
        'level' => 1,
        'exp' => 0,
        'hp' => 100,
        'max_hp' => 100,
        'mana' => 80,
        'max_mana' => 80,
        'attack' => 15,
        'defense' => 10,
        'stage' => 1,
        'cards' => [],
        'message' => 'Create your character to begin.',
        'message_key' => 'message.arrival',
        'message_params' => [],
    ];
}

$player = &$_SESSION['player'];

// Compatibility with existing player sessions.
if (!isset($player['created'])) {
    $player['created'] = ($player['name'] ?? '') !== 'The Unregistered';
}

$player['mana'] ??= 80;
$player['max_mana'] ??= 80;
$player['attack'] ??= 15;
$player['defense'] ??= 10;
$player['class'] ??= '';

if (!isset($player['message_key'])) {
    $player['message_key'] = 'message.arrival';
    $player['message_params'] = [];
}

function addExp(array &$player, int $amount): void {
    $player['exp'] += $amount;
    while ($player['exp'] >= $player['level'] * 100) {
        $player['exp'] -= $player['level'] * 100;
        $player['level']++;
        $player['max_hp'] += 10;
        $player['hp'] = $player['max_hp'];
        $player['message'] = 'LEVEL UP. Your level is now ' . $player['level'] . '.';
        $player['message_key'] = 'message.levelUp';
        $player['message_params'] = ['level' => $player['level']];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_character') {
        $name = trim($_POST['character_name'] ?? '');
        $class = $_POST['character_class'] ?? '';

        $classes = [
            'Arcanist' => [
                'hp' => 80,
                'mana' => 140,
                'attack' => 15,
                'defense' => 8,
            ],
            'Shadow Hunter' => [
                'hp' => 100,
                'mana' => 80,
                'attack' => 22,
                'defense' => 12,
            ],
            'Knight of Ruin' => [
                'hp' => 130,
                'mana' => 50,
                'attack' => 16,
                'defense' => 20,
            ],
        ];

        if ($name === '' || strlen($name) > 40
            || !isset($classes[$class])) {
            header('Location: index.php');
            exit;
        }

        $stats = $classes[$class];

        $player['created'] = true;
        $player['name'] = $name;
        $player['class'] = $class;
        $player['level'] = 1;
        $player['exp'] = 0;
        $player['hp'] = $stats['hp'];
        $player['max_hp'] = $stats['hp'];
        $player['mana'] = $stats['mana'];
        $player['max_mana'] = $stats['mana'];
        $player['attack'] = $stats['attack'];
        $player['defense'] = $stats['defense'];
        $player['stage'] = 1;
        $player['message'] = 'Your journey begins.';
        $player['message_key'] = 'message.arrival';
        $player['message_params'] = [];

        unset($player['last_card_id']);

        header('Location: index.php');
        exit;
    }
    
    if ($action !== 'draw') {
        unset($player['last_card_id']);
    }

    if ($action === 'draw' && count($cards) > 0) {
        $card = $cards[array_rand($cards)];
        $player['last_card_id'] = $card['id'];
        $collectionCounts[$card['id']] = addCardToCollection($collectionFile, $collectionOwner, $card['id']);
        $player['message'] = 'A card has answered your call: ' . $card['name']
            . ($collectionCounts[$card['id']] > 1
                ? '. You now have ' . $collectionCounts[$card['id']] . ' copies.'
                : '. It has been added to your collection.');
        $player['message_key'] = $collectionCounts[$card['id']] > 1
            ? 'message.drawDuplicate'
            : 'message.drawNew';
        $player['message_params'] = ['name' => $card['name'], 'count' => $collectionCounts[$card['id']]];
    } elseif ($action === 'explore') {
        $expGain = random_int(15, 35);
        addExp($player, $expGain);
        $player['message'] = 'You survived the investigation and gained ' . $expGain . ' EXP.';
        $player['message_key'] = 'message.investigate';
        $player['message_params'] = ['amount' => $expGain];
    } elseif ($action === 'stage') {
        if ($player['hp'] > 0) {
            $damage = random_int(8, 22);
            $player['hp'] = max(0, $player['hp'] - $damage);
            if ($player['hp'] > 0) {
                $expGain = random_int(25, 45);
                addExp($player, $expGain);
                $player['stage']++;
                $player['message'] = 'Stage cleared. You lost ' . $damage . ' HP and gained ' . $expGain . ' EXP.';
                $player['message_key'] = 'message.stageCleared';
                $player['message_params'] = ['damage' => $damage, 'amount' => $expGain];
            } else {
                $player['message'] = 'Your HP reached zero. Rest to recover before entering another stage.';
                $player['message_key'] = 'message.hpZero';
                $player['message_params'] = [];
            }
        } else {
            $player['message'] = 'Your HP is zero. Recover before trying again.';
            $player['message_key'] = 'message.needRest';
            $player['message_params'] = [];
        }
    } elseif ($action === 'rest') {
        $player['hp'] = $player['max_hp'];
        $player['message'] = 'You recovered your HP. The next stage is waiting.';
        $player['message_key'] = 'message.restored';
        $player['message_params'] = [];
    } elseif ($action === 'reset') {
        session_destroy();
        header('Location: index.php');
        exit;
    }

    header('Location: index.php');
    exit;
}

if (empty($player['created'])) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#100e10">
    <title>Create Character — The Last Arcana</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .creation-panel {
            max-width: 520px;
            margin: 36px auto;
            padding: 26px;
            border: 1px solid #76516e;
            border-radius: 18px;
            background: linear-gradient(145deg, #241a2b, #100e10 75%);
            box-shadow: 0 0 35px #8d45a522;
        }
        .creation-panel h2 {
            color: #e3c4ff;
            letter-spacing: 2px;
        }
        .creation-panel label {
            display: block;
            margin: 18px 0 8px;
            color: #d9c5e8;
        }
        .creation-panel input,
        .creation-panel select {
            box-sizing: border-box;
            width: 100%;
            padding: 13px;
            border: 1px solid #67496f;
            border-radius: 9px;
            color: #f0e6f5;
            background: #151019;
            font: inherit;
        }
        .class-option {
            margin-top: 12px;
            padding: 13px;
            border: 1px solid #49323b;
            border-radius: 10px;
            color: #c9bacf;
            font-size: .9rem;
        }
        .class-option strong {
            display: block;
            margin-bottom: 5px;
            color: #e2c3ff;
        }
        .creation-button {
            width: 100%;
            margin-top: 24px;
            padding: 14px;
            border: 1px solid #c7a77a;
            border-radius: 10px;
            color: #fff2dc;
            background: linear-gradient(100deg, #572d60, #8c354d);
            font: inherit;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="sigil">✦</div>
        <div>
            <p class="eyebrow">OCCULT SURVIVAL RPG</p>
            <h1>THE LAST ARCANA</h1>
        </div>
    </header>

    <main class="container">
        <section class="creation-panel">
            <p class="eyebrow">SYSTEM INITIALIZATION // 0001</p>
            <h2>CREATE YOUR CHARACTER</h2>
            <p class="muted">
                The Arcana has noticed your existence.
                Choose who you will become.
            </p>

            <form method="post">
                <input type="hidden" name="action"
                       value="create_character">

                <label for="character_name">Character Name</label>
                <input id="character_name"
                       name="character_name"
                       type="text"
                       maxlength="40"
                       required
                       placeholder="Enter your name">

                <label for="character_class">Choose Your Class</label>
                <select id="character_class"
                        name="character_class" required>
                    <option value="Arcanist">Arcanist</option>
                    <option value="Shadow Hunter">Shadow Hunter</option>
                    <option value="Knight of Ruin">Knight of Ruin</option>
                </select>

                <div class="class-option">
                    <strong>✧ Arcanist</strong>
                    High mana and mysterious arcane power.
                    <br>HP 80 · Mana 140 · ATK 15 · DEF 8
                </div>

                <div class="class-option">
                    <strong>☾ Shadow Hunter</strong>
                    A swift hunter specializing in attacks.
                    <br>HP 100 · Mana 80 · ATK 22 · DEF 12
                </div>

                <div class="class-option">
                    <strong>⚔ Knight of Ruin</strong>
                    A durable warrior who can endure danger.
                    <br>HP 130 · Mana 50 · ATK 16 · DEF 20
                </div>

                <button class="creation-button" type="submit">
                    AWAKEN MY CHARACTER
                </button>
            </form>
        </section>
    </main>
</body>
</html>
<?php
    exit;
}

$expNeeded = $player['level'] * 100;
$expPercent = min(100, (int) (($player['exp'] / $expNeeded) * 100));
$hpPercent = (int) (($player['hp'] / $player['max_hp']) * 100);
$ownedCards = [];
foreach ($cards as $card) {
    $cardId = $card['id'];
    if (!isset($collectionCounts[$cardId])) {
        continue;
    }

    $ownedCards[] = $card;
}
$lastDrawnCard = null;
foreach ($cards as $card) {
    if ($card['id'] === ($player['last_card_id'] ?? null)) {
        $lastDrawnCard = $card;
        break;
    }
}
$collectionTotal = array_sum($collectionCounts);
$playerMessageKey = is_string($player['message_key'] ?? null) ? $player['message_key'] : 'message.arrival';
$playerMessageParams = is_array($player['message_params'] ?? null) ? $player['message_params'] : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#100e10">
    <title data-i18n="brand.title">The Last Arcana</title>
    <link rel="stylesheet" href="style.css">
    <script src="assets/js/translations.js" defer></script>
</head>
<body>
    <header class="topbar">
        <div class="sigil">✦</div>
        <div>
            <p class="eyebrow" data-i18n="brand.tagline">OCCULT SURVIVAL RPG</p>
            <h1>THE LAST ARCANA</h1>
        </div>
        <div class="topbar-actions">
            <button id="language-toggle" class="language-toggle" type="button" aria-label="Switch to Bahasa Indonesia">🇬🇧 EN</button>
            <span class="status-dot" title="System online" data-i18n-title="status.online" data-i18n-aria-label="status.online"></span>
        </div>
    </header>

    <main class="container">
        <details class="panel settings-panel">
            <summary data-i18n="settings.title">Settings</summary>
            <label for="language-select" data-i18n="language.label">Language</label>
            <select id="language-select" aria-label="Language" data-i18n-aria-label="language.label">
                <option value="en" data-i18n="language.english">🇬🇧 English</option>
                <option value="id" data-i18n="language.indonesian">🇮🇩 Bahasa Indonesia</option>
            </select>
        </details>

        <section class="intro panel">
            <p class="eyebrow" data-i18n="intro.archiveRecord">ARCHIVE RECORD // 0001</p>
            <h2 data-i18n="intro.headline">The world has forgotten your name.</h2>
            <p class="muted" data-i18n="intro.subline">The Arcana has not.</p>
            <p class="quote" data-i18n="intro.quote">“Not every stage was created to be cleared.”</p>
        </section>

        <section class="panel status-panel">
            <div class="section-heading">
                <div class="status-identity">
                    <img class="player-portrait" src="assets/characters/the-unregistered.jpg" alt="Portrait of The Unregistered" data-i18n-alt="alt.playerPortrait" loading="lazy" decoding="async">
                    <div>
                    <p class="eyebrow" data-i18n="status.title">PLAYER STATUS</p>
                    <h2><?= htmlspecialchars($player['name']) ?></h2>
                    </div>
                </div>
                <div class="level-badge"><span data-i18n="status.levelShort">LV.</span> <?= (int) $player['level'] ?></div>
            </div>
            <div class="stat-line"><span data-i18n="status.hp">HP</span><span><?= (int) $player['hp'] ?> / <?= (int) $player['max_hp'] ?></span></div>
            <div class="bar hp-bar"><span style="width: <?= $hpPercent ?>%"></span></div>
            <div class="stat-line"><span data-i18n="status.exp">EXP</span><span><?= (int) $player['exp'] ?> / <?= $expNeeded ?></span></div>
            <div class="bar exp-bar"><span style="width: <?= $expPercent ?>%"></span></div>
            <div class="stage-line"><span data-i18n="status.stage">Current stage</span><strong><?= (int) $player['stage'] ?></strong></div>
        </section>

        <section class="notice panel" aria-live="polite">
            <span class="notice-icon">◈</span>
            <div><p class="eyebrow" data-i18n="message.label">SYSTEM MESSAGE</p><p data-i18n="<?= htmlspecialchars($playerMessageKey, ENT_QUOTES, 'UTF-8') ?>" data-i18n-params="<?= htmlspecialchars(json_encode($playerMessageParams, JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($player['message']) ?></p></div>
        </section>

        <?php if ($lastDrawnCard !== null): ?>
            <section class="panel reveal-panel" aria-live="polite">
                <p class="eyebrow" data-i18n="draw.revealed">NEW CARD REVEALED</p>
                <article class="revealed-card rarity-<?= htmlspecialchars(rarityKey($lastDrawnCard['rarity']), ENT_QUOTES, 'UTF-8') ?>">
                    <?php renderCardArtwork($lastDrawnCard, $cardArtwork, $cardArtworkFallback, 'eager'); ?>
                    <div class="revealed-card-details">
                        <p class="card-number"><?= htmlspecialchars($lastDrawnCard['number']) ?></p>
                        <h2><?= htmlspecialchars($lastDrawnCard['name']) ?></h2>
                        <p class="rarity rarity-<?= htmlspecialchars(rarityKey($lastDrawnCard['rarity']), ENT_QUOTES, 'UTF-8') ?>" data-i18n="rarity.<?= htmlspecialchars(rarityKey($lastDrawnCard['rarity']), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucfirst(rarityKey($lastDrawnCard['rarity']))) ?></p>
                        <p class="card-description" data-i18n="cards.<?= htmlspecialchars($lastDrawnCard['id'], ENT_QUOTES, 'UTF-8') ?>.description"><?= htmlspecialchars($lastDrawnCard['description']) ?></p>
                        <p class="copy-count" data-i18n="collection.ownedCopies" data-i18n-params="<?= htmlspecialchars(json_encode(['count' => $collectionCounts[$lastDrawnCard['id']]], JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>">Copies owned: <?= $collectionCounts[$lastDrawnCard['id']] ?></p>
                    </div>
                </article>
            </section>
        <?php endif; ?>

        <section class="panel">
            <p class="eyebrow" data-i18n="actions.title">CHOOSE YOUR ACTION</p>
            <div class="action-grid">
                <form method="post"><input type="hidden" name="action" value="draw"><button class="action-card draw-action" type="submit"><span class="action-icon">✧</span><strong data-i18n="actions.draw">Draw Arcana</strong><small data-i18n="actions.drawHint">Draw a mysterious card</small></button></form>
                <form method="post"><input type="hidden" name="action" value="explore"><button class="action-card" type="submit"><span class="action-icon">⌕</span><strong data-i18n="actions.investigate">Investigate</strong><small data-i18n="actions.investigateHint">Search for hidden clues</small></button></form>
                <form method="post"><input type="hidden" name="action" value="stage"><button class="action-card" type="submit"><span class="action-icon">⚔</span><strong data-i18n="actions.stage">Enter Stage</strong><small data-i18n="actions.stageHint">Risk HP for more EXP</small></button></form>
                <form method="post"><input type="hidden" name="action" value="rest"><button class="action-card" type="submit"><span class="action-icon">☾</span><strong data-i18n="actions.rest">Rest</strong><small data-i18n="actions.restHint">Restore all HP</small></button></form>
            </div>
        </section>

        <section class="panel">
            <div class="section-heading"><div><p class="eyebrow" data-i18n="collection.eyebrow">RECOVERED OBJECTS</p><h2 data-i18n="collection.title">Arcana Collection</h2></div><span class="count"><span data-i18n="collection.<?= $collectionTotal === 1 ? 'totalCopy' : 'totalCopies' ?>" data-i18n-params="<?= htmlspecialchars(json_encode(['count' => $collectionTotal], JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>"><?= $collectionTotal ?> copies</span> · <span data-i18n="collection.<?= count($ownedCards) === 1 ? 'uniqueCard' : 'uniqueCards' ?>" data-i18n-params="<?= htmlspecialchars(json_encode(['count' => count($ownedCards)], JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>"><?= count($ownedCards) ?> unique</span></span></div>
            <?php if (count($ownedCards) === 0): ?>
                <div class="empty-state"><span>☽</span><p data-i18n="collection.empty">No cards recovered yet.</p><small data-i18n="collection.emptyHint">Draw an Arcana card to begin your collection. Your discoveries are saved between visits.</small></div>
            <?php else: ?>
                <div class="card-grid">
                    <?php foreach ($ownedCards as $card): ?>
                        <?php $cardRarityKey = rarityKey($card['rarity']); ?>
                        <article class="tarot-card rarity-<?= htmlspecialchars($cardRarityKey, ENT_QUOTES, 'UTF-8') ?>">
                            <?php renderCardArtwork($card, $cardArtwork, $cardArtworkFallback); ?>
                            <p class="card-number"><?= htmlspecialchars($card['number']) ?></p>
                            <h3><?= htmlspecialchars($card['name']) ?></h3>
                            <p class="rarity rarity-<?= htmlspecialchars($cardRarityKey, ENT_QUOTES, 'UTF-8') ?>" data-i18n="rarity.<?= htmlspecialchars($cardRarityKey, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucfirst($cardRarityKey)) ?></p>
                            <p class="card-description" data-i18n="cards.<?= htmlspecialchars($card['id'], ENT_QUOTES, 'UTF-8') ?>.description"><?= htmlspecialchars($card['description']) ?></p>
                            <p class="copy-count" data-i18n="collection.<?= $collectionCounts[$card['id']] === 1 ? 'copyCountSingle' : 'copyCountMany' ?>" data-i18n-params="<?= htmlspecialchars(json_encode(['count' => $collectionCounts[$card['id']]], JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>"><?= $collectionCounts[$card['id']] ?> copies</p>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="panel archive-panel">
            <div class="archive-lead">
                <img src="assets/bosses/the-observer.jpg" alt="The Observer, a concealed figure inside a forbidden archive" data-i18n-alt="alt.observer" loading="lazy" decoding="async">
                <div>
                    <p class="eyebrow" data-i18n="archive.classified">CLASSIFIED FILE // LOCKED</p>
                    <h2 data-i18n="archive.truth">The truth is buried.</h2>
                    <p class="muted" data-i18n="archive.teaser">The Observer is watching from beyond the sealed record.</p>
                </div>
            </div>
            <div class="redacted" data-i18n="archive.redacted">████████ ███████ ███ THE OBSERVER ███████</div>
            <details class="archive-preview">
                <summary data-i18n="archive.previewToggle">Open sealed previews</summary>
                <p class="preview-notice" data-i18n="archive.previewNotice">Concept art only — these records are not playable content and have no unlock conditions yet.</p>
                <h3 data-i18n="archive.locations">Sealed locations</h3>
                <div class="archive-grid">
                    <figure><img src="assets/locations/cursed-forest.jpg" alt="Cursed forest concept art" data-i18n-alt="alt.location.forest" loading="lazy" decoding="async"><figcaption data-i18n="location.forest">Cursed Forest</figcaption></figure>
                    <figure><img src="assets/locations/abandoned-city.jpg" alt="Abandoned city concept art" data-i18n-alt="alt.location.city" loading="lazy" decoding="async"><figcaption data-i18n="location.city">Abandoned City</figcaption></figure>
                    <figure><img src="assets/locations/ancient-library.jpg" alt="Ancient library concept art" data-i18n-alt="alt.location.library" loading="lazy" decoding="async"><figcaption data-i18n="location.library">Ancient Library</figcaption></figure>
                    <figure><img src="assets/locations/underground-ruins.jpg" alt="Underground ruins concept art" data-i18n-alt="alt.location.ruins" loading="lazy" decoding="async"><figcaption data-i18n="location.ruins">Underground Ruins</figcaption></figure>
                    <figure><img src="assets/locations/occult-sanctuary.jpg" alt="Occult sanctuary concept art" data-i18n-alt="alt.location.sanctuary" loading="lazy" decoding="async"><figcaption data-i18n="location.sanctuary">Occult Sanctuary</figcaption></figure>
                </div>
                <h3 data-i18n="archive.entities">Entities under observation</h3>
                <div class="archive-grid archive-grid-small">
                    <figure><img src="assets/monsters/nameless-hunter.jpg" alt="Nameless Hunter concept art" data-i18n-alt="alt.hunter" loading="lazy" decoding="async"><figcaption data-i18n="archive.hunter">The Nameless Hunter · PREVIEW</figcaption></figure>
                    <figure><img src="assets/bosses/the-observer.jpg" alt="The Observer concept art" data-i18n-alt="alt.observer" loading="lazy" decoding="async"><figcaption data-i18n="archive.observer">The Observer · LOCKED</figcaption></figure>
                </div>
                <h3 data-i18n="archive.relics">Uncatalogued relics</h3>
                <div class="archive-grid archive-grid-small">
                    <figure><img src="assets/items/ashen-relic.jpg" alt="Ashen Relic concept art" data-i18n-alt="alt.item.relic" loading="lazy" decoding="async"><figcaption data-i18n="item.relic">Ashen Relic</figcaption></figure>
                    <figure><img src="assets/items/moonsteel-blade.jpg" alt="Moonsteel Blade concept art" data-i18n-alt="alt.item.blade" loading="lazy" decoding="async"><figcaption data-i18n="item.blade">Moonsteel Blade</figcaption></figure>
                    <figure><img src="assets/items/veiled-mirror.jpg" alt="Veiled Mirror concept art" data-i18n-alt="alt.item.mirror" loading="lazy" decoding="async"><figcaption data-i18n="item.mirror">Veiled Mirror</figcaption></figure>
                </div>
            </details>
        </section>

        <footer>
            <p>THE LAST ARCANA <span>•</span> <span data-i18n="footer.version">ARCHIVE VERSION 0.1</span></p>
            <form method="post" data-confirm-i18n="confirm.reset"><input type="hidden" name="action" value="reset"><button class="reset-button" type="submit" data-i18n="footer.reset">Reset player status</button></form>
        </footer>
    </main>
</body>
</html>
