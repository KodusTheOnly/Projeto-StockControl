<?php
// Gerenciamento de sessão e controle de acesso
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Verifica se o usuário está logado
function is_logged_in(): bool {
  return isset($_SESSION['user']);
}

// Redireciona para login se não estiver autenticado
function require_login(): void {
  if (!is_logged_in()) {
    header('Location: login.html');
    exit;
  }
}

// Valida se o usuário tem o perfil necessário (ADMIN ou OPERADOR)
function require_role(string $role): void {
  require_login();
  if (($_SESSION['user']['perfil'] ?? '') !== $role) {
    http_response_code(403);
    echo 'Acesso negado. Perfil insuficiente.';
    exit;
  }
}