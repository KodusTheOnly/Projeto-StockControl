<?php
// Processamento do Login
include_once 'conexao.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

if ($email === '' || $senha === '') {
  echo "<script>alert('Informe e-mail e senha');history.back();</script>";
  exit;
}

$stmt = $conn->prepare("SELECT id, nome, email, senha_hash, perfil FROM usuarios WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

if ($user && password_verify($senha, $user['senha_hash'])) {
  session_regenerate_id(true);
  $_SESSION['user'] = [
    'id'     => (int)$user['id'],
    'nome'   => $user['nome'],
    'email'  => $user['email'],
    'perfil' => $user['perfil'],
  ];
  //! AJUSTAR DEPOIS - AQUI É A PAGINA ONDE VOU MANDAR O USUÁRIO DEPOIS DO LOGIN DAR CERTO
  header('Location: index.php');
  exit;
}

echo "<script>alert('Credenciais inválidas');history.back();</script>";
