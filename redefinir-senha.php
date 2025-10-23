<?php
// conexao banco
include_once 'conexao.php'; 

// msg erro banco
$token = $_GET['token'] ?? '';
if ($token === '') { http_response_code(400); echo 'Token ausente.'; exit; } 

// busca token de login
$stmt = $conn->prepare(" 
  SELECT t.id, t.usuario_id, t.expira_em, t.usado_em, u.email
  FROM senha_tokens t
  JOIN usuarios u ON u.id = t.usuario_id
  WHERE t.token = ?
");
$stmt->bind_param('s', $token);
$stmt->execute();
$res = $stmt->get_result();
$tk = $res->fetch_assoc(); 

// expiração do token
$agora = new DateTime();
$expira = $tk ? new DateTime($tk['expira_em']) : null;
$valido = $tk && !$tk['usado_em'] && $expira && $agora <= $expira;
if (!$valido) { echo 'Link inválido ou expirado.'; exit; }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login</title>
  <link rel="stylesheet" href="assets/css/login.css" /> <!-- UTILIZANDO COMO REFERÊNCIA O CSS DE LOGIN -->
</head>
<body>
<!-- Aplica troca de senha -->
<div class="caixa-formulario">
  <form class="formulario" action="atualizar-senha.php" method="post">
    <span class="titulo">Recuperar senha</span>
    <span class="subtitulo">Recupere sua conta com o seu email</span>
    <div class="container-formulario">
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES); ?>">
    <input type="password" class="entrada" name="senha" placeholder="Nova senha" required>
    </div>
    <button type="submit">Atualizar senha</button>
  </form>
</div>
</body>
</html>