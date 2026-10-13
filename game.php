<?php
require_once __DIR__ . '/config.php';

if (
    !isset($_SESSION['player']) ||
    empty($_SESSION['player']['created'])
) {
    header('Location: character.php');
    exit;
}

$player = &$_SESSION['player'];

function gameEscape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/* Kartu Tarot */
$tarotDeck = [
    [
        'name' => 'The Fool',
        'number' => '0',
        'rarity' => 'COMMON',
        'color' => '#b6b6c9',
        'description' => 'Awal dari perjalanan yang belum diketahui.'
    ],
    [
        'name' => 'The Magician',
        'number' => 'I',
        'rarity' => 'UNCOMMON',
        'color' => '#71c9b3',
        'description' => 'Simbol kemampuan dan kekuatan yang tersembunyi.'
    ],
    [
        'name' => 'The Hermit',
        'number' => 'IX',
        'rarity' => 'RARE',
        'color' => '#8b8aff',
        'description' => 'Cahaya kecil di tengah kegelapan.'
    ],
    [
        'name' => 'The High Priestess',
        'number' => 'II',
        'rarity' => 'EPIC',
        'color' => '#d18aff',
        'description' => 'Penjaga rahasia yang tidak diketahui dunia.'
    ],
    [
        'name' => 'The Star',
        'number' => 'XVII',
        'rarity' => 'LEGENDARY',
        'color' => '#ffd879',
        'description' => 'Harapan yang bertahan di tengah kehancuran.'
    ],
    [
        'name' => 'The Tower',
        'number' => 'XVI',
        'rarity' => 'MYTHIC',
        'color' => '#ff737d',
        'description' => 'Perubahan besar yang meruntuhkan segalanya.'
    ],
    [
        'name' => 'Death',
        'number' => 'XIII',
        'rarity' => 'EPIC',
        'color' => '#d18aff',
        'description' => 'Akhir dari satu perjalanan dan awal yang baru.'
    ],
    [
        'name' => 'The Moon',
        'number' => 'XVIII',
        'rarity' => 'RARE',
        'color' => '#8b8aff',
        'description' => 'Ilusi dan misteri yang bersembunyi di balik malam.'
    ]
];

/* Data monster */
$enemyNames = [
    'Veilborn Wraith',
    'Hollow Stalker',
    'Crimson Watcher',
    'Abyssal Hound',
    'Forgotten Knight',
    'The Nameless'
];

function createEnemy($stage, $names) {
    $index = min($stage - 1, count($names) - 1);

    $hp = 35 + ($stage * 12);

    return [
        'name' => $names[$index],
        'hp' => $hp,
        'max_hp' => $hp,
        'attack' => 7 + ($stage * 2),
        'exp' => 35 + ($stage * 15)
    ];
}

