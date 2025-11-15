<?php
// RF02.1 - Autenticação de usuário
include_once '../../config/conexao.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Captura credenciais do formulário
$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

// Validação: campos obrigatórios
if ($email === '' || $senha === '') {
  echo "<script>alert('Informe e-mail e senha');history.back();</script>";
  exit;
}

// Busca usuário no banco
$stmt = $conn->prepare("SELECT id, nome, email, senha_hash, perfil FROM usuarios WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

// Verifica credenciais
if ($user && password_verify($senha, $user['senha_hash'])) {
  // Cria sessão do usuário
  session_regenerate_id(true);
  $_SESSION['user'] = [
    'id'     => (int)$user['id'],
    'nome'   => $user['nome'],
    'email'  => $user['email'],
    'perfil' => $user['perfil'],
  ];
  
  // Redireciona para área de produtos
  header('Location: ../produtos/cadastro_produtos.html');
  exit;
}

echo "<script>alert('Credenciais inválidas');history.back();</script>";
