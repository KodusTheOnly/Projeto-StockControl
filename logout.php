<?php
// PAGINA DE LOGOUT
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$_SESSION = [];
session_destroy();
header('Location: login.html'); exit;
