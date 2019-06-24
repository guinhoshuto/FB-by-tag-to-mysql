<?php
require_once __DIR__ . '/vendor/autoload.php'; // change path as needed

//--------------------------------

$fb = new Facebook\Facebook([
    'app_id' => '826014457551588', // Replace {app-id} with your app id
    'app_secret' => '92a9c242546a9e1863b76a750763a8e0',
    'default_graph_version' => 'v3.2',
]);

$helper = $fb->getRedirectLoginHelper();

$permissions = ['email']; // Optional permissions
$loginUrl = $helper->getLoginUrl('http://vendo-bolo.com/sheets-crud/fb-callback.php', $permissions);

echo '<a href="' . htmlspecialchars($loginUrl) . '">Log in with Facebook!</a>';
?>