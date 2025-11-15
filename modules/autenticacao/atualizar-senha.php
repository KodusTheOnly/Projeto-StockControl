<?php
// RF02.3 - Processa atualização de senha
include_once '../../config/conexao.php';

// Captura dados do formulário
$token = $_POST['token'] ?? '';
$senha = $_POST['senha'] ?? '';

if ($token === '' || $senha === '') { 
    echo "<script>alert('Dados insuficientes');history.back();</script>"; 
    exit; 
}

$conn->begin_transaction();

try {
  // Busca e trava o token para evitar uso simultâneo
  $q = $conn->prepare("SELECT id, usuario_id, expira_em, usado_em FROM senha_tokens WHERE token = ? FOR UPDATE");
  $q->bind_param('s', $token);
  $q->execute();
  $res = $q->get_result();
  $tk = $res->fetch_assoc();

  // Valida se token ainda é utilizável
  if (!$tk || $tk['usado_em'] !== null || new DateTime() > new DateTime($tk['expira_em'])) {
    throw new Exception('Token inválido ou expirado');
  }

  // Atualiza senha do usuário
  $hash = password_hash($senha, PASSWORD_DEFAULT);
  $u = $conn->prepare("UPDATE usuarios SET senha_hash = ? WHERE id = ?");
  $u->bind_param('si', $hash, $tk['usuario_id']);
  $u->execute();

  // Marca token como usado para impedir reutilização
  $m = $conn->prepare("UPDATE senha_tokens SET usado_em = NOW() WHERE id = ?");
  $m->bind_param('i', $tk['id']);
  $m->execute();

  $conn->commit();
  echo "<script>alert('Senha atualizada com sucesso');window.location='login.html';</script>";

} catch (Throwable $e) {
  $conn->rollback();
  echo "<script>alert('Falha ao atualizar senha');window.location='login.html';</script>";
}
