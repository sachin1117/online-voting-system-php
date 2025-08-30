<?php
require_once 'config.php';

// Destroy session and redirect
session_destroy();
showAlert('You have been logged out successfully.', 'success');
redirectTo('index.php');
?>