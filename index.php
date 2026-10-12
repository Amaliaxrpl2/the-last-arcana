<?php
session_start();

$cardsFile = __DIR__ . '/data/cards.json';
$cards = json_decode(file_get_contents($cardsFile), true) ?? [];

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

    if ($action === 'draw' && count($cards) > 0) {
        $card = $cards[array_rand($cards)];
        $player['cards'][] = $card['id'];
        $player['message'] = 'A card has answered your call: ' . $card['name'] . '.';
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
foreach ($player['cards'] as $cardId) {
    foreach ($cards as $card) {
        if ($card['id'] === $cardId) {
            $ownedCards[] = $card;
            break;
        }
    }
}
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
            <div class="section-heading"><div><p class="eyebrow">RECOVERED OBJECTS</p><h2>Your Arcana</h2></div><span class="count"><?= count($ownedCards) ?> found</span></div>
            <?php if (count($ownedCards) === 0): ?>
                <div class="empty-state"><span>☽</span><p>No cards recovered yet.</p><small>Draw an Arcana card to begin your collection.</small></div>
            <?php else: ?>
                <div class="card-grid">
                    <?php foreach ($ownedCards as $card): ?>
                        <article class="tarot-card">
                            <div class="card-symbol"><?= htmlspecialchars($card['symbol']) ?></div>
                            <p class="card-number"><?= htmlspecialchars($card['number']) ?></p>
                            <h3><?= htmlspecialchars($card['name']) ?></h3>
                            <p class="rarity"><?= htmlspecialchars($card['rarity']) ?></p>
                            <p class="card-description"><?= htmlspecialchars($card['description']) ?></p>
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
            <form method="post" onsubmit="return confirm('Reset all current progress?');"><input type="hidden" name="action" value="reset"><button class="reset-button" type="submit">Reset progress</button></form>
        </footer>
    </main>
</body>
</html>
