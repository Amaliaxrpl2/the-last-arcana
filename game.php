
<?php
require_once __DIR__ . '/config.php';

if (empty($_SESSION['player']['created'])) {
    arcana_redirect('character.php');
}

$player = &$_SESSION['player'];

$defaults = [
    'name' => 'Unknown',
    'class' => 'Arcanist',
    'level' => 1,
    'exp' => 0,
    'hp' => 100,
    'max_hp' => 100,
    'mana' => 100,
    'max_mana' => 100,
    'attack' => 10,
    'defense' => 5,
    'stage' => 1,
    'cards' => []
];

foreach ($defaults as $key => $value) {
    if (!isset($player[$key])) {
        $player[$key] = $value;
    }
}

$deck = [
    [
        'name' => 'The Fool',
        'number' => '0',
        'rarity' => 'COMMON',
        'color' => '#b6b6c9',
        'description' => 'Awal dari perjalanan yang tidak diketahui.'
    ],
    [
        'name' => 'The Magician',
        'number' => 'I',
        'rarity' => 'UNCOMMON',
        'color' => '#71c9b3',
        'description' => 'Meningkatkan potensi kekuatan Arcana.'
    ],
    [
        'name' => 'The Hermit',
        'number' => 'IX',
        'rarity' => 'RARE',
        'color' => '#8b8aff',
        'description' => 'Kebijaksanaan tersembunyi dari kegelapan.'
    ],
    [
        'name' => 'The High Priestess',
        'number' => 'II',
        'rarity' => 'EPIC',
        'color' => '#d18aff',
        'description' => 'Membuka rahasia yang tidak terlihat.'
    ],
    [
        'name' => 'The Star',
        'number' => 'XVII',
        'rarity' => 'LEGENDARY',
        'color' => '#ffd879',
        'description' => 'Cahaya harapan di tengah kehancuran.'
    ],
    [
        'name' => 'The Tower',
        'number' => 'XVI',
        'rarity' => 'MYTHIC',
        'color' => '#ff737d',
        'description' => 'Kehancuran yang mengubah takdir.'
    ],
    [
        'name' => 'Death',
        'number' => 'XIII',
        'rarity' => 'EPIC',
        'color' => '#d18aff',
        'description' => 'Simbol akhir dan awal yang baru.'
    ],
    [
        'name' => 'The Moon',
        'number' => 'XVIII',
        'rarity' => 'RARE',
        'color' => '#8b8aff',
        'description' => 'Mengungkap ilusi dan misteri.'
    ]
];

$enemyNames = [
    'Veilborn Wraith',
    'Hollow Stalker',
    'Crimson Watcher',
    'Abyssal Hound',
    'Forgotten Knight',
    'The Nameless'
];

