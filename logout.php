<?php require 'config.php';
$_SESSION = []; session_destroy(); session_start(); flash('success', 'You have been logged out.');
header('Location: index.php');
