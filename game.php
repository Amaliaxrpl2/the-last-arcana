<?php
require_once __DIR__ . '/config.php';

if (empty($_SESSION['player']['created'])) {
    arcana_redirect('character.php');
}
if (empty($_SESSION['story_completed'])) {
    arcana_redirect('story.php');
}

$player = &$_SESSION['player'];
$saveFile = __DIR__ . '/data/players.json';

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

$cards = [
    ['name'=>'The Fool','number'=>'0','rarity'=>'COMMON',
     'color'=>'#b6b6c9','description'=>'Awal perjalanan yang tidak diketahui.',
     'effect'=>'Menambah 2 Attack selama satu encounter.','power'=>2],
    ['name'=>'The Magician','number'=>'I','rarity'=>'UNCOMMON',
     'color'=>'#71c9b3','description'=>'Potensi kekuatan Arcana.',
     'effect'=>'Menambah 4 Attack.','power'=>4],
    ['name'=>'The High Priestess','number'=>'II','rarity'=>'EPIC',
     'color'=>'#d18aff','description'=>'Rahasia yang tersembunyi.',
     'effect'=>'Memulihkan 15 Mana.','power'=>15],
    ['name'=>'The Empress','number'=>'III','rarity'=>'RARE',
     'color'=>'#8b8aff','description'=>'Kekuatan kehidupan.',
     'effect'=>'Memulihkan 20 HP.','power'=>20],
    ['name'=>'The Hermit','number'=>'IX','rarity'=>'RARE',
     'color'=>'#8b8aff','description'=>'Kebijaksanaan dalam kegelapan.',
     'effect'=>'Menambah 5 Defense.','power'=>5],
    ['name'=>'Death','number'=>'XIII','rarity'=>'EPIC',
     'color'=>'#d18aff','description'=>'Akhir dan awal yang baru.',
     'effect'=>'Menambah 8 Attack.','power'=>8],
    ['name'=>'The Tower','number'=>'XVI','rarity'=>'MYTHIC',
     'color'=>'#ff737d','description'=>'Kehancuran yang mengubah takdir.',
     'effect'=>'Menambah 12 Attack.','power'=>12],
    ['name'=>'The Star','number'=>'XVII','rarity'=>'LEGENDARY',
     'color'=>'#ffd879','description'=>'Harapan di tengah kehancuran.',
     'effect'=>'Memulihkan 30 HP.','power'=>30],
    ['name'=>'The Moon','number'=>'XVIII','rarity'=>'RARE',
     'color'=>'#8b8aff','description'=>'Ilusi dan misteri.',
     'effect'=>'Menambah 10 Mana.','power'=>10]
];

$enemyNames = [
    'Veilborn Wraith',
    'Hollow Stalker',
    'Crimson Watcher',
    'Abyssal Hound',
    'Forgotten Knight',
    'The Nameless'
];