function gameEscape($value) {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

function createEnemy($stage, $names) {
    $index = min(
        count($names) - 1,
        intdiv(max(0, $stage - 1), 2)
    );

    return [
        'name' => $names[$index],
        'hp' => 35 + ($stage * 12),
        'max_hp' => 35 + ($stage * 12),
        'attack' => 7 + ($stage * 2),
        'exp' => 35 + ($stage * 15)
    ];
}

function checkLevelUp(&$player) {
    $messages = [];

    while ($player['exp'] >= $player['level'] * 100) {
        $player['exp'] -= $player['level'] * 100;
        $player['level']++;

        $player['max_hp'] += 15;
        $player['max_mana'] += 10;
        $player['attack'] += 3;
        $player['defense'] += 2;

        $player['hp'] = $player['max_hp'];
        $player['mana'] = $player['max_mana'];

        $messages[] = 'LEVEL UP! Kamu mencapai Level ' .
            $player['level'] . '.';
    }

    return $messages;
}

if (
    !isset($_SESSION['tla_enemy']) ||
    !is_array($_SESSION['tla_enemy'])
) {
    $_SESSION['tla_enemy'] = createEnemy(
        $player['stage'],
        $enemyNames
    );
}

$enemy = &$_SESSION['tla_enemy'];

if (!isset($_SESSION['tla_notice'])) {
    $_SESSION['tla_notice'] = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $messages = [];

    if ($player['hp'] <= 0 && $action !== 'recover') {
        $messages[] = 'Kamu kehabisan HP. Pulihkan diri terlebih dahulu.';
    } else {
        switch ($action) {
            case 'attack':
                $damage = max(
                    1,
                    $player['attack'] - 2
                );

                $enemy['hp'] = max(
                    0,
                    $enemy['hp'] - $damage
                );

                $messages[] = 'Kamu menyerang ' .
                    $enemy['name'] . ' sebesar ' .
                    $damage . ' damage.';

                if ($enemy['hp'] <= 0) {
                    $earnedExp = $enemy['exp'];

                    $player['exp'] += $earnedExp;
                    $player['stage']++;

                    $messages[] = 'Monster dikalahkan! Kamu mendapat ' .
                        $earnedExp . ' EXP.';

                    $levelMessages = checkLevelUp($player);

                    foreach ($levelMessages as $message) {
                        $messages[] = $message;
                    }

                    $_SESSION['tla_enemy'] = createEnemy(
                        $player['stage'],
                        $enemyNames
                    );

                    $enemy = &$_SESSION['tla_enemy'];
                } else {
                    $enemyDamage = max(
                        1,
                        $enemy['attack'] - $player['defense']
                    );

                    $player['hp'] = max(
                        0,
                        $player['hp'] - $enemyDamage
                    );

                    $messages[] = $enemy['name'] .
                        ' membalas dengan ' .
                        $enemyDamage . ' damage.';
                }
                break;

            case 'draw':
                $lastName = $_SESSION['tla_last_tarot']['name']
                    ?? $_SESSION['tla_last_card']['name']
                    ?? '';

                $availableCards = array_values(
                    array_filter(
                        $deck,
                        function ($card) use ($lastName) {
                            return $card['name'] !== $lastName;
                        }
                    )
                );

                if (empty($availableCards)) {
                    $availableCards = $deck;
                }

                $drawnCard = $availableCards[
                    array_rand($availableCards)
                ];

                $_SESSION['tla_last_card'] = $drawnCard;
                $_SESSION['tla_last_tarot'] = $drawnCard;

                $player['cards'][] = $drawnCard;

                $messages[] = 'Kartu Tarot berhasil ditemukan: ' .
                    $drawnCard['name'] . ' (' .
                    $drawnCard['rarity'] . ').';
                break;

            case 'meditate':
                if ($player['mana'] < 10) {
                    $messages[] = 'Mana tidak cukup untuk bermeditasi.';
                } else {
                    $player['mana'] -= 10;

                    $oldHp = $player['hp'];

                    $player['hp'] = min(
                        $player['max_hp'],
                        $player['hp'] + 25
                    );

                    $restored = $player['hp'] - $oldHp;

                    $messages[] = 'Meditasi selesai. HP pulih ' .
                        $restored . ' poin.';
                }
                break;

            case 'recover':
                $player['hp'] = $player['max_hp'];
                $player['mana'] = $player['max_mana'];

                $messages[] = 'HP dan Mana berhasil dipulihkan.';
                break;

            default:
                $messages[] = 'Aksi tidak dikenal.';
        }
    }

    $_SESSION['tla_notice'] = implode(' ', $messages);

    arcana_redirect('game.php');
}

$notice = $_SESSION['tla_notice'] ?? '';
unset($_SESSION['tla_notice']);

$lastCard = $_SESSION['tla_last_tarot']
    ?? $_SESSION['tla_last_card']
    ?? null;

$expNeeded = $player['level'] * 100;

$hpPercent = $player['max_hp'] > 0
    ? max(0, min(100, ($player['hp'] / $player['max_hp']) * 100))
    : 0;

$manaPercent = $player['max_mana'] > 0
    ? max(0, min(100, ($player['mana'] / $player['max_mana']) * 100))
    : 0;

$expPercent = $expNeeded > 0
    ? max(0, min(100, ($player['exp'] / $expNeeded) * 100))
    : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>THE LAST ARCANA | Game</title>
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            background: #090711;
            color: #eee9ff;
        }

        .game-page {
            width: min(100% - 28px, 1000px);
            margin: 30px auto;
        }

        .game-header, .game-panel, .notice-panel {
            background: linear-gradient(145deg, #191326, #0e0b17);
            border: 1px solid #51406d;
            border-radius: 18px;
            padding: 22px;
            margin-bottom: 20px;
            box-shadow: 0 0 24px #0008;
        }

        .game-header {
            text-align: center;
            border-color: #a88745;
        }

        .game-header h1 {
            color: #e7c77c;
            letter-spacing: 3px;
        }

        .game-subtitle {
            color: #a99abb;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .stat-box {
            background: #100d19;
            border: 1px solid #392c50;
            padding: 13px;
            border-radius: 12px;
        }

        .stat-label {
            color: #b9abc9;
            font-size: 12px;
            text-transform: uppercase;
        }

        .stat-value {
            display: block;
            font-size: 19px;
            font-weight: bold;
            margin-top: 5px;
        }

        .bar {
            background: #30253e;
            height: 9px;
            border-radius: 20px;
            overflow: hidden;
            margin-top: 8px;
        }

        .bar-fill {
            height: 100%;
            border-radius: 20px;
        }

        .hp-fill {
            background: #e95c75;
        }

        .mana-fill {
            background: #7b8dff;
        }

        .exp-fill {
            background: #e4c16c;
        }

        .game-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .game-button {
            display: block;
            box-sizing: border-box;
            width: 100%;
            padding: 13px 10px;
            background: #251936;
            border: 1px solid #8061a8;
            color: #f5eaff;
            border-radius: 10px;
            text-align: center;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
        }

        .game-button:hover {
            background: #39254e;
        }

        .gold-button {
            border-color: #b99a56;
            color: #f2d58b;
        }

        .notice-panel {
            border-color: #8b6ac0;
            color: #e8d8ff;
        }

        .enemy-name {
            color: #ff8c9c;
            font-size: 23px;
        }

        .enemy-bar {
            background: #39212c;
            height: 12px;
            border-radius: 20px;
            overflow: hidden;
        }

        .enemy-fill {
            height: 100%;
            background: #d84f70;
        }

        .tarot-card {
            max-width: 310px;
            margin: 12px auto;
            padding: 20px;
            text-align: center;
            border: 2px solid var(--card-color, #8b8aff);
            border-radius: 18px;
            background: radial-gradient(ellipse at top, #352547, #100c18 75%);
            box-shadow: 0 0 25px #0008;
        }

        .tarot-number {
            font-size: 14px;
            color: var(--card-color, #b6b6c9);
        }

        .tarot-name {
            color: var(--card-color, #b6b6c9);
            font-size: 23px;
            margin: 15px 0;
        }

        .tarot-rarity {
            display: inline-block;
            border: 1px solid var(--card-color, #8b8aff);
            color: var(--card-color, #b6b6c9);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            letter-spacing: 2px;
        }

        .muted {
            color: #a99bb9;
        }

        .game-footer {
            text-align: center;
            color: #80738f;
            font-size: 12px;
            padding: 20px 0;
        }

        @media (max-width: 420px) {
            .game-page {
                width: min(100% - 18px, 1000px);
            }

            .game-header, .game-panel, .notice-panel {
                padding: 15px;
            }

            .game-button {
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
<main class="game-page">

    <header class="game-header">
        <p class="game-subtitle">OCCULT SURVIVAL RPG</p>
        <h1>THE LAST ARCANA</h1>
        <p>Every card has a secret.</p>
    </header>

    <?php if ($notice !== ''): ?>
        <section class="notice-panel">
            <?= gameEscape($notice) ?>
        </section>
    <?php endif; ?>

    <section class="game-panel">
        <h2>✧ CHARACTER STATUS</h2>
        <h3><?= gameEscape($player['name']) ?></h3>
        <p class="muted">
            <?= gameEscape($player['class']) ?>
            · Level <?= (int) $player['level'] ?>
            · Stage <?= (int) $player['stage'] ?>
        </p>

        <div class="stat-grid">
            <div class="stat-box">
                <span class="stat-label">HP</span>
                <span class="stat-value">
                    <?= (int) $player['hp'] ?> /
                    <?= (int) $player['max_hp'] ?>
                </span>
                <div class="bar">
                    <div class="bar-fill hp-fill"
                         style="width: <?= $hpPercent ?>%"></div>
                </div>
            </div>

            <div class="stat-box">
                <span class="stat-label">Mana</span>
                <span class="stat-value">
                    <?= (int) $player['mana'] ?> /
                    <?= (int) $player['max_mana'] ?>
                </span>
                <div class="bar">
                    <div class="bar-fill mana-fill"
                         style="width: <?= $manaPercent ?>%"></div>
                </div>
            </div>

            <div class="stat-box">
                <span class="stat-label">EXP</span>
                <span class="stat-value">
                    <?= (int) $player['exp'] ?> / <?= $expNeeded ?>
                </span>
                <div class="bar">
                    <div class="bar-fill exp-fill"
                         style="width: <?= $expPercent ?>%"></div>
                </div>
            </div>

            <div class="stat-box">
                <span class="stat-label">Attack / Defense</span>
                <span class="stat-value">
                    <?= (int) $player['attack'] ?> /
                    <?= (int) $player['defense'] ?>
                </span>
            </div>
        </div>
    </section>

    <section class="game-panel">
        <h2>☠️ ENCOUNTER</h2>
        <h3 class="enemy-name">
            <?= gameEscape($enemy['name']) ?>
        </h3>

        <p class="muted">
            Enemy HP: <?= (int) $enemy['hp'] ?> /
            <?= (int) $enemy['max_hp'] ?>
        </p>

        <div class="enemy-bar">
            <div class="enemy-fill"
                 style="width: <?= max(0, min(100, ($enemy['hp'] / max(1, $enemy['max_hp'])) * 100)) ?>%">
            </div>
        </div>

        <p class="muted">
            ATK <?= (int) $enemy['attack'] ?>
            · Reward <?= (int) $enemy['exp'] ?> EXP
        </p>

        <div class="game-actions">
            <form method="post">
                <button class="game-button" name="action"
                        value="attack" type="submit">
                    ⚔️ ATTACK
                </button>
            </form>

            <form method="post">
                <button class="game-button" name="action"
                        value="draw" type="submit">
                    🔮 DRAW TAROT
                </button>
            </form>

            <form method="post">
                <button class="game-button" name="action"
                        value="meditate" type="submit">
                    ✧ MEDITATE (-10 MANA)
                </button>
            </form>

            <form method="post">
                <button class="game-button gold-button" name="action"
                        value="recover" type="submit">
                    ♥ RECOVER
                </button>
            </form>
        </div>
    </section>

    <section class="game-panel">
        <h2>🃏 LAST DISCOVERED ARCANA</h2>

        <?php if ($lastCard): ?>
            <div class="tarot-card"
                 style="--card-color: <?= gameEscape($lastCard['color'] ?? '#8b8aff') ?>">
                <div class="tarot-number">
                    ARCANA <?= gameEscape($lastCard['number'] ?? '?') ?>
                </div>

                <h3 class="tarot-name">
                    <?= gameEscape($lastCard['name'] ?? 'Unknown') ?>
                </h3>

                <span class="tarot-rarity">
                    <?= gameEscape($lastCard['rarity'] ?? 'UNKNOWN') ?>
                </span>

                <p>
                    <?= gameEscape(
                        $lastCard['description']
                        ?? $lastCard['effect']
                        ?? 'Misteri yang belum terungkap.'
                    ) ?>
                </p>
            </div>
        <?php else: ?>
            <p class="muted">
                Belum ada kartu yang ditemukan. Tekan DRAW TAROT
                untuk memulai penemuan Arcana pertamamu.
            </p>
        <?php endif; ?>
    </section>

    <section class="game-panel">
        <h2>✧ QUICK MENU</h2>

        <div class="game-actions">
            <a href="tarot.php" class="game-button gold-button">
                🃏 TAROT ARCHIVE
            </a>

            <a href="index.php" class="game-button">
                ← KEMBALI KE AWAL
            </a>
        </div>

        <p class="muted">
            Total kartu terkumpul: <?= count($player['cards']) ?>
        </p>
    </section>

    <footer class="game-footer">
        THE LAST ARCANA · Every card has a secret.
    </footer>

</main>
</body>
</html>
