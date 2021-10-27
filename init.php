<?php
	session_start();
	$host = 'neva';
	$user = 'root';
	$password = '';
	$db_name = 'test';

	$link = mysqli_connect($host, $user, $password, $db_name);
	mysqli_query($link, "SET NAMES utf-8");


?>