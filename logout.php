<?php
// RF02 - Encerra sessão do usuário
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$_SESSION = [];
session_destroy();
header('Location: login.html'); 
exit;