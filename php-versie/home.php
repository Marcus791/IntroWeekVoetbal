<?php
declare(strict_types=1);
require __DIR__ . '/db.php';
$user = requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $day = (string) ($_POST['day'] ?? 'maandag');
    $time = (string) ($_POST['time'] ?? '08:00');
    $stadium = (string) ($_POST['stadium'] ?? 'de-kuip');
    $stadiums = ['de-kuip' => 'De Kuip', 'het-kasteel' => 'Het Kasteel', 'varkenoord' => 'Sportcomplex Varkenoord'];
    $days = ['maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag', 'zondag'];
    if (in_array($day, $days, true) && isset($stadiums[$stadium]) && preg_match('/^(0[8-9]|1[0-9]|2[0-2]):00$/', $time)) {
        $statement = $database->prepare('INSERT INTO schedule (week, day, start_time, title, location, user_id) VALUES (1, :day, :start_time, :title, :location, :user_id)');
        $statement->execute([':day' => $day, ':start_time' => $time, ':title' => 'Voetbalplanning', ':location' => $stadiums[$stadium], ':user_id' => $user['id']]);
    }
    header('Location: rooster.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Football - Home</title><link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
</head>
<body class="home-page">
    <header class="header">
        <a href="index.php" class="logo"><img src="Logo.png" alt="Logo"></a>
        <nav><div class="center-nav"><a href="home.php" class="active">Home</a><a href="rooster.php">Teams</a></div><a href="logout.php" class="login-link">Uitloggen</a></nav>
    </header>
    <main class="main">
        <section class="home-dashboard">
            <div class="dashboard-copy">
                <p class="eyebrow">Plan je voetbalmoment</p><h1>Welkom!</h1>
                <p class="subtitle">Kies een dag en uur en bekijk direct de kaart van Nederland.</p>
                <form method="post">
                    <div class="control-grid">
                        <div class="control-card"><label for="day-select">Dagen</label><select id="day-select" name="day">
                            <?php foreach (['maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag', 'zondag'] as $day): ?><option value="<?= $day ?>"><?= ucfirst($day) ?></option><?php endforeach; ?>
                        </select></div>
                        <div class="control-card"><label for="stadium-select">Stadions Rotterdam</label><select id="stadium-select" name="stadium"><option value="de-kuip">De Kuip</option><option value="het-kasteel">Het Kasteel</option><option value="varkenoord">Sportcomplex Varkenoord</option></select></div>
                        <div class="control-card"><label for="time-select">Uren</label><select id="time-select" name="time"><?php for ($hour = 8; $hour <= 22; $hour++): ?><option value="<?= sprintf('%02d:00', $hour) ?>"><?= sprintf('%02d:00', $hour) ?></option><?php endfor; ?></select></div>
                    </div>
                    <button type="submit" class="primary-action">Bekijk planning</button>
                </form>
            </div>
            <div class="map-panel"><div id="netherlands-map" aria-label="Kaart van Nederland"></div></div>
        </section>
    </main>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        const stadiums = {'de-kuip': {name: 'De Kuip', location: [51.8936, 4.5239], zoom: 15}, 'het-kasteel': {name: 'Het Kasteel', location: [51.9198, 4.4328], zoom: 15}, 'varkenoord': {name: 'Sportcomplex Varkenoord', location: [51.8886, 4.5061], zoom: 15}};
        const map = L.map('netherlands-map', {zoomControl: true, scrollWheelZoom: false}).setView(stadiums['de-kuip'].location, 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19, attribution: '&copy; OpenStreetMap contributors'}).addTo(map);
        const marker = L.marker(stadiums['de-kuip'].location).addTo(map).bindPopup(stadiums['de-kuip'].name).openPopup();
        document.getElementById('stadium-select').addEventListener('change', (event) => { const stadium = stadiums[event.target.value]; map.setView(stadium.location, stadium.zoom); marker.setLatLng(stadium.location).setPopupContent(stadium.name).openPopup(); });
    </script>
</body>
</html>