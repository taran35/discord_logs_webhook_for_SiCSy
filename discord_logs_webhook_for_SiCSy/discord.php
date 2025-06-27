<?php
session_start();
$configPath = "config.json";
$json = file_get_contents($configPath);
$config = json_decode($json, true);
$webhookUrl = $config["param"]["webhook_url"] ?? null;
$webhookName = $config["param"]["webhook_name"] ?? "SiCSy Logs";
$webhookAvatar = $config["param"]["webhook_avatar"];
$caCertPath =  $config["param"]["cacert.pem"];
$status = $config["status"];
if ($status === "off") {
    exit;
}
if (!isset($webhookUrl)) {
    echo("webhook_undefined");
    exit;
}
if (isset($webhookAvatar)) {
    $webhookAvatar = "https://raw.githubusercontent.com/taran35/discord_logs_webhook_for_SiCSy/refs/heads/main/favicon.png";
}


$path = $_GET['path'] ?? '/';
$type = $_GET['type'] ?? null;
$content = $_GET['content'] ?? null;
$user = $_SESSION['username'];
if ($type === null) {
    http_response_code(400);
    echo 'error';
    exit;
}
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}

if ($type === "uploadFile") {
    $color = hexdec("007bff");
} else if ($type === "deleteFile") {
    $color = hexdec("fd0000");
} else if ($type === "createFile") {
    $color = hexdec("17a2b8");
} else if ($type === "downloadFile") {
    $color = hexdec("6f42c1");
} else if ($type === "createFolder") {
    $color = hexdec("28a745");
} else if ($type === "deleteFolder") {
    $color = hexdec("e83e8c");
} else if ($type === "moveFile") {
    $color = hexdec("fd7e14");
} else if ($type === "renameFile") {
    $color = hexdec("20c997");
} else if ($type === "updateFile") {
    $color = hexdec("c9c620");
} else {
    $color = hexdec("95a5a6"); //DEFAULT
}


$data = [
    "username" => $webhookName,
    "avatar_url" => $webhookAvatar,
    "embeds" => [
        [
            "title" => $type,
            "description" => "",
            "color" => $color,
            "footer" => [
                "text" => "SiCSy logs made by taran35",
                "icon_url" => ""
            ],
            "author" => [
                "name" => $webhookName,
                "url" => "https://github.com/taran35",
                "icon_url" => $webhookAvatar
            ],
            "fields" => [
                [
                    "name" => "Date : ",
                    "value" => "",
                    "inline" => false
                ],
                [
                    "name" => "IP : ",
                    "value" => $ip,
                    "inline" => false
                ],
                [
                    "name" => "Utilisateur : ",
                    "value" => $user,
                    "inline" => true
                ],
                [
                    "name" => "Path : ",
                    "value" => $path,
                    "inline" => false
                ],
                [
                    "name" => "Contenu : ",
                    "value" => $content,
                    "inline" => false
                ]
            ]
        ]
    ]
];

$ch = curl_init($webhookUrl); 

if ($ch === false) {
    echo 'erreur cURL';
    exit;
}

curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-type: application/json']);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

curl_setopt($ch, CURLOPT_CAINFO, $caCertPath);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'erreur cURL';
} else {
    echo 'success';
}

curl_close($ch);
?>
