<?php
declare(strict_types=1);
require __DIR__ . '/db.php';

$error = '';
$mode = $_GET['mode'] ?? 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? 'login';
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Vul een geldig emailadres in.';
    } elseif (strlen($password) < 6) {
        $error = 'Je wachtwoord moet minimaal 6 tekens bevatten.';
    } elseif ($mode === 'register') {
        try {
            $statement = $database->prepare('INSERT INTO users (email, password_hash) VALUES (:email, :password_hash)');
            $statement->execute([':email' => $email, ':password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
            $_SESSION['user'] = ['id' => (int) $database->lastInsertId(), 'email' => $email];
            header('Location: home.php');
            exit;
        } catch (PDOException $exception) {
            $error = 'Dit emailadres bestaat al.';
        }
    } else {
        $statement = $database->prepare('SELECT id, email, password_hash FROM users WHERE email = :email');
        $statement->execute([':email' => $email]);
        $user = $statement->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => (int) $user['id'], 'email' => $user['email']];
            header('Location: home.php');
            exit;
        }
        $error = 'Email of wachtwoord is onjuist.';
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Football - <?= $mode === 'register' ? 'Account aanmaken' : 'Login' ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="header">
        <a href="index.php" class="logo"><img src="Logo.png" alt="Logo"></a>
        <nav><div class="center-nav"><a href="home.php">Home</a><a href="rooster.php">Teams</a></div><a href="index.php" class="active login-link">Login</a></nav>
    </header>
    <main class="main login-main">
        <div class="login-box">
            <h1><?= $mode === 'register' ? 'Account aanmaken' : 'Log in om verder te gaan' ?></h1>
            <p class="subtitle">Gebruik je emailadres om je eigen voetbalplanning te beheren.</p>
            <?php if ($error !== ''): ?><p class="form-error" role="alert"><?= escape($error) ?></p><?php endif; ?>
            <form method="post">
                <input type="hidden" name="mode" value="<?= escape($mode) ?>">
                <div class="input-group"><label for="email">Email</label><input type="email" id="email" name="email" placeholder="naam@voorbeeld.nl" required value="<?= escape($_POST['email'] ?? '') ?>"></div>
                <div class="input-group"><label for="password">Wachtwoord</label><input type="password" id="password" name="password" placeholder="Minimaal 6 tekens" minlength="6" required></div>
                <button type="submit"><?= $mode === 'register' ? 'Account aanmaken' : 'Log in' ?></button>
            </form>
            <p class="register">
                <?php if ($mode === 'register'): ?>Al een account? <a href="index.php">Log in</a>
                <?php else: ?>Nog geen account? <a href="index.php?mode=register">Account aanmaken</a><?php endif; ?>
            </p>
        </div>
    </main>
</body>
</html>