$dictionary = [
    'id' => [
        'eyebrow'=>'ARSIP CATATAN // 0001',
        'headline'=>'DUNIA TELAH MELUPAKAN NAMAMU.',
        'subhead'=>'Namun, Arcana belum.',
        'quote'=>'“Tidak semua stage diciptakan untuk ditaklukkan.”',
        'status'=>'STATUS PEMAIN',
        'stage'=>'Stage saat ini',
        'encounter'=>'PERTEMUAN',
        'actions'=>'PILIH AKSIMU',
        'attack'=>'SERANG',
        'draw'=>'DRAW ARCANA',
        'investigate'=>'INVESTIGASI',
        'enter'=>'MASUK STAGE',
        'meditate'=>'MEDITASI',
        'recover'=>'PULIHKAN',
        'archive'=>'ARSIP ARCANA',
        'inventory'=>'INVENTARIS',
        'quest'=>'QUEST',
        'investigation'=>'LOG INVESTIGASI',
        'system'=>'LOG SISTEM',
        'hp'=>'HP',
        'mana'=>'MANA',
        'exp'=>'EXP',
        'attackstat'=>'SERANGAN',
        'defense'=>'PERTAHANAN',
        'empty'=>'Belum ada data. Mulailah petualanganmu.',
        'back'=>'KEMBALI KE GAME',
        'language'=>'ENGLISH',
        'rest'=>'Pulihkan HP dan Mana. Recover tersedia sekali per stage.',
        'footer'=>'Setiap kartu menyimpan rahasia.',
    ],
    'en' => [
        'eyebrow'=>'ARCHIVE RECORD // 0001',
        'headline'=>'THE WORLD HAS FORGOTTEN YOUR NAME.',
        'subhead'=>'The Arcana has not.',
        'quote'=>'“Not every stage was created to be cleared.”',
        'status'=>'PLAYER STATUS',
        'stage'=>'Current stage',
        'encounter'=>'ENCOUNTER',
        'actions'=>'CHOOSE YOUR ACTION',
        'attack'=>'ATTACK',
        'draw'=>'DRAW ARCANA',
        'investigate'=>'INVESTIGATE',
        'enter'=>'ENTER STAGE',
        'meditate'=>'MEDITATE',
        'recover'=>'RECOVER',
        'archive'=>'ARCANA ARCHIVE',
        'inventory'=>'INVENTORY',
        'quest'=>'QUEST',
        'investigation'=>'INVESTIGATION LOG',
        'system'=>'SYSTEM LOG',
        'hp'=>'HP',
        'mana'=>'MANA',
        'exp'=>'EXP',
        'attackstat'=>'ATTACK',
        'defense'=>'DEFENSE',
        'empty'=>'No records yet. Begin your journey.',
        'back'=>'BACK TO GAME',
        'language'=>'INDONESIA',
        'rest'=>'Restore HP and Mana. Recover is available once per stage.',
        'footer'=>'Every card has a secret.',
    ]
];

if (!isset($_SESSION['tla_language'])) {
    $_SESSION['tla_language'] = 'en';
}

$lang = $_SESSION['tla_language'];
$t = $dictionary[$lang];

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function makeEnemy($stage, $names) {
    $boss = ($stage % 5 === 0);
    $index = min(count($names) - 1, intdiv($stage - 1, 2));
    $hp = (35 + $stage * 12) * ($boss ? 3 : 1);

    return [
        'name' => $boss ? 'THE ARCANA WARDEN' : $names[$index],
        'hp' => $hp,
        'max_hp' => $hp,
        'attack' => 7 + $stage * 2 + ($boss ? 5 : 0),
        'exp' => 35 + $stage * 15 + ($boss ? 50 : 0),
        'boss' => $boss
    ];
}

function saveGame($file, $player, $state) {
    $dir = dirname($file);

    if (!is_dir($dir) || !is_writable($dir)) {
        return false;
    }

    $all = [];
    if (is_file($file)) {
        $raw = file_get_contents($file);
        $decoded = json_decode($raw ?: '{}', true);
        if (is_array($decoded)) {
            $all = $decoded;
        }
    }

    $all[session_id()] = [
        'player' => $player,
        'state' => $state,
        'updated_at' => date(DATE_ATOM)
    ];

    $json = json_encode(
        $all,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );

    if ($json === false) {
        return false;
    }

    $fp = fopen($file, 'c+');
    if (!$fp) {
        return false;
    }

    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        return false;
    }

    rewind($fp);
    $ok = ftruncate($fp, 0)
        && fwrite($fp, $json . PHP_EOL) !== false
        && fflush($fp);

    flock($fp, LOCK_UN);
    fclose($fp);

    return $ok;
}

function addLog(&$state, $message, $type = 'system') {
    $state['logs'][] = [
        'message' => $message,
        'type' => $type,
        'time' => date('H:i')
    ];

    $state['logs'] = array_slice($state['logs'], -80);
}

function levelUp(&$player, &$state) {
    while ($player['exp'] >= $player['level'] * 100) {
        $player['exp'] -= $player['level'] * 100;
        $player['level']++;
        $player['max_hp'] += 15;
        $player['max_mana'] += 10;
        $player['attack'] += 3;
        $player['defense'] += 2;
        $player['hp'] = $player['max_hp'];
        $player['mana'] = $player['max_mana'];

        addLog($state, 'LEVEL UP! Level '.$player['level'].'!', 'reward');
    }
}

