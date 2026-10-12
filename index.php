<?php
session_start();

$cardsFile = __DIR__ . '/data/cards.json';
$cards = json_decode(file_get_contents($cardsFile), true) ?? [];
$collectionFile = __DIR__ . '/data/.arcana/collections.json';
$cardArtwork = [
    'fool' => 'assets/cards/the-fool.svg',
    'magician' => 'assets/cards/the-magician.svg',
    'moon' => 'assets/cards/the-moon.svg',
    'tower' => 'assets/cards/the-tower.svg',
    'hermit' => 'assets/cards/the-hermit.svg',
    'high-priestess' => 'assets/cards/the-high-priestess.svg',
];

function renderCardArtwork(array $card, array $artworkPaths): void {
    $name = htmlspecialchars($card['name'], ENT_QUOTES, 'UTF-8');
    $artwork = $artworkPaths[$card['id']] ?? null;
    ?>
    <div class="card-artwork">
        <?php if ($artwork !== null): ?>
            <img src="<?= htmlspecialchars($artwork, ENT_QUOTES, 'UTF-8') ?>" alt="<?= $name ?> — original tarot illustration">
        <?php else: ?>
            <div class="card-artwork-fallback" role="img" aria-label="<?= $name ?> tarot symbol">
                <span><?= htmlspecialchars($card['symbol'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>
    </div>
    <?php
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
        'name' => 'The Unregistered',
        'level' => 1,
        'exp' => 0,
        'hp' => 100,
        'max_hp' => 100,
        'stage' => 1,
        'cards' => [],
        'message' => 'The system has noticed your arrival.'
    ];
}

$player = &$_SESSION['player'];

function addExp(array &$player, int $amount): void {
    $player['exp'] += $amount;
    while ($player['exp'] >= $player['level'] * 100) {
        $player['exp'] -= $player['level'] * 100;
        $player['level']++;
        $player['max_hp'] += 10;
        $player['hp'] = $player['max_hp'];
        $player['message'] = 'LEVEL UP. Your level is now ' . $player['level'] . '.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
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
    } elseif ($action === 'explore') {
        $expGain = random_int(15, 35);
        addExp($player, $expGain);
        $player['message'] = 'You survived the investigation and gained ' . $expGain . ' EXP.';
    } elseif ($action === 'stage') {
        if ($player['hp'] > 0) {
            $damage = random_int(8, 22);
            $player['hp'] = max(0, $player['hp'] - $damage);
            if ($player['hp'] > 0) {
                $expGain = random_int(25, 45);
                addExp($player, $expGain);
                $player['stage']++;
                $player['message'] = 'Stage cleared. You lost ' . $damage . ' HP and gained ' . $expGain . ' EXP.';
            } else {
                $player['message'] = 'Your HP reached zero. Rest to recover before entering another stage.';
            }
        } else {
            $player['message'] = 'Your HP is zero. Recover before trying again.';
        }
    } elseif ($action === 'rest') {
        $player['hp'] = $player['max_hp'];
        $player['message'] = 'You recovered your HP. The next stage is waiting.';
    } elseif ($action === 'reset') {
        session_destroy();
        header('Location: index.php');
        exit;
    }

    header('Location: index.php');
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Last Arcana</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="topbar">
        <div class="sigil">✦</div>
        <div>
            <p class="eyebrow">OCCULT SURVIVAL RPG</p>
            <h1>THE LAST ARCANA</h1>
        </div>
        <span class="status-dot" title="System online"></span>
    </header>

    <main class="container">
        <section class="intro panel">
            <p class="eyebrow">ARCHIVE RECORD // 0001</p>
            <h2>The world has forgotten your name.</h2>
            <p class="muted">The Arcana has not.</p>
            <p class="quote">“Not every stage was created to be cleared.”</p>
        </section>

        <section class="panel status-panel">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">PLAYER STATUS</p>
                    <h2><?= htmlspecialchars($player['name']) ?></h2>
                </div>
                <div class="level-badge">LV. <?= (int) $player['level'] ?></div>
            </div>
            <div class="stat-line"><span>HP</span><span><?= (int) $player['hp'] ?> / <?= (int) $player['max_hp'] ?></span></div>
            <div class="bar hp-bar"><span style="width: <?= $hpPercent ?>%"></span></div>
            <div class="stat-line"><span>EXP</span><span><?= (int) $player['exp'] ?> / <?= $expNeeded ?></span></div>
            <div class="bar exp-bar"><span style="width: <?= $expPercent ?>%"></span></div>
            <div class="stage-line"><span>Current stage</span><strong><?= (int) $player['stage'] ?></strong></div>
        </section>

        <section class="notice panel" aria-live="polite">
            <span class="notice-icon">◈</span>
            <div><p class="eyebrow">SYSTEM MESSAGE</p><p><?= htmlspecialchars($player['message']) ?></p></div>
        </section>

        <?php if ($lastDrawnCard !== null): ?>
            <section class="panel reveal-panel" aria-live="polite">
                <p class="eyebrow">NEW CARD REVEALED</p>
                <article class="revealed-card">
                    <?php renderCardArtwork($lastDrawnCard, $cardArtwork); ?>
                    <div class="revealed-card-details">
                        <p class="card-number"><?= htmlspecialchars($lastDrawnCard['number']) ?></p>
                        <h2><?= htmlspecialchars($lastDrawnCard['name']) ?></h2>
                        <p class="rarity"><?= htmlspecialchars($lastDrawnCard['rarity']) ?></p>
                        <p class="card-description"><?= htmlspecialchars($lastDrawnCard['description']) ?></p>
                        <p class="copy-count">Copies owned: <?= $collectionCounts[$lastDrawnCard['id']] ?></p>
                    </div>
                </article>
            </section>
        <?php endif; ?>

        <section class="panel">
            <p class="eyebrow">CHOOSE YOUR ACTION</p>
            <div class="action-grid">
                <form method="post"><input type="hidden" name="action" value="draw"><button class="action-card" type="submit"><span class="action-icon">✧</span><strong>Draw Arcana</strong><small>Draw a mysterious card</small></button></form>
                <form method="post"><input type="hidden" name="action" value="explore"><button class="action-card" type="submit"><span class="action-icon">⌕</span><strong>Investigate</strong><small>Search for hidden clues</small></button></form>
                <form method="post"><input type="hidden" name="action" value="stage"><button class="action-card" type="submit"><span class="action-icon">⚔</span><strong>Enter Stage</strong><small>Risk HP for more EXP</small></button></form>
                <form method="post"><input type="hidden" name="action" value="rest"><button class="action-card" type="submit"><span class="action-icon">☾</span><strong>Rest</strong><small>Restore all HP</small></button></form>
            </div>
        </section>

        <section class="panel">
            <div class="section-heading"><div><p class="eyebrow">RECOVERED OBJECTS</p><h2>Arcana Collection</h2></div><span class="count"><?= $collectionTotal ?> <?= $collectionTotal === 1 ? 'copy' : 'copies' ?> · <?= count($ownedCards) ?> unique</span></div>
            <?php if (count($ownedCards) === 0): ?>
                <div class="empty-state"><span>☽</span><p>No cards recovered yet.</p><small>Draw an Arcana card to begin your collection. Your discoveries are saved between visits.</small></div>
            <?php else: ?>
                <div class="card-grid">
                    <?php foreach ($ownedCards as $card): ?>
                        <article class="tarot-card">
                            <?php renderCardArtwork($card, $cardArtwork); ?>
                            <p class="card-number"><?= htmlspecialchars($card['number']) ?></p>
                            <h3><?= htmlspecialchars($card['name']) ?></h3>
                            <p class="rarity"><?= htmlspecialchars($card['rarity']) ?></p>
                            <p class="card-description"><?= htmlspecialchars($card['description']) ?></p>
                            <p class="copy-count"><?= $collectionCounts[$card['id']] ?> <?= $collectionCounts[$card['id']] === 1 ? 'copy' : 'copies' ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="panel archive-panel">
            <p class="eyebrow">CLASSIFIED FILE // LOCKED</p>
            <h2>The truth is buried.</h2>
            <p class="muted">Organizations, forbidden entities, corrupted cards, and hidden endings are waiting to be discovered in future updates.</p>
            <div class="redacted">████████ ███████ ███ THE OBSERVER ███████</div>
        </section>

        <footer>
            <p>THE LAST ARCANA <span>•</span> ARCHIVE VERSION 0.1</p>
            <form method="post" onsubmit="return confirm('Reset player status? Your Arcana Collection will be kept.');"><input type="hidden" name="action" value="reset"><button class="reset-button" type="submit">Reset player status</button></form>
        </footer>
    </main>
</body>
</html>
