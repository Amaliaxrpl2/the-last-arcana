<?php
require_once __DIR__ . '/config.php';

$classes = [
    'Arcanist' => [
        'hp' => 80,
        'mana' => 140,
        'attack' => 15,
        'defense' => 8,
        'description' => 'Penguasa energi Arcana. Ahli sihir dan manipulasi mana.'
    ],
    'Shadow Hunter' => [
        'hp' => 100,
        'mana' => 80,
        'attack' => 22,
        'defense' => 12,
        'description' => 'Pemburu dari balik bayangan dengan serangan cepat.'
    ],
    'Knight of Ruin' => [
        'hp' => 130,
        'mana' => 50,
        'attack' => 16,
        'defense' => 20,
        'description' => 'Kesatria tangguh yang bertahan menghadapi kehancuran.'
    ]
];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['character_name'] ?? '');
    $class = $_POST['character_class'] ?? '';

    if ($name === '' || mb_strlen($name) > 20) {
        $error = 'Nama harus diisi dan maksimal 20 karakter.';
    } elseif (!isset($classes[$class])) {
        $error = 'Pilih kelas karakter terlebih dahulu.';
    } else {
        $stats = $classes[$class];

        $_SESSION['player'] = [
            'created' => true,
            'name' => $name,
            'class' => $class,
            'level' => 1,
            'exp' => 0,
            'hp' => $stats['hp'],
            'max_hp' => $stats['hp'],
            'mana' => $stats['mana'],
            'max_mana' => $stats['mana'],
            'attack' => $stats['attack'],
            'defense' => $stats['defense'],
            'stage' => 1,
            'cards' => []
        ];

        // Karakter selesai dibuat, lanjut ke prolog cerita.
        $_SESSION['story_page'] = 1;
        $_SESSION['story_completed'] = false;

        header('Location: story.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Character | THE LAST ARCANA</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .creation {
            width: min(850px, 92%);
            margin: 45px auto;
            position: relative;
            z-index: 1;
        }

        .creation-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .creation-header .eyebrow {
            margin-bottom: 12px;
        }

        .creation-header h1 {
            font-size: clamp(30px, 7vw, 48px);
        }

        .creation-header p {
            color: #bcaecc;
            font-size: 17px;
            margin-top: 15px;
        }

        .creation-panel {
            padding: 25px;
            background: linear-gradient(145deg, #171020ee, #0d0914f5);
            border: 1px solid #b08bdf55;
            box-shadow: 0 0 35px #0008;
        }

        .field-label {
            display: block;
            color: #d8b46a;
            font-family: 'Cinzel', serif;
            font-size: 12px;
            letter-spacing: 2px;
            margin-bottom: 12px;
        }

        .character-input {
            width: 100%;
            box-sizing: border-box;
            padding: 15px;
            background: #090710;
            border: 1px solid #72548e;
            color: #fff;
            outline: none;
            font: inherit;
            font-size: 17px;
            margin-bottom: 28px;
        }

        .character-input:focus {
            border-color: #d8b46a;
        }

        .class-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .class-option {
            display: block;
            cursor: pointer;
        }

        .class-option input {
            position: absolute;
            opacity: 0;
        }

        .class-card {
            height: 100%;
            min-height: 225px;
            padding: 18px 12px;
            box-sizing: border-box;
            background: #100b19;
            border: 1px solid #554064;
            text-align: center;
            transition: 0.25s;
        }

        .class-option input:checked + .class-card {
            border-color: #d8b46a;
            background: linear-gradient(160deg, #352044, #120c1b);
            box-shadow: 0 0 20px #a77be033;
        }

        .class-option input:focus-visible + .class-card {
            outline: 2px solid #fff;
            outline-offset: 3px;
        }

        .class-icon {
            font-size: 36px;
            margin-bottom: 13px;
            color: #d8b46a;
        }

        .class-card h2 {
            color: #e6ceff;
            font-family: 'Cinzel', serif;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .class-card p {
            color: #b7a7c5;
            font-size: 15px;
            line-height: 1.45;
            margin-bottom: 15px;
        }

        .class-stats {
            color: #d8b46a;
            font-size: 12px;
            line-height: 1.8;
        }

        .error-message {
            padding: 12px;
            margin-bottom: 20px;
            color: #ffb4c0;
            background: #42172666;
            border: 1px solid #a94861;
        }

        .create-submit {
            width: 100%;
            margin-top: 28px;
            border: 0;
            cursor: pointer;
            justify-content: center;
        }

        .back-link {
            display: block;
            width: fit-content;
            margin: 22px auto 0;
            color: #bcaecc;
            text-decoration: none;
            font-size: 16px;
        }

        .back-link:hover {
            color: #d8b46a;
        }

        @media (max-width: 620px) {
            .creation {
                margin: 30px auto;
            }

            .creation-panel {
                padding: 18px 13px;
            }

            .class-grid {
                grid-template-columns: 1fr;
            }

            .class-card {
                min-height: 0;
                padding: 20px 14px;
            }

            .class-icon {
                font-size: 30px;
            }
        }
    </style>
</head>

<body>
<div class="stars"></div>

<header class="topbar">
    <a href="index.php" class="logo">
        <span class="logo-symbol">✦</span>
        THE LAST ARCANA
    </a>

    <span class="eyebrow">CHARACTER REGISTRATION</span>
</header>

<main class="creation">
    <div class="creation-header">
        <p class="eyebrow">THE FIRST STEP OF YOUR FATE</p>

        <h1>
            CREATE YOUR<br>
            <span>CHARACTER</span>
        </h1>

        <p>
            Tentukan identitasmu sebelum memasuki dunia Arcana.
        </p>
    </div>

    <form class="creation-panel" method="POST">
        <?php if ($error !== ''): ?>
            <div class="error-message">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <label class="field-label" for="character_name">
            CHARACTER NAME
        </label>

        <input
            class="character-input"
            type="text"
            id="character_name"
            name="character_name"
            maxlength="20"
            placeholder="Masukkan nama karaktermu..."
            value="<?= htmlspecialchars($_POST['character_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            required
        >

        <p class="field-label">CHOOSE YOUR CLASS</p>

        <div class="class-grid">
            <?php foreach ($classes as $className => $stats): ?>
                <label class="class-option">
                    <input
                        type="radio"
                        name="character_class"
                        value="<?= htmlspecialchars($className, ENT_QUOTES, 'UTF-8') ?>"
                        <?= (($_POST['character_class'] ?? '') === $className) ? 'checked' : '' ?>
                        required
                    >

                    <div class="class-card">
                        <div class="class-icon">
                            <?php
                            if ($className === 'Arcanist') {
                                echo '✧';
                            } elseif ($className === 'Shadow Hunter') {
                                echo '☾';
                            } else {
                                echo '♜';
                            }
                            ?>
                        </div>

                        <h2>
                            <?= htmlspecialchars($className, ENT_QUOTES, 'UTF-8') ?>
                        </h2>

                        <p>
                            <?= htmlspecialchars($stats['description'], ENT_QUOTES, 'UTF-8') ?>
                        </p>

                        <div class="class-stats">
                            HP <?= $stats['hp'] ?> ·
                            MANA <?= $stats['mana'] ?><br>

                            ATK <?= $stats['attack'] ?> ·
                            DEF <?= $stats['defense'] ?>
                        </div>
                    </div>
                </label>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="start-button create-submit">
            ✦ AWAKEN YOUR DESTINY ✦
        </button>

        <a href="index.php" class="back-link">
            ← Kembali ke halaman awal
        </a>
    </form>
</main>

<footer class="footer">
    ✦ THE LAST ARCANA
    <p>EVERY BEGINNING HAS A PRICE</p>
</footer>

</body>
</html>
