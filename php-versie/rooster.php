<?php
declare(strict_types=1);

require __DIR__ . '/db.php';
$user = requireLogin();

$days = [
    'maandag' => 'Maandag', 'dinsdag' => 'Dinsdag', 'woensdag' => 'Woensdag',
    'donderdag' => 'Donderdag', 'vrijdag' => 'Vrijdag',
];
$times = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'];
$week = max(1, (int) ($_GET['week'] ?? $_POST['week'] ?? 1));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $statement = $database->prepare('DELETE FROM schedule WHERE id = :id AND week = :week AND user_id = :user_id');
    $statement->execute([':id' => (int) ($_POST['id'] ?? 0), ':week' => $week, ':user_id' => $user['id']]);
    header('Location: rooster.php?week=' . $week);
    exit;
}

$statement = $database->prepare('SELECT schedule.*, users.email AS owner_email FROM schedule LEFT JOIN users ON users.id = schedule.user_id WHERE schedule.week = :week AND schedule.user_id = :user_id ORDER BY start_time, id');
$statement->execute([':week' => $week, ':user_id' => $user['id']]);
$appointments = [];
foreach ($statement as $appointment) {
    $appointments[$appointment['day']][$appointment['start_time']][] = $appointment;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Football - Rooster</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="schedule-page">
    <header class="header">
        <a href="index.php" class="logo"><img src="Logo.png" alt="Logo"></a>
        <nav>
            <div class="center-nav"><a href="home.php">Home</a><a href="rooster.php" class="active">Teams</a></div>
            <a href="logout.php" class="login-link">Uitloggen</a>
        </nav>
    </header>

    <main class="main schedule-main">
        <section class="schedule-shell">
            <div class="schedule-toolbar">
                <div class="week-switcher">
                    <a href="rooster.php?week=<?= $week - 1 ?>" aria-label="Vorige week">&#8592;</a>
                    <strong>Week <?= $week ?></strong>
                    <a href="rooster.php?week=<?= $week + 1 ?>" aria-label="Volgende week">&#8594;</a>
                </div>
                <a class="plan-button" href="home.php">Inplannen</a>
            </div>
            <div class="schedule-scroll">
                <table class="schedule-table">
                    <thead><tr><th class="time-column">Tijd</th>
                        <?php foreach ($days as $day): ?><th><?= htmlspecialchars($day, ENT_QUOTES, 'UTF-8') ?></th><?php endforeach; ?>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($times as $time): ?><tr>
                        <th class="time-column"><?= $time ?></th>
                        <?php foreach ($days as $dayKey => $dayName): ?><td>
                            <?php foreach ($appointments[$dayKey][$time] ?? [] as $appointment): ?><article class="appointment">
                                <strong><?= htmlspecialchars($appointment['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <?php if ($appointment['location'] !== ''): ?><span><?= htmlspecialchars($appointment['location'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
                                <small class="appointment-owner"><?= htmlspecialchars($appointment['owner_email'] ?? 'Onbekende gebruiker', ENT_QUOTES, 'UTF-8') ?></small>
                                <form method="post" class="delete-form">
                                    <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $appointment['id'] ?>"><input type="hidden" name="week" value="<?= $week ?>">
                                    <button type="submit" class="delete-button" aria-label="Verwijder afspraak">&times;</button>
                                </form>
                            </article><?php endforeach; ?>
                        </td><?php endforeach; ?>
                    </tr><?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>