<?php
	require 'CrawlerHelper/CrawlerHelper.php';
	require 'Uzo.php';

	use DavidMagalhaes\CrawlersRepo\Uzo;

	$uzo = new Uzo('96XXXXXXX', '********');
	$login = $uzo->login();

	if ($login) {
		echo 'Balance: ' . $uzo->getBalance() . "\n";
	} else {
		echo 'Fail to login!' . "\n";
	}