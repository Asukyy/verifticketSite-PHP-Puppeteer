<?php

require __DIR__ . '/vendor/autoload.php';

use Twilio\Rest\Client;
use Nesk\Puphpeteer\Puppeteer;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();


$sid = $_ENV['TWILIO_ACCOUNT_SID'];
$token = $_ENV['TWILIO_AUTH_TOKEN'];
$client = new Client($sid, $token);

// Initialiser Puppeteer
$puppeteer = new Puppeteer;
$browser = $puppeteer->launch();
$page = $browser->newPage();
$date = date('Y-m-d H:i:s');

while (true) {
    // Aller sur la page des billets
    $page->goto('https://tickets.rugbyworldcup.com/fr');

    // clique sur le 5 eme js-show-offers
    $page->click('.actions-wrapper');
    $page->waitForSelector('.btn-resale');
    $isAvailable = $page->evaluate('document.querySelector(".btn") !== null');

    // Envoyer un SMS si des billets sont disponibles
    if ($isAvailable) {
        $message = $client->messages->create(
            $_ENV['MY_PHONE_NUMBER'],
            array(
                'from' => $_ENV['TWILIO_PHONE_NUMBER'],
                'body' => 'Billets dispo'
            )
        );
        if ($message) {
            echo "$date - Message envoyé\n";
        } else {
            echo "$date - Message non envoyé\n";
        }
    } else {
        echo "Pas de billets disponibles\n";
    }

    // Attendre 5 minutes avant la prochaine itération
    sleep(300);
}

// Fermer le navigateur
$browser->close();
?>