/* Pastikan data karakter tersedia */
$defaults = [
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

if (!isset($_SESSION['tla_enemy'])) {
    $_SESSION['tla_enemy'] = createEnemy(
        $player['stage'],
        $enemyNames
    );
}

$enemy = &$_SESSION['tla_enemy'];

/* Pulihkan notifikasi setelah redirect */
$notice = $_SESSION['tla_notice'] ?? '';
unset($_SESSION['tla_notice']);

/* Level up */
function checkLevelUp(&$player) {
    $messages = [];

    while ($player['exp'] >= $player['level'] * 100) {
        $neededExp = $player['level'] * 100;
        $player['exp'] -= $neededExp;
        $player['level']++;

        $player['max_hp'] += 15;
        $player['max_mana'] += 10;
        $player['attack'] += 3;
        $player['defense'] += 2;

        $player['hp'] = $player['max_hp'];
        $player['mana'] = $player['max_mana'];

        $messages[] = 'LEVEL UP! Kamu sekarang Level ' .
            $player['level'] . '.';
    }

    return $messages;
}

/* Proses aksi game */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $messages = [];

    if ($player['hp'] <= 0 && $action !== 'recover') {
        $messages[] = 'Karakter kehabisan HP. Pulihkan diri dahulu.';
    } else {
        switch ($action) {

            case 'attack':
                $damage = max(
                    1,
                    $player['attack'] + random_int(-3, 5)
                );

                $enemy['hp'] -= $damage;

                $messages[] = 'Kamu menyerang ' .
                    $enemy['name'] . ' sebesar ' . $damage . ' damage.';

                if ($enemy['hp'] <= 0) {
                    $enemyName = $enemy['name'];
                    $reward = $enemy['exp'];

                    $player['exp'] += $reward;
                    $player['stage']++;

                    $messages[] = $enemyName .
                        ' berhasil dikalahkan! +' .
                        $reward . ' EXP.';

                    $levelMessages = checkLevelUp($player);

                    foreach ($levelMessages as $levelMessage) {
                        $messages[] = $levelMessage;
                    }

                    $_SESSION['tla_enemy'] = createEnemy(
                        $player['stage'],
                        $enemyNames
                    );
                } else {
                    $enemyDamage = max(
                        1,
                        $enemy['attack'] +
                        random_int(-2, 3) -
                        $player['defense']
                    );

                    $player['hp'] = max(
                        0,
                        $player['hp'] - $enemyDamage
                    );

                    $messages[] = $enemy['name'] .
                        ' membalas sebesar ' .
                        $enemyDamage . ' damage.';
                }
                break;

            case 'draw':
                $previous = $_SESSION['tla_last_card']['name'] ?? '';

                $availableCards = array_values(array_filter(
                    $tarotDeck,
                    fn($card) => $card['name'] !== $previous
                ));

                $drawnCard = $availableCards[
                    random_int(0, count($availableCards) - 1)
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

                $messages[] = 'HP dan mana berhasil dipulihkan.';
                break;

            default:
                $messages[] = 'Aksi tidak dikenal.';
        }
    }

    $_SESSION['tla_notice'] = implode(' ', $messages);

    header('Location: game.php');
    exit;
}

/* Data untuk tampilan */
$hpPercent = $player['max_hp'] > 0
    ? max(0, min(100, ($player['hp'] / $player['max_hp']) * 100))
    : 0;

$manaPercent = $player['max_mana'] > 0
    ? max(0, min(100, ($player['mana'] / $player['max_mana']) * 100))
    : 0;

$expNeeded = $player['level'] * 100;

$expPercent = $expNeeded > 0
    ? max(0, min(100, ($player['exp'] / $expNeeded) * 100))
    : 0;

$lastCard = $_SESSION['tla_last_card'] ?? null;
$cards = array_reverse($player['cards']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Last Arcana — The Awakening</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .game-wrap {
            width: min(1100px, 92%);
            margin: 35px auto;
            color: #eee8ff;
        }

        .game-heading {
            text-align: center;
            margin-bottom: 30px;
        }

        .game-heading small {
            color: #c6a6ff;
            letter-spacing: 4px;
        }

        .game-heading h1 {
            font-size: clamp(28px, 6vw, 46px);
            margin: 12px 0;
        }

        .game-panel {
            background: rgba(18, 13, 35, .88);
            border: 1px solid #6e4b99;
            border-radius: 15px;
            padding: 22px;
            margin-bottom: 20px;
            box-shadow: 0 0 25px rgba(123, 65, 180, .12);
        }

        .game-panel h2,
        .game-panel h3 {
            color: #e0c18b;
        }

        .game-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .stat-line {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin: 12px 0 6px;
            font-size: 14px;
        }

        .stat-track {
            height: 9px;
            border-radius: 20px;
            background: #302741;
            overflow: hidden;
        }

        .stat-fill {
            height: 100%;
            border-radius: 20px;
            transition: width .3s ease;
        }

        .hp-fill { background: #e75b79; }
        .mana-fill { background: #8d77ff; }
        .exp-fill { background: #d8b36a; }

        .game-button {
            display: block;
            width: 100%;
            border: 1px solid #7959a0;
            background: #211630;
            color: #f6edff;
            padding: 14px 10px;
            border-radius: 10px;
            font-family: inherit;
            font-size: 14px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            box-sizing: border-box;
            transition: .2s;
        }

        .game-button:hover {
            background: #42275e;
            border-color: #d5b1ff;
            transform: translateY(-2px);
        }

        .game-button.primary {
            background: linear-gradient(135deg, #54347a, #34204f);
            border-color: #c9a6ef;
        }

        .game-button.gold {
            border-color: #a58a50;
            color: #f4dca5;
        }

        .game-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .enemy-name {
            font-size: 23px;
            color: #ff9aaf;
            margin: 8px 0;
        }

        .enemy-symbol {
            width: 110px;
            height: 110px;
            display: grid;
            place-items: center;
            margin: 15px auto;
            border: 1px solid #a45182;
            border-radius: 50%;
            color: #ff91bd;
            font-size: 44px;
            background: radial-gradient(circle, #49213f, #171020 70%);
            box-shadow: 0 0 30px #8b285544;
        }

        .game-notice {
            padding: 15px;
            border: 1px solid #a58a50;
            background: #2a211b;
            color: #f1d8a1;
            border-radius: 10px;
            margin-bottom: 20px;
            line-height: 1.7;
        }

        .tarot-card {
            max-width: 300px;
            margin: 16px auto;
            padding: 18px;
            text-align: center;
            border: 2px solid var(--card-color, #9c83ca);
            border-radius: 14px;
            background:
                radial-gradient(ellipse at center, #352044, #100c1c 75%);
            box-shadow: 0 0 25px color-mix(in srgb, var(--card-color, #9c83ca) 20%, transparent);
        }

        .tarot-number {
            font-size: 54px;
            color: var(--card-color, #c8b4ed);
            margin: 15px 0;
            font-family: Georgia, serif;
        }

        .tarot-rarity {
            font-size: 11px;
            letter-spacing: 3px;
            color: var(--card-color, #c8b4ed);
        }

        .tarot-card h3 {
            color: #f1dfb7;
            margin: 12px 0;
        }

        .tarot-card p {
            color: #c6bad8;
            line-height: 1.6;
            font-size: 14px;
        }

        .archive-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(135px, 1fr));
            gap: 12px;
        }

        .archive-card {
            border: 1px solid #55406f;
            background: #171121;
            padding: 15px 10px;
            text-align: center;
            border-radius: 10px;
        }

        .archive-card .tarot-number {
            font-size: 32px;
        }

        .archive-card strong {
            display: block;
            color: #ead9ff;
            font-size: 13px;
        }

        .archive-card small {
            display: block;
            margin-top: 8px;
            color: #c2a1ed;
            font-size: 10px;
            letter-spacing: 1px;
        }

        .game-footer {
            text-align: center;
            padding: 25px 10px;
            color: #9e90b2;
            font-size: 12px;
        }

        @media (max-width: 650px) {
            .game-grid {
                grid-template-columns: 1fr;
            }

            .game-panel {
                padding: 17px;
            }

            .game-actions {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>
    <main class="game-wrap">

        <header class="game-heading">
            <small>OCCULT SURVIVAL RPG</small>
            <h1>THE LAST ARCANA</h1>
            <p>Fate has chosen you. Survive the unknown.</p>
        </header>

        <?php if ($notice !== ''): ?>
            <div class="game-notice">
                ✦ <?= gameEscape($notice) ?>
            </div>
        <?php endif; ?>

        <section class="game-panel">
            <h2>✦ PLAYER STATUS</h2>
            <div class="game-grid">
                <div>
                    <p>Nama karakter</p>
                    <h3><?= gameEscape($player['name']) ?></h3>
                    <p>Kelas: <?= gameEscape($player['class']) ?></p>
                    <p>Level: <strong><?= (int)$player['level'] ?></strong></p>
                    <p>Stage: <strong><?= (int)$player['stage'] ?></strong></p>
                </div>

                <div>
                    <div class="stat-line">
                        <span>HP</span>
                        <span><?= (int)$player['hp'] ?> / <?= (int)$player['max_hp'] ?></span>
                    </div>
                    <div class="stat-track">
                        <div class="stat-fill hp-fill"
                             style="width: <?= $hpPercent ?>%"></div>
                    </div>

                    <div class="stat-line">
                        <span>MANA</span>
                        <span><?= (int)$player['mana'] ?> / <?= (int)$player['max_mana'] ?></span>
                    </div>
                    <div class="stat-track">
                        <div class="stat-fill mana-fill"
                             style="width: <?= $manaPercent ?>%"></div>
                    </div>

                    <div class="stat-line">
                        <span>EXP</span>
                        <span><?= (int)$player['exp'] ?> / <?= $expNeeded ?></span>
                    </div>
                    <div class="stat-track">
                        <div class="stat-fill exp-fill"
                             style="width: <?= $expPercent ?>%"></div>
                    </div>
                </div>
            </div>

            <div class="game-grid">
                <p>⚔ ATK: <strong><?= (int)$player['attack'] ?></strong></p>
                <p>🛡 DEF: <strong><?= (int)$player['defense'] ?></strong></p>
            </div>
        </section>

        <section class="game-panel">
            <h2>☠ ENCOUNTER</h2>
            <div class="enemy-symbol">☠</div>
            <p style="text-align:center;color:#a99bbd;">CURRENT ENTITY</p>
            <h3 class="enemy-name" style="text-align:center;">
                <?= gameEscape($enemy['name']) ?>
            </h3>

            <div class="stat-line">
                <span>Monster HP</span>
                <span><?= max(0, (int)$enemy['hp']) ?> / <?= (int)$enemy['max_hp'] ?></span>
            </div>
            <div class="stat-track">
                <div class="stat-fill hp-fill"
                     style="width: <?= max(0, min(100, ($enemy['hp'] / $enemy['max_hp']) * 100)) ?>%"></div>
            </div>

            <p>Monster ATK: <?= (int)$enemy['attack'] ?></p>

            <div class="game-actions">
                <form method="post">
                    <button class="game-button primary" name="action" value="attack">
                        ⚔ ATTACK
                    </button>
                </form>

                <form method="post">
                    <button class="game-button" name="action" value="meditate">
                        ✧ MEDITATE
                    </button>
                </form>

                <form method="post">
                    <button class="game-button gold" name="action" value="draw">
                        ✦ DRAW TAROT
                    </button>
                </form>

                <form method="post">
                    <button class="game-button" name="action" value="recover">
                        ♡ RECOVER
                    </button>
                </form>
            </div>
        </section>

        <section class="game-panel">
            <h2>✧ YOUR LATEST ARCANA</h2>

            <?php if ($lastCard): ?>
                <div class="tarot-card"
                     style="--card-color: <?= gameEscape($lastCard['color']) ?>;">
                    <div class="tarot-rarity">
                        <?= gameEscape($lastCard['rarity']) ?> ARCANA
                    </div>
                    <div class="tarot-number">
                        <?= gameEscape($lastCard['number']) ?>
                    </div>
                    <h3><?= gameEscape($lastCard['name']) ?></h3>
                    <p><?= gameEscape($lastCard['description']) ?></p>
                </div>
            <?php else: ?>
                <p style="text-align:center;color:#b7a8cb;">
                    Belum ada kartu yang ditemukan. Tekan DRAW TAROT untuk memulai.
                </p>
            <?php endif; ?>
        </section>

        <section class="game-panel">
            <h2>▤ ARCANA ARCHIVE</h2>
            <p>Total kartu ditemukan: <strong><?= count($player['cards']) ?></strong></p>

            <?php if (count($cards) > 0): ?>
                <div class="archive-grid">
                    <?php foreach ($cards as $card): ?>
                        <div class="archive-card"
                             style="border-color: <?= gameEscape($card['color']) ?>;">
                            <div class="tarot-number"
                                 style="color: <?= gameEscape($card['color']) ?>;">
                                <?= gameEscape($card['number']) ?>
                            </div>
                            <strong><?= gameEscape($card['name']) ?></strong>
                            <small><?= gameEscape($card['rarity']) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color:#b7a8cb;">Arsip masih kosong.</p>
            <?php endif; ?>
        </section>

        
<a href="tarot.php" class="game-button">
    ✧ TAROT ARCHIVE
</a>

<a class="game-button" href="index.php">
    ← KEMBALI KE AWAL
</a>

<footer class="game-footer">
    THE LAST ARCANA · Every card has a secret.
</footer>
</main>
</body>
</html>
