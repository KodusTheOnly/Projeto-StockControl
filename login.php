<?php
// Processamento do Login
include_once 'conexao.php'; // Conexão banco
if (session_status() !== PHP_SESSION_ACTIVE) session_start(); // sessão do usuario

$email = trim($_POST['email'] ?? ''); /* lê email e senha*/
$senha = $_POST['senha'] ?? ''; 

if ($email === '' || $senha === '') {
  echo "<script>alert('Informe e-mail e senha');history.back();</script>";
  exit;
} // campo vazio -> erro

$stmt = $conn->prepare("SELECT id, nome, email, senha_hash, perfil FROM usuarios WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc(); // consulta email banco de dados

if ($user && password_verify($senha, $user['senha_hash'])) { // usuario existe?
  session_regenerate_id(true);
  $_SESSION['user'] = [
    'id'     => (int)$user['id'],
    'nome'   => $user['nome'],
    'email'  => $user['email'],
    'perfil' => $user['perfil'],
  ];
  //! AJUSTAR DEPOIS - AQUI É A PAGINA ONDE VOU MANDAR O USUÁRIO DEPOIS DO LOGIN DAR CERTO
  header('Location: cadastro_produtos.html'); // redireciona para a tela de produtos
  exit;
}

echo "<script>alert('Credenciais inválidas');history.back();</script>"; // msg erro 