$key = session_id();
$allSaves = [];

if (is_file($saveFile)) {
    $decoded = json_decode(file_get_contents($saveFile) ?: '{}', true);
    if (is_array($decoded)) {
        $allSaves = $decoded;
    }
}

$saved = $allSaves[$key] ?? null;

if (is_array($saved)) {
    if (isset($saved['player']) && is_array($saved['player'])) {
        foreach ($saved['player'] as $k => $v) {
            $player[$k] = $v;
        }
    }
    $state = $saved['state'] ?? [];
} else {
    $state = [];
}

$state += [
    'enemy' => null,
    'entered' => false,
    'recovered_stage' => 0,
    'last_card' => null,
    'inventory' => [],
    'quests' => [
        ['title'=>'First Contact','done'=>false],
        ['title'=>'Arcana Collector','done'=>false],
        ['title'=>'Survive Stage 1','done'=>false]
    ],
    'clues' => [],
    'logs' => [],
    'notice' => ''
];

if (!is_array($state['inventory'])) $state['inventory'] = [];
if (!is_array($state['clues'])) $state['clues'] = [];
if (!is_array($state['logs'])) $state['logs'] = [];
if (!is_array($state['quests'])) $state['quests'] = [];

if (!isset($_SESSION['tla_csrf'])) {
    $_SESSION['tla_csrf'] = bin2hex(random_bytes(24));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? '';

    if (!is_string($token) ||
        !hash_equals($_SESSION['tla_csrf'], $token)) {
        http_response_code(403);
        exit('Invalid request token. Refresh the page and try again.');
    }

    if (isset($_POST['language'])) {
        $_SESSION['tla_language'] =
            ($_POST['language'] === 'id') ? 'id' : 'en';
    } else {
        $action = $_POST['action'] ?? '';
        $message = '';

        if ($action === 'attack') {
            if (!$state['entered'] || empty($state['enemy'])) {
                $message = 'Masuki stage terlebih dahulu.';
            } elseif ($player['hp'] <= 0) {
                $message = 'HP habis. Gunakan Recover.';
            } else {
                $bonus = (int)($state['attack_bonus'] ?? 0);
                $damage = max(1, $player['attack'] + $bonus - 2);
                $state['enemy']['hp'] = max(
                    0,
                    $state['enemy']['hp'] - $damage
                );

                addLog($state, 'Kamu memberikan '.$damage.' damage kepada '.$state['enemy']['name'].'.', 'combat');

                if ($state['enemy']['hp'] <= 0) {
                    $reward = $state['enemy']['exp'];
                    $player['exp'] += $reward;
                    $state['entered'] = false;
                    $state['attack_bonus'] = 0;
                    $state['defense_bonus'] = 0;

                    addLog($state, 'Musuh dikalahkan! +'.$reward.' EXP.', 'reward');

                    foreach ($state['quests'] as &$q) {
                        if ($q['title'] === 'First Contact') $q['done'] = true;
                        if ($q['title'] === 'Survive Stage 1' && $player['stage'] === 1) $q['done'] = true;
                    }
                    unset($q);

                    levelUp($player, $state);
                    $player['stage']++;
                    $state['enemy'] = null;
                } else {
                    $enemyDamage = max(
                        1,
                        $state['enemy']['attack']
                        - $player['defense']
                        - (int)($state['defense_bonus'] ?? 0)
                    );

                    $player['hp'] = max(0, $player['hp'] - $enemyDamage);
                    addLog($state, $state['enemy']['name'].' membalas: '.$enemyDamage.' damage.', 'combat');
                }
            }
        } elseif ($action === 'draw') {
            $card = $cards[array_rand($cards)];
            $card['id'] = bin2hex(random_bytes(5));
            $player['cards'][] = $card;
            $state['last_card'] = $card;

            foreach ($state['quests'] as &$q) {
                if ($q['title'] === 'Arcana Collector') $q['done'] = true;
            }
            unset($q);

            addLog($state, 'Arcana ditemukan: '.$card['name'].' ['.$card['rarity'].'].', 'arcana');
            $message = 'Kamu menemukan '.$card['name'].'!';
        } elseif ($action === 'investigate') {
            if (!$state['entered']) {
                $message = 'Masuki stage sebelum melakukan investigasi.';
            } else {
                $clueList = [
                    'A torn page bearing an unfamiliar sigil.',
                    'A cold whisper behind the sealed door.',
                    'A trace of crimson dust near the altar.',
                    'A forgotten name etched into the wall.',
                    'A broken seal humming with Arcana.'
                ];

                $clue = $clueList[array_rand($clueList)];
                $state['clues'][] = [
                    'clue' => $clue,
                    'stage' => $player['stage'],
                    'time' => date('H:i')
                ];

                if (random_int(1, 100) <= 35) {
                    $state['inventory'][] = [
                        'name' => 'Minor Potion',
                        'type' => 'consumable',
                        'description' => 'Memulihkan 25 HP.',
                        'count' => 1
                    ];
                    $message = 'Petunjuk ditemukan. Kamu juga menemukan Minor Potion!';
                } else {
                    $message = 'Petunjuk baru ditemukan.';
                }

                addLog($state, $clue, 'clue');
            }
        } elseif ($action === 'enter') {
            if ($player['hp'] <= 0) {
                $message = 'Pulihkan HP sebelum memasuki stage.';
            } elseif ($state['entered']) {
                $message = 'Kamu masih berada di stage ini. Kalahkan musuh terlebih dahulu.';
            } else {
                $state['enemy'] = makeEnemy($player['stage'], $enemyNames);
                $state['entered'] = true;
                $state['attack_bonus'] = 0;
                $state['defense_bonus'] = 0;
                addLog($state, 'Stage '.$player['stage'].' dimulai. Musuh: '.$state['enemy']['name'].'.', 'system');
                $message = 'Kamu memasuki Stage '.$player['stage'].'.';
            }
        } elseif ($action === 'meditate') {
            if ($player['hp'] <= 0) {
                $message = 'HP habis. Gunakan Recover.';
            } elseif ($player['mana'] < 10) {
                $message = 'Mana tidak cukup.';
            } else {
                $player['mana'] -= 10;
                $before = $player['hp'];
                $player['hp'] = min($player['max_hp'], $player['hp'] + 25);
                $message = 'Meditasi memulihkan '.($player['hp'] - $before).' HP.';
                addLog($state, $message, 'reward');
            }
        } elseif ($action === 'recover') {
            if ((int)$state['recovered_stage'] === (int)$player['stage']) {
                $message = 'Recover sudah digunakan pada stage ini.';
            } else {
                $player['hp'] = $player['max_hp'];
                $player['mana'] = $player['max_mana'];
                $state['recovered_stage'] = $player['stage'];
                $message = 'HP dan Mana pulih sepenuhnya.';
                addLog($state, $message, 'reward');
            }
        } elseif ($action === 'use_potion') {
            $found = false;
            foreach ($state['inventory'] as $i => &$item) {
                if ($item['name'] === 'Minor Potion' && $item['count'] > 0) {
                    $before = $player['hp'];
                    $player['hp'] = min($player['max_hp'], $player['hp'] + 25);
                    $item['count']--;
                    $found = true;
                    $message = 'Potion memulihkan '.($player['hp'] - $before).' HP.';
                    break;
                }
            }
            unset($item);
            if (!$found) $message = 'Kamu tidak memiliki Minor Potion.';
            addLog($state, $message, 'item');
        } elseif ($action === 'apply_card') {
            $id = $_POST['card_id'] ?? '';
            $found = false;

            foreach ($player['cards'] as &$card) {
                if (($card['id'] ?? '') === $id && !$found) {
                    $found = true;
                    $name = $card['name'];

                    if ($name === 'The High Priestess') {
                        $player['mana'] = min($player['max_mana'], $player['mana'] + 15);
                    } elseif ($name === 'The Empress' || $name === 'The Star') {
                        $amount = $name === 'The Star' ? 30 : 20;
                        $player['hp'] = min($player['max_hp'], $player['hp'] + $amount);
                    } elseif ($name === 'The Hermit') {
                        $state['defense_bonus'] = 5;
                    } else {
                        $state['attack_bonus'] = (int)($card['power'] ?? 2);
                    }

                    $state['last_card'] = $card;
                    $message = $name.' diaktifkan.';
                    addLog($state, $message, 'arcana');
                    break;
                }
            }
            unset($card);

            if (!$found) $message = 'Kartu tidak ditemukan.';
        } else {
            $message = 'Aksi tidak dikenal.';
        }

        $state['notice'] = $message;
    }

    $player['cards'] = array_values($player['cards']);
    saveGame($saveFile, $player, $state);
    arcana_redirect('game.php');
}

