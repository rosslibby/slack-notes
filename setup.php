<?php

    require 'vendor/autoload.php';

    $dotenv = new Dotenv\Dotenv(__DIR__);
    $dotenv->load();

	$client_id = $_ENV['CLIENT_ID'];
	$client_secret = $_ENV['CLIENT_SECRET'];
	$code = $_GET['code'];
	$redirect_uri = $_ENV['REDIRECT_URI'];
	$url = "https://slack.com/api/oauth.access";

	if (isset($code)) {
		$data = [
			'client_id' => $client_id,
			'client_secret' => $client_secret,
			'code' => $code,
			'redirect_uri' => $redirect_uri
		];

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);

		curl_close($ch);

		$result = json_decode($response, true);

		if ($result['ok']) {
			echo "You have successfully installed the Notes integration";
		}
	}