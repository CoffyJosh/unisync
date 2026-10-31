<?php

session_start();

// Remove all session variables.
$_SESSION = [];

// Destroy the current session.
session_destroy();

// Redirect to the public homepage.
header('Location: ../../public/index.php');
exit;
