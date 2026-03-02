<?php

session_start();
if(isset($_REQUEST['u']) && !empty($_REQUEST['u'])){
	$_SESSION['callback_u'] = $_REQUEST['u'];
}

require('ig-php-sdk/ig-php-sdk.php');
$ig = new IG();

if(isset($_REQUEST['clogin'])){
	$ig->checkLogin();
}

if(isset($_REQUEST['access_token'])){
	$ig->getAccessToken();
}

if(isset($_REQUEST['user_media'])){
	$ig->getUserMedia();
}

echo $ig->print_response();
?>