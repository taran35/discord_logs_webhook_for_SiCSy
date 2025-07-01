<?php
session_start();
require_once '../../account/bdd.php';

$configPath = "../../themes-admin/config.json";
$json = file_get_contents($configPath);
$data = json_decode($json, true);
$fenetre = basename(__FILE__);
$folder = $data['theme'];

$configPath2 = "../../themes-admin/" . $folder . "/config.json";
$json2 = file_get_contents($configPath2);
$data2 = json_decode($json2, true);
$file = $data2[$fenetre];
$basePath = $data2['base'];
$base = "/themes-admin/" . $folder . "/" . $basePath;


if (isset($_SESSION['adm_token'])) {
    $token = $_SESSION['adm_token'];
    $sql = "SELECT * FROM adm_token WHERE token = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    if ($result->num_rows == 0) {
        header('Location: ../../admin/login.php');
        exit;
    }

} else {
    header('Location: ../../admin/login.php');
    exit;
}

$configPath = "config.json";
$json = file_get_contents($configPath);
$config = json_decode($json, true);
$webhookUrl = $config["param"]["webhook_url"] ?? null;
$setup = $config["setup"];
$caCertPath = $config["param"]["cacert.pem"];


?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Création de compte</title>
    <link rel="stylesheet" href="<?php echo $base ?>">
</head>

<html>

<body>
    <header>
        <div style="display:flex; justify-content: space-between; align-items:center;">
            <button onclick="window.location.href='dash.php'" id="home" aria-label="retour a la page d'accueil" style="
            background:none; 
            border:none; 
            color:white; 
            font-size:1.5rem; 
            cursor:pointer;
        ">🏠</button>
            <div>Bienvenue, <?= htmlspecialchars($_SESSION['username']) ?> 👋</div>
            <button id="theme-toggle" aria-label="Basculer le thème" style="
            background:none; 
            border:none; 
            color:white; 
            font-size:1.5rem; 
            cursor:pointer;
        ">🌙</button>
        </div>
    </header>
    <main class="container" style="display: flex; margin: 0;">
    <div class="setup" style="border-radius: 15px; border: 1px solid black; margin: 20px; padding: 20px;">
                <h1> Etat de la configuration : </h1>
        <hr style="width: 80%; margin-bottom: 30px;">
        <?php if ($setup == false) { echo setup(); } else { echo "<h1 style='color: green'> Configuration déjà effectuée</h1>"; }
        ?>
    </div>
    <div class="webhook" style="border-radius: 15px; border: 1px solid black; margin: 20px; padding: 20px;">
        <h1> Etat du webhook : </h1>
        <hr style="width: 80%; margin-bottom: 30px;">
    <?php if (isset($webhookUrl)) {
        echo verifierWebhookDiscord($webhookUrl, $caCertPath);
    } else {
        echo 'Webhook non défini, impossible de le tester';
    } ?>
</div>
</main>
    <footer>
        <p><a class="logout" href="logout.php">Se déconnecter</a></p>
        <p class="credits"><a class="credits2" href="https://github.com/taran35/cloud">Copyright © 2025 Taran35</a></p>
    </footer>
</body>
<script>
    const themeToggleBtn = document.getElementById('theme-toggle');
    const currentTheme = localStorage.getItem('theme');

    if (currentTheme) {
        document.documentElement.setAttribute('data-theme', currentTheme);
        themeToggleBtn.textContent = currentTheme === 'dark' ? '☀️' : '🌙';
    } else {
        document.documentElement.setAttribute('data-theme', 'light');
        themeToggleBtn.textContent = '🌙';
        localStorage.setItem('theme', 'light');
    }

    function switchTheme() {
        const theme = document.documentElement.getAttribute('data-theme');
        if (theme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'light');
            themeToggleBtn.textContent = '🌙';
            localStorage.setItem('theme', 'light');
        } else {
            document.documentElement.setAttribute('data-theme', 'dark');
            themeToggleBtn.textContent = '☀️';
            localStorage.setItem('theme', 'dark');
        }
    }

    themeToggleBtn.addEventListener('click', switchTheme);
</script>
<style>
    .container {
        flex-direction: row;
    }
    .webhook, .setup {
        max-width: 45%;
    }
    @media screen and (max-width: 768px) {
        .container {
            flex-direction: column;
        }
        .webhook, .setup {
            max-width: 90%;
        }
    }
</style>
</html>

<?php

function verifierWebhookDiscord($url, $caCertPath)
{
    $testPayload = json_encode([
        "content" => "Test de vérification du webhook."
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($testPayload)
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $testPayload);
    curl_setopt($ch, CURLOPT_CAINFO, $caCertPath);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);

    curl_close($ch);

    if ($curlError) {
        return "Erreur cURL : $curlError";
    }

    if ($httpCode === 204) {
        return "<h1 style='color: green'>Webhook valide et fonctionnel</h1>"; 
    } else {
        return "<h1 style='color: red'>Webhook invalide</h1>";
    }
}


function setup()
{
    $configPath = "config.json";
    $json = file_get_contents($configPath);
    $data = json_decode($json, true);
    $setup = $data["setup"];
    if ($setup === true) {
        return "<h1 style='color: green'> Configuration déjà effectuée</h1>";
    }

    $path = "../../main/cloud_script.js";
    $code = file_get_contents($path);

    $webhookCall = "Webhook(type, parent, name, content);";

    $fetchRegex = '/function\s+logs\s*\(\s*type\s*,\s*parent\s*,\s*name\s*,\s*content\s*\)\s*\{/';


    $fetchExists = preg_match($fetchRegex, $code);

    $initExists = stripos($code, '//initialisation') !== false;

    if (!$fetchExists || !$initExists) {
        return "Fonction logs trouvée ? " . ($fetchExists ? "<p style='color: green'>Oui</p>" : "<p style='color: red'>Non</p>") . "<br>" .
       "Commentaire //INITIALISATION trouvé ? " . ($initExists ? "<p style='color: green'>Oui</p>" : "<p style='color: red'>Non</p>") . "<br>" .
       "<h1 style='color: red;'>Impossible de configurer le module</h1>";
    }
    if (strpos($code, $webhookCall) === false) {
        $code = preg_replace_callback(
            $fetchRegex,
            function ($matches) use ($webhookCall) {

                return $matches[0] . "\n    " . $webhookCall;
            },
            $code
        );
    }

    if (strpos($code, 'function Webhook(') === false) {
        $webhookFunction = <<<JS
    function Webhook(type, parent, name, content) {
        const Pcontent = content.replace(/\\r?\\n/g, '\\n');
        if (parent == '/') {
            var path = '/' + name;
        } else {
            var path = parent + '/' + name;
        }
        fetch('./modules/discord_logs_webhook_for_SiCSy/discord.php?type=' + encodeURIComponent(type) + '&path=' + encodeURIComponent(path) + '&content=' + encodeURIComponent(Pcontent), {
            headers: {
                'X-Requested-With': 'webhook'
            }
        })
        .then(response => response.text())
        .then(response => {
            if (response == 'success') {
            } else {
                console.log("erreur lors de l'envoie des logs via webhook")
            }
        })
    }
JS;

        $code = preg_replace('/\/\/INITIALISATION/i', $webhookFunction . "\n\n//INITIALISATION", $code);
    }

    file_put_contents($path, $code);
    echo "<h1 style='color: green;'>Configuration effectuée</h1>";
    $data["setup"] = true;
    file_put_contents($configPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

}