$notice = $state['notice'] ?? '';
$state['notice'] = '';

$enemy = $state['enemy'];
$lastCard = $state['last_card'];
$expNeeded = max(1, $player['level'] * 100);

$hpPct = min(100, max(0, $player['hp'] / max(1, $player['max_hp']) * 100));
$manaPct = min(100, max(0, $player['mana'] / max(1, $player['max_mana']) * 100));
$expPct = min(100, max(0, $player['exp'] / $expNeeded * 100));
$enemyPct = $enemy ? min(100, max(0, $enemy['hp'] / max(1, $enemy['max_hp']) * 100)) : 0;

$inventoryCount = 0;
foreach ($state['inventory'] as $item) {
    $inventoryCount += (int)($item['count'] ?? 0);
}
$cardCount = count($player['cards']);

saveGame($saveFile, $player, $state);
?>
<!DOCTYPE html>
<html lang="<?= e($lang) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>THE LAST ARCANA | Gameplay</title>
<link rel="stylesheet" href="style.css">
<style>
.game-page{position:relative;z-index:1;width:min(900px,calc(100% - 24px));margin:20px auto;color:var(--text)}
.arc-panel{padding:20px;margin:0 0 16px;background:linear-gradient(145deg,#1b1126f5,#0c0812f7);border:1px solid #65437d;border-radius:10px;box-shadow:0 12px 35px #0006}
.arc-eyebrow{color:var(--gold);font:10px 'Cinzel',serif;letter-spacing:2px}
.arc-title{font:600 clamp(24px,6vw,36px) 'Cinzel',serif;line-height:1.35;margin:12px 0;color:#f5efff}
.arc-muted{color:var(--muted)}
.arc-row{display:flex;justify-content:space-between;align-items:center;gap:12px}
.arc-bar{height:8px;background:#30223d;border-radius:12px;overflow:hidden;margin:8px 0 15px}
.arc-fill{height:100%;border-radius:12px}
.arc-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.arc-stat,.arc-link,.arc-action{min-width:0;padding:14px;background:#100b19;border:1px solid #50365f;border-radius:7px}
.arc-stat small{display:block;color:#b9a6d0;font:10px 'Cinzel',serif;letter-spacing:1px}
.arc-stat strong{display:block;margin:5px 0;font-size:21px}
.arc-actions{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}
.arc-actions form{min-width:0}
.arc-button{display:flex;align-items:center;justify-content:center;width:100%;min-height:50px;padding:11px 6px;background:linear-gradient(145deg,#332044,#1a1027);border:1px solid #8155a3;border-radius:6px;color:#f7edff;text-align:center;text-decoration:none;font:700 10px 'Cinzel',serif;cursor:pointer}
.arc-button.gold{color:#f3d994;border-color:#b79655;background:linear-gradient(145deg,#34251c,#1a1017)}
.arc-button:disabled{opacity:.45;cursor:not-allowed}
.arc-enemy{color:#ff829d;font:600 23px 'Cinzel',serif}
.arc-notice{border-color:#a16bcb;color:#ead9ff}
.arc-card{max-width:330px;margin:16px auto;padding:22px;text-align:center;background:radial-gradient(ellipse at top,#63358a88,transparent 65%),#100b19;border:2px solid var(--cardcolor,#a986e8);border-radius:9px}
.arc-card .symbol{font-size:50px;color:var(--cardcolor,#c6a0ff)}
.arc-card h3{color:var(--cardcolor,#d6bdff);font:600 22px 'Cinzel',serif}
.arc-rarity{display:inline-block;padding:5px 10px;border:1px solid var(--cardcolor);color:var(--cardcolor);font:10px 'Cinzel',serif;letter-spacing:2px}
.arc-list{list-style:none;padding:0;margin:0}
.arc-list li{padding:12px 0;border-bottom:1px solid #50365f}
.arc-list li:last-child{border-bottom:0}
.arc-time{color:#9e8eaf;font-size:12px}
.arc-nav{display:flex;flex-wrap:wrap;gap:8px}
.arc-nav a{flex:1 1 120px;padding:10px;border:1px solid #50365f;border-radius:5px;text-align:center;text-decoration:none;
color:#d6bdff;background:#100b19;font:10px 'Cinzel',serif}
.arc-nav a:hover{border-color:var(--gold);background:#24152f}
.arc-section{scroll-margin-top:20px}
.arc-empty{padding:18px;text-align:center;color:var(--muted);border:1px dashed #50365f;border-radius:7px}
.arc-panel h2{font:600 17px 'Cinzel',serif;color:#e9d9ff}
.arc-label{font:10px 'Cinzel',serif;letter-spacing:1px;color:var(--gold)}
.arc-log-combat{color:#ff91a7}
.arc-log-reward{color:#f3d994}
.arc-log-clue{color:#c6a0ff}
.arc-log-arcana{color:#d6a5ff}
.arc-card-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.arc-mini-card{padding:13px;background:#100b19;border:1px solid #65437d;border-radius:7px}
.arc-mini-card h3{font:600 14px 'Cinzel',serif;color:var(--gold)}
.arc-mini-card p{font-size:14px;color:var(--muted)}
.arc-boss{color:#ff647e;font:600 11px 'Cinzel',serif;letter-spacing:2px}
@media(max-width:420px){
.arc-panel{padding:15px}
.arc-grid,.arc-actions,.arc-card-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
.arc-stat{padding:10px}
.arc-stat strong{font-size:18px}
.arc-button{font-size:9px}
}
</style>
</head>
<body>
<div class="game-page">

<section class="arc-panel">
<p class="arc-eyebrow"><?= e($t['eyebrow']) ?></p>
<h1 class="arc-title"><?= e($t['headline']) ?></h1>
<p class="arc-muted"><?= e($t['subhead']) ?></p>
<blockquote><?= e($t['quote']) ?></blockquote>
<form method="post">
<input type="hidden" name="csrf" value="<?= e($_SESSION['tla_csrf']) ?>">
<button class="arc-button gold" type="submit" name="language" value="<?= $lang === 'id' ? 'en' : 'id' ?>">
<?= e($t['language']) ?>
</button>
</form>
</section>

<?php if ($notice !== ''): ?>
<section class="arc-panel arc-notice">
<?= e($notice) ?>
</section>
<?php endif; ?>

<section class="arc-panel">
<p class="arc-eyebrow"><?= e($t['status']) ?></p>
<div class="arc-row">
<h2><?= e($player['name']) ?></h2>
<span class="arc-label">LV. <?= (int)$player['level'] ?></span>
</div>
<p class="arc-muted"><?= e($player['class']) ?> · Stage <?= (int)$player['stage'] ?></p>

<div class="arc-row"><span><?= e($t['hp']) ?></span><strong><?= (int)$player['hp'] ?>/<?= (int)$player['max_hp'] ?></strong></div>
<div class="arc-bar"><div class="arc-fill" style="width:<?= $hpPct ?>%;background:#e94d70"></div></div>

<div class="arc-row"><span><?= e($t['mana']) ?></span><strong><?= (int)$player['mana'] ?>/<?= (int)$player['max_mana'] ?></strong></div>
<div class="arc-bar"><div class="arc-fill" style="width:<?= $manaPct ?>%;background:#8b8aff"></div></div>

<div class="arc-row"><span><?= e($t['exp']) ?></span><strong><?= (int)$player['exp'] ?>/<?= $expNeeded ?></strong></div>
<div class="arc-bar"><div class="arc-fill" style="width:<?= $expPct ?>%;background:#e4c078"></div></div>

<div class="arc-grid">
<div class="arc-stat"><small><?= e($t['attackstat']) ?></small><strong><?= (int)$player['attack'] ?></strong></div>
<div class="arc-stat"><small><?= e($t['defense']) ?></small><strong><?= (int)$player['defense'] ?></strong></div>
<div class="arc-stat"><small>ARCANA</small><strong><?= $cardCount ?></strong></div>
<div class="arc-stat"><small>ITEMS</small><strong><?= $inventoryCount ?></strong></div>
</div>
</section>

<section class="arc-panel">
<p class="arc-eyebrow"><?= e($t['encounter']) ?></p>

<?php if ($enemy && !empty($state['entered'])): ?>
<?php if (!empty($enemy['boss'])): ?><p class="arc-boss">BOSS ENCOUNTER</p><?php endif; ?>
<h2 class="arc-enemy"><?= e($enemy['name']) ?></h2>
<div class="arc-row"><span>ENEMY HP</span><strong><?= (int)$enemy['hp'] ?>/<?= (int)$enemy['max_hp'] ?></strong></div>
<div class="arc-bar"><div class="arc-fill" style="width:<?= $enemyPct ?>%;background:#e94d70"></div></div>
<p class="arc-muted">ATK <?= (int)$enemy['attack'] ?> · <?= (int)$enemy['exp'] ?> EXP</p>

<div class="arc-actions">
<form method="post"><input type="hidden" name="csrf" value="<?= e($_SESSION['tla_csrf']) ?>"><button class="arc-button" name="action" value="attack">⚔ <?= e($t['attack']) ?></button></form>
<form method="post"><input type="hidden" name="csrf" value="<?= e($_SESSION['tla_csrf']) ?>"><button class="arc-button gold" name="action" value="draw">✧ <?= e($t['draw']) ?></button></form>
<form method="post"><input type="hidden" name="csrf" value="<?= e($_SESSION['tla_csrf']) ?>"><button class="arc-button" name="action" value="investigate">⌕ <?= e($t['investigate']) ?></button></form>
<form method="post"><input type="hidden" name="csrf" value="<?= e($_SESSION['tla_csrf']) ?>"><button class="arc-button" name="action" value="meditate">☾ <?= e($t['meditate']) ?></button></form>
</div>
<?php else: ?>
<p class="arc-empty">Belum ada encounter aktif. Masuki stage untuk memulai pertarungan.</p>
<form method="post"><input type="hidden" name="csrf" value="<?= e($_SESSION['tla_csrf']) ?>"><button class="arc-button gold" name="action" value="enter">⚔ <?= e($t['enter']) ?></button></form>
<?php endif; ?>

<form method="post" style="margin-top:10px">
<input type="hidden" name="csrf" value="<?= e($_SESSION['tla_csrf']) ?>">
<button class="arc-button gold" name="action" value="recover"><?= e($t['recover']) ?></button>
</form>
<p class="arc-muted"><?= e($t['rest']) ?></p>
</section>

<section class="arc-panel" id="archive">
<p class="arc-eyebrow"><?= e($t['archive']) ?></p>
<?php if ($lastCard): ?>
<div class="arc-card" style="--cardcolor:<?= e($lastCard['color'] ?? '#c6a0ff') ?>">
<div class="symbol">✧</div>
<p class="arc-label">ARCANA <?= e($lastCard['number'] ?? '?') ?></p>
<h3><?= e($lastCard['name'] ?? 'Unknown') ?></h3>
<span class="arc-rarity"><?= e($lastCard['rarity'] ?? 'COMMON') ?></span>
<p><?= e($lastCard['description'] ?? '') ?></p>
<p><?= e($lastCard['effect'] ?? '') ?></p>
</div>
<?php else: ?><p class="arc-empty"><?= e($t['empty']) ?></p><?php endif; ?>

<form method="post"><input type="hidden" name="csrf" value="<?= e($_SESSION['tla_csrf']) ?>"><button class="arc-button gold" name="action" value="draw"><?= e($t['draw']) ?></button></form>

<h2>COLLECTED ARCANA</h2>
<div class="arc-card-grid">
<?php foreach ($player['cards'] as $card): ?>
<div class="arc-mini-card">
<p class="arc-label"><?= e($card['rarity'] ?? 'COMMON') ?></p>
<h3><?= e($card['name'] ?? 'Unknown') ?></h3>
<p><?= e($card['description'] ?? '') ?></p>
<form method="post">
<input type="hidden" name="csrf" value="<?= e($_SESSION['tla_csrf']) ?>">
<input type="hidden" name="card_id" value="<?= e($card['id'] ?? '') ?>">
<button class="arc-button" name="action" value="apply_card">USE CARD</button>
</form>
</div>
<?php endforeach; ?>
</div>
</section>

<section class="arc-panel" id="inventory">
<p class="arc-eyebrow"><?= e($t['inventory']) ?></p>
<?php if (!$state['inventory']): ?>
<p class="arc-empty"><?= e($t['empty']) ?></p>
<?php else: ?>
<?php foreach ($state['inventory'] as $item): ?>
<div class="arc-mini-card">
<h3><?= e($item['name']) ?> ×<?= (int)$item['count'] ?></h3>
<p><?= e($item['description'] ?? '') ?></p>
<?php if (($item['name'] ?? '') === 'Minor Potion' && ($item['count'] ?? 0) > 0): ?>
<form method="post">
<input type="hidden" name="csrf" value="<?= e($_SESSION['tla_csrf']) ?>">
<button class="arc-button" name="action" value="use_potion">USE ITEM</button>
</form>
<?php endif; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>
</section>

<section class="arc-panel" id="quests">
<p class="arc-eyebrow"><?= e($t['quest']) ?></p>
<?php foreach ($state['quests'] as $quest): ?>
<div class="arc-row arc-list">
<span><?= e($quest['title']) ?></span>
<strong><?= !empty($quest['done']) ? '✓ COMPLETE' : '◇ IN PROGRESS' ?></strong>
</div>
<?php endforeach; ?>
</section>

<section class="arc-panel" id="investigation">
<p class="arc-eyebrow"><?= e($t['investigation']) ?></p>
<?php if (!$state['clues']): ?><p class="arc-empty"><?= e($t['empty']) ?></p>
<?php else: ?>
<ul class="arc-list">
<?php foreach (array_reverse($state['clues']) as $clue): ?>
<li><?= e($clue['clue']) ?><div class="arc-time">Stage <?= (int)$clue['stage'] ?> · <?= e($clue['time']) ?></div></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
</section>

<section class="arc-panel" id="systemlog">
<p class="arc-eyebrow"><?= e($t['system']) ?></p>
<?php if (!$state['logs']): ?><p class="arc-empty"><?= e($t['empty']) ?></p>
<?php else: ?>
<ul class="arc-list">
<?php foreach (array_reverse($state['logs']) as $log): ?>
<li class="arc-log-<?= e($log['type']) ?>"><?= e($log['message']) ?><div class="arc-time"><?= e($log['time']) ?></div></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
</section>

<section class="arc-panel">
<p class="arc-eyebrow">QUICK MENU</p>
<nav class="arc-nav">
<a href="#archive">🃏 <?= e($t['archive']) ?></a>
<a href="#inventory">🎒 <?= e($t['inventory']) ?></a>
<a href="#quests">✦ <?= e($t['quest']) ?></a>
<a href="#investigation">⌕ <?= e($t['investigation']) ?></a>
<a href="#systemlog">◇ <?= e($t['system']) ?></a>
<a href="character.php">CHARACTER</a>
<a href="index.php">HOME</a>
</nav>
</section>

<footer class="game-footer">
THE LAST ARCANA · <?= e($t['footer']) ?>
</footer>
</div>
</body>
</html>
