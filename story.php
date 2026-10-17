<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['player'])) {
    header('Location: index.php');
    exit;
}

$pages = [
    [
        'label' => 'THE UNWRITTEN BEGINNING',
        'title' => 'A World Without Answers',
        'text' => [
            'Somewhere beyond the reach of memory, a forgotten world continues to exist.',
            'Its cities have fallen silent. Its history has faded. And somewhere at the edge of everything lies a place known only as The End.',
            'Many have searched for it. Few have ever returned.'
        ]
    ],
    [
        'label' => 'THE AWAKENING',
        'title' => 'The Stranger',
        'text' => [
            'You awaken among the ruins of an unfamiliar place.',
            'You cannot remember your past. You do not know how you arrived here, or whether you have ever walked these streets before.',
            'There is only one thing you understand: you must reach The End.',
            'You do not know why.'
        ]
    ],
    [
        'label' => 'A MESSAGE LEFT BEHIND',
        'title' => 'The First Clue',
        'text' => [
            'A strange inscription covers the wall beside you.',
            '"Those who seek the end shall lose what brought them there."',
            'The words mean nothing to you. Still, something about them feels strangely familiar.',
            'You leave the ruins behind.'
        ]
    ],
    [
        'label' => 'THE FIRST ENCOUNTER',
        'title' => 'You Came Back',
        'text' => [
            'A creature emerges from the mist, its shape barely distinguishable from the darkness around it.',
            'It stares at you without moving.',
            '"...You came back."',
            'Before you can ask what it means, the creature raises its weapon.',
            'There is no time for answers.'
        ]
    ],
    [
        'label' => 'THE JOURNEY BEGINS',
        'title' => 'Beyond the Ruins',
        'text' => [
            'The creature disappears, leaving behind nothing that explains its words.',
            'Far beyond the ruined city, the road continues into a world you do not understand.',
            'You are not the first to walk this path. Somewhere ahead, others have left traces of their own journeys.',
            'You tighten your grip on your weapon and take the first step.',
            'The road to The End awaits.'
        ]
    ]
];

if (!isset($_SESSION['story_page'])) {
    $_SESSION['story_page'] = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'next') {
        $_SESSION['story_page'] = min(
            count($pages) - 1,
            (int) $_SESSION['story_page'] + 1
        );
    } elseif ($action === 'previous') {
        $_SESSION['story_page'] = max(
            0,
            (int) $_SESSION['story_page'] - 1
        );
    } elseif ($action === 'begin') {
        $_SESSION['story_completed'] = true;
        header('Location: game.php');
        exit;
    }
}

$pageIndex = max(0, min(
    count($pages) - 1,
    (int) $_SESSION['story_page']
));

$page = $pages[$pageIndex];

function storyEscape($value) {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title><?= storyEscape($page['title']) ?> | THE LAST ARCANA</title>

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="story.css">
</head>
<body class="story-body">

<main class="story-shell">
    <header class="story-header">
        <a href="index.php" class="story-logo">
            ✦ THE LAST ARCANA
        </a>

        <span>PROLOGUE</span>
    </header>

    <section class="story-book">
        <div class="story-art">
            <div class="story-art-symbol">✧</div>
            <p>EVERY BEGINNING HIDES A SECRET</p>
        </div>

        <div class="story-content">
            <p class="story-label">
                <?= storyEscape($page['label']) ?>
            </p>

            <h1><?= storyEscape($page['title']) ?></h1>

            <div class="story-divider"></div>

            <div class="story-narrative">
                <?php foreach ($page['text'] as $paragraph): ?>
                    <p><?= storyEscape($paragraph) ?></p>
                <?php endforeach; ?>
            </div>

            <div class="story-progress">
                <span>
                    PAGE <?= str_pad($pageIndex + 1, 2, '0', STR_PAD_LEFT) ?>
                    / <?= str_pad(count($pages), 2, '0', STR_PAD_LEFT) ?>
                </span>

                <div class="story-progress-track">
                    <div
                        class="story-progress-fill"
                        style="width: <?= (($pageIndex + 1) / count($pages)) * 100 ?>%">
                    </div>
                </div>
            </div>

            <form method="POST" class="story-controls">
                <?php if ($pageIndex > 0): ?>
                    <button
                        type="submit"
                        name="action"
                        value="previous"
                        class="story-button story-secondary">
                        ← Previous
                    </button>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>

                <?php if ($pageIndex < count($pages) - 1): ?>
                    <button
                        type="submit"
                        name="action"
                        value="next"
                        class="story-button story-primary">
                        Continue →
                    </button>
                <?php else: ?>
                    <button
                        type="submit"
                        name="action"
                        value="begin"
                        class="story-button story-primary">
                        Begin Your Journey ✦
                    </button>
                <?php endif; ?>
            </form>
        </div>
    </section>

    <footer class="story-footer">
        SOME MEMORIES ARE BETTER LEFT UNFOUND
    </footer>
</main>

</body>
</html>
