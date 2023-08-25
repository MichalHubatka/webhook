<?php declare(strict_types = 1);

namespace Hubatka\Webhook;

use Dotenv\Dotenv;

require_once "../vendor/autoload.php";

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

if (empty($_POST['token']) || $_POST['token'] !== $_ENV['INCOMING_AUTH_TOKEN'] || empty($_POST['user_name']) || empty($_POST['text'])) {
    http_response_code(400);
    die();
}

$text = sprintf('%s: %s', $_POST['user_name'], $_POST['text']);
$postData = [
    'type' => 'message',
    'attachments' => [
        [
            'contentType' => 'application/vnd.microsoft.card.adaptive',
            'content' => [
                'type' => 'AdaptiveCard',
                'body' => [
                    [
                        'type' => 'TextBlock',
                        'text' => $text,
                    ],
                ],
                '$schema' => 'http => //adaptivecards.io/schemas/adaptive-card.json',
                'version' => '1.0',
            ],
        ],
    ],
];

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $_ENV['MICROSOFT_TEAMS_URL']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

$headers[] = 'Content-Type: application/json';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
    http_response_code(500);
    die();
}

curl_close($ch);

echo 'Success';
