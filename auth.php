<?php
// Serve para centralizar sessão e checagens
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function is_logged_in(): bool {
  return isset($_SESSION['user']);
}

function require_login(): void {
  if (!is_logged_in()) {
    header('Location: login.html');
    exit;
  }
}

function require_role(string $role): void {
  require_login();
  if (($_SESSION['user']['perfil'] ?? '') !== $role) {
    http_response_code(403);
    echo 'Acesso negado. Perfil insuficiente.';
    exit;
  }
}
