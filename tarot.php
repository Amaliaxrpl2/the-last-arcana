
<?php
require_once __DIR__ . '/config.php';

if (empty($_SESSION['player']['created'])) {
    arcana_redirect('character.php');
}

$deck = [
    [
        'name' => 'The Fool',
        'number' => '0',
        'rarity' => 'COMMON',
        'color' => '#aaa4bd',
        'effect' => 'Awal dari perjalanan yang tidak diketahui.'
    ],
    [
        'name' => 'The Magician',
        'number' => 'I',
        'rarity' => 'UNCOMMON',
        'color' => '#72d6c2',
        'effect' => 'Meningkatkan potensi kekuatan Arcana.'
    ],
    [
        'name' => 'The Hermit',
        'number' => 'IX',
        'rarity' => 'RARE',
        'color' => '#70a6ff',
        'effect' => 'Kebijaksanaan tersembunyi dari kegelapan.'
    ],
    [
        'name' => 'The High Priestess',
        'number' => 'II',
        'rarity' => 'EPIC',
        'color' => '#c79aff',
        'effect' => 'Membuka rahasia yang tidak terlihat.'
    ],
    [
        'name' => 'The Star',
        'number' => 'XVII',
        'rarity' => 'LEGENDARY',
        'color' => '#ffd777',
        'effect' => 'Cahaya harapan di tengah kehancuran.'
    ],
    [
        'name' => 'The Moon',
        'number' => 'XVIII',
        'rarity' => 'LEGENDARY',
        'color' => '#9da9ff',
        'effect' => 'Mengungkap ilusi dan misteri.'
    ],
    [
        'name' => 'Death',
        'number' => 'XIII',
        'rarity' => 'MYTHIC',
        'color' => '#ff647c',
        'effect' => 'Simbol akhir dan awal yang baru.'
    ],
    [
        'name' => 'The Tower',
        'number' => 'XVI',
        'rarity' => 'MYTHIC',
        'color' => '#ff805e',
        'effect' => 'Kehancuran yang mengubah takdir.'
    ]
];

if (!isset($_SESSION['player']['cards']) ||
    !is_array($_SESSION['player']['cards'])) {
    $_SESSION['player']['cards'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['draw'])) {

    $card = $deck[array_rand($deck)];

    $_SESSION['player']['cards'][] = $card;
    $_SESSION['tla_last_tarot'] = $card;

    arcana_redirect('tarot.php');
}

