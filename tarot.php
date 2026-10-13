
<?php
require_once __DIR__ . '/config.php';

if (empty($_SESSION['player']['created'])) {
    arcana_redirect('character.php');
}

$player = &$_SESSION['player'];

if (!isset($player['cards']) || !is_array($player['cards'])) {
    $player['cards'] = [];
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

function tarotEscape($value) {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'draw') {
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

        $card = $availableCards[array_rand($availableCards)];

        $_SESSION['player']['cards'][] = $card;
        $_SESSION['tla_last_tarot'] = $card;
        $_SESSION['tla_last_card'] = $card;

        $_SESSION['tla_tarot_notice'] =
            'Kamu menemukan ' . $card['name'] .
            ' dengan rarity ' . $card['rarity'] . '.';
    } else {
        $_SESSION['tla_tarot_notice'] =
            'Aksi tidak dikenal.';
    }

    arcana_redirect('tarot.php');
}

$lastCard = $_SESSION['tla_last_tarot']
    ?? $_SESSION['tla_last_card']
    ?? null;

$cards = $player['cards'];

$notice = $_SESSION['tla_tarot_notice'] ?? '';
unset($_SESSION['tla_tarot_notice']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>THE LAST ARCANA | Tarot Archive</title>
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            background: #090711;
            color: #eee9ff;
        }

        .tarot-page {
            width: min(100% - 28px, 1000px);
            margin: 30px auto;
        }

        .tarot-header, .tarot-panel {
            background: linear-gradient(145deg, #191326, #0e0b17);
            border: 1px solid #51406d;
            border-radius: 18px;
            padding: 22px;
            margin-bottom: 20px;
            box-shadow: 0 0 24px #0008;
        }

        .tarot-header {
            text-align: center;
            border-color: #a88745;
        }

        .tarot-header h1 {
            color: #e7c77c;
            letter-spacing: 3px;
        }

        .muted {
            color: #a99bb9;
        }

        .tarot-card {
            width: min(100% - 44px, 290px);
            min-height: 300px;
            margin: 20px auto;
            padding: 22px;
            text-align: center;
            border: 2px solid var(--card-color, #8b8aff);
            border-radius: 18px;
            background:
                radial-gradient(ellipse at top, #352547, #100c18 75%);
            box-shadow: 0 0 28px #0009;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .tarot-symbol {
            font-size: 52px;
            color: var(--card-color, #b6b6c9);
            margin: 12px 0;
            text-shadow: 0 0 18px var(--card-color, #8b8aff);
        }

        .tarot-number {
            font-size: 13px;
            color: var(--card-color, #b6b6c9);
            letter-spacing: 3px;
        }

        .tarot-name {
            font-size: 23px;
            color: var(--card-color, #b6b6c9);
            margin: 12px 0;
        }

        .tarot-rarity {
            align-self: center;
            border: 1px solid var(--card-color, #8b8aff);
            color: var(--card-color, #b6b6c9);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            letter-spacing: 2px;
        }

        .tarot-button {
            display: block;
            box-sizing: border-box;
            width: 100%;
            padding: 14px;
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

        .tarot-button:hover {
            background: #39254e;
        }

        .gold-button {
            border-color: #b99a56;
            color: #f2d58b;
        }

        .tarot-notice {
            padding: 15px;
            margin-bottom: 20px;
            background: #201630;
            border: 1px solid #8b6ac0;
            border-radius: 12px;
        }

        .archive-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 13px;
        }

        .archive-card {
            min-width: 0;
            padding: 15px;
            border: 1px solid var(--card-color, #51406d);
            border-radius: 13px;
            background: #100d19;
        }

        .archive-symbol {
            font-size: 28px;
            color: var(--card-color, #8b8aff);
        }

        .archive-name {
            color: var(--card-color, #b6b6c9);
            overflow-wrap: anywhere;
        }

        .archive-rarity {
            font-size: 10px;
            letter-spacing: 1px;
            color: var(--card-color, #b6b6c9);
        }

        .tarot-footer {
            text-align: center;
            color: #80738f;
            font-size: 12px;
            padding: 20px 0;
        }

        @media (max-width: 420px) {
            .tarot-page {
                width: min(100% - 18px, 1000px);
            }

            .tarot-header, .tarot-panel {
                padding: 15px;
            }

            .archive-grid {
                gap: 9px;
            }

            .archive-card {
                padding: 11px;
            }
        }
    </style>
</head>
<body>
<main class="tarot-page">

    <header class="tarot-header">
        <p class="muted">THE OCCULT COLLECTION</p>
        <h1>✧ TAROT ARCHIVE</h1>
        <p>Eight Arcana. Endless mysteries.</p>
    </header>

    <?php if ($notice !== ''): ?>
        <div class="tarot-notice">
            <?= tarotEscape($notice) ?>
        </div>
    <?php endif; ?>

    <section class="tarot-panel">
        <h2>✧ LATEST DISCOVERY</h2>

        <?php if ($lastCard): ?>
            <div class="tarot-card"
                 style="--card-color: <?= tarotEscape($lastCard['color'] ?? '#8b8aff') ?>">

                <div class="tarot-number">
                    ARCANA <?= tarotEscape($lastCard['number'] ?? '?') ?>
                </div>

                <div class="tarot-symbol">✧</div>

                <h2 class="tarot-name">
                    <?= tarotEscape($lastCard['name'] ?? 'Unknown') ?>
                </h2>

                <span class="tarot-rarity">
                    <?= tarotEscape($lastCard['rarity'] ?? 'UNKNOWN') ?>
                </span>

                <p>
                    <?= tarotEscape(
                        $lastCard['description']
                        ?? $lastCard['effect']
                        ?? 'Misteri yang belum terungkap.'
                    ) ?>
                </p>
            </div>
        <?php else: ?>
            <p class="muted">
                Belum ada kartu yang ditemukan.
                Tarik kartu pertamamu untuk membuka misteri Arcana.
            </p>
        <?php endif; ?>

        <form method="post">
            <button type="submit" name="action" value="draw"
                    class="tarot-button gold-button">
                ✧ DRAW A NEW TAROT
            </button>
        </form>
    </section>

    <section class="tarot-panel">
        <h2>🃏 YOUR COLLECTION</h2>

        <p class="muted">
            Total kartu ditemukan: <?= count($cards) ?>
        </p>

        <?php if (!empty($cards)): ?>
            <div class="archive-grid">
                <?php foreach (array_reverse($cards) as $card): ?>
                    <article class="archive-card"
                             style="--card-color: <?= tarotEscape($card['color'] ?? '#8b8aff') ?>">

                        <div class="archive-symbol">✧</div>

                        <div class="muted">
                            ARCANA <?= tarotEscape($card['number'] ?? '?') ?>
                        </div>

                        <h3 class="archive-name">
                            <?= tarotEscape($card['name'] ?? 'Unknown') ?>
                        </h3>

                        <div class="archive-rarity">
                            <?= tarotEscape($card['rarity'] ?? 'UNKNOWN') ?>
                        </div>

                        <p>
                            <?= tarotEscape(
                                $card['description']
                                ?? $card['effect']
                                ?? 'Misteri yang belum terungkap.'
                            ) ?>
                        </p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="muted">
                Koleksimu masih kosong. Gunakan tombol DRAW A NEW TAROT
                untuk mendapatkan kartu pertama.
            </p>
        <?php endif; ?>
    </section>

    <section class="tarot-panel">
        <a href="game.php" class="tarot-button">
            ← KEMBALI KE GAME
        </a>
    </section>

    <footer class="tarot-footer">
        THE LAST ARCANA · Every card has a secret.
    </footer>

</main>
</body>
</html>