$lastCard = $_SESSION['tla_last_tarot'] ?? null;
$cards = $_SESSION['player']['cards'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarot Archive | The Last Arcana</title>
    <link rel="stylesheet" href="style.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            padding: 25px 16px;
            color: #f2eafa;
            background:
                radial-gradient(ellipse at top, #28163e 0%, #100b19 48%, #08070d 100%);
            font-family: Georgia, serif;
        }

        .archive {
            width: 100%;
            max-width: 950px;
            margin: auto;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 25px;
            color: #d8b875;
            text-decoration: none;
        }

        .heading {
            text-align: center;
            margin-bottom: 35px;
        }

        .heading small {
            color: #c8a56d;
            letter-spacing: 4px;
        }

        .heading h1 {
            margin: 12px 0;
            color: #f1d49b;
            font-size: clamp(28px, 7vw, 44px);
        }

        .heading p {
            color: #b8abc9;
            line-height: 1.7;
        }

        .panel {
            padding: 22px;
            margin-bottom: 24px;
            border: 1px solid #76558d;
            border-radius: 18px;
            background: linear-gradient(145deg, #1c1229, #100b19);
            box-shadow: 0 0 25px #8b48a31a;
        }

        .panel h2 {
            margin-top: 0;
            color: #e8cb91;
        }

        .tarot-card {
            width: 100%;
            max-width: 290px;
            min-height: 390px;
            margin: 22px auto;
            padding: 15px;
            border: 2px solid var(--card-color, #c79aff);
            border-radius: 150px 150px 22px 22px;
            background:
                radial-gradient(ellipse at center, #39234f, #100b19 75%);
            box-shadow: 0 0 24px color-mix(in srgb, var(--card-color, #c79aff) 25%, transparent);
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .card-number {
            color: var(--card-color, #c79aff);
            font-size: 18px;
            letter-spacing: 4px;
        }

        .card-symbol {
            width: 145px;
            height: 145px;
            margin: 22px 0;
            border: 1px solid var(--card-color, #c79aff);
            border-radius: 50%;
            display: grid;
            place-items: center;
            color: var(--card-color, #c79aff);
            font-size: 66px;
            box-shadow: inset 0 0 25px #a66de322, 0 0 20px #a66de322;
        }

        .card-rarity {
            color: var(--card-color, #c79aff);
            font-size: 12px;
            letter-spacing: 3px;
        }

        .card-name {
            margin: 12px 0;
            font-size: 24px;
        }

        .card-effect {
            max-width: 230px;
            color: #c3b6d0;
            line-height: 1.6;
        }

        .draw-button {
            display: block;
            width: 100%;
            max-width: 290px;
            margin: 18px auto 0;
            padding: 15px;
            border: 1px solid #d9b879;
            border-radius: 10px;
            background: linear-gradient(135deg, #e0c28b, #98703e);
            color: #170f20;
            font-weight: bold;
            letter-spacing: 2px;
            cursor: pointer;
        }

        .draw-button:active {
            transform: scale(.98);
        }

        .collection {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(145px, 1fr));
            gap: 12px;
        }

        .collection-card {
            padding: 17px 12px;
            border: 1px solid var(--card-color, #76558d);
            border-radius: 12px;
            background: #171020;
            text-align: center;
            overflow-wrap: anywhere;
        }

        .collection-card .symbol {
            margin-bottom: 10px;
            color: var(--card-color, #c79aff);
            font-size: 34px;
        }

        .collection-card h3 {
            font-size: 15px;
        }

        .collection-card small {
            color: var(--card-color, #c79aff);
            letter-spacing: 1px;
        }

        .empty {
            color: #a99ab8;
            text-align: center;
            line-height: 1.7;
        }

        .total {
            color: #d8b875;
            font-size: 13px;
        }

        @media (max-width: 480px) {
            .panel {
                padding: 16px;
            }

            .tarot-card {
                min-height: 350px;
            }
        }
    </style>
</head>

<body>
<main class="archive">

    <a class="back-link" href="game.php">
        ← Kembali ke Dashboard
    </a>

    <header class="heading">
        <small>THE OCCULT ARCHIVE</small>
        <h1>TAROT ARCHIVE</h1>
        <p>
            Takdir tidak pernah memberikan jawaban yang sama
            kepada setiap jiwa.
        </p>
    </header>

    <section class="panel">
        <h2>✦ Arcana Summoning</h2>

        <p>
            Pemilik: <strong>
                <?= arcana_escape($_SESSION['player']['name']) ?>
            </strong>
        </p>

        <?php if ($lastCard): ?>
            <div class="tarot-card"
                 style="--card-color: <?= arcana_escape($lastCard['color'] ?? '#c79aff') ?>">

                <div class="card-number">
                    <?= arcana_escape($lastCard['number'] ?? '?') ?>
                </div>

                <div class="card-symbol">✧</div>

                <div class="card-rarity">
                    <?= arcana_escape($lastCard['rarity'] ?? 'ARCANA') ?>
                    ARCANA
                </div>

                <h2 class="card-name">
                    <?= arcana_escape($lastCard['name'] ?? 'Unknown Arcana') ?>
                </h2>

                <p class="card-effect">
                    <?= arcana_escape($lastCard['effect'] ?? 'A secret yet to be revealed.') ?>
                </p>
            </div>
        <?php else: ?>
            <p class="empty">
                Belum ada kartu yang terungkap.<br>
                Tekan tombol di bawah untuk memanggil Arcana pertamamu.
            </p>
        <?php endif; ?>

        <form method="post">
            <button class="draw-button" type="submit" name="draw" value="1">
                ✦ DRAW ARCANA ✦
            </button>
        </form>
    </section>

    <section class="panel">
        <h2>✧ Your Collection</h2>

        <p class="total">
            Total kartu terkumpul: <?= count($cards) ?>
        </p>

        <?php if (count($cards) > 0): ?>
            <div class="collection">
                <?php foreach (array_reverse($cards) as $card): ?>
                    <article class="collection-card"
                        style="--card-color: <?= arcana_escape($card['color'] ?? '#c79aff') ?>">

                        <div class="symbol">✧</div>

                        <small>
                            <?= arcana_escape($card['rarity'] ?? 'ARCANA') ?>
                        </small>

                        <h3>
                            <?= arcana_escape($card['name'] ?? 'Unknown Arcana') ?>
                        </h3>

                        <p>
                            Nomor:
                            <?= arcana_escape($card['number'] ?? '?') ?>
                        </p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="empty">
                Koleksimu masih kosong.<br>
                Kartu pertamamu menunggu untuk ditemukan.
            </p>
        <?php endif; ?>
    </section>

</main>
</body>
</html>
