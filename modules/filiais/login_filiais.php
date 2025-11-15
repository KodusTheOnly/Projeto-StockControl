<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Filiais</title>
  <link rel="stylesheet" href="../../assets/css/login.css" />
</head>
<body>
<div class="caixa-formulario">
  <!-- RF08 - Login específico para gestão de filiais -->
  <form class="formulario" action="valida_filiais.php" method="post">
    <span class="titulo">Gestão de Filiais</span>
    <span class="subtitulo">Acesse o sistema de filiais</span>
    <div class="container-formulario">
      <input type="text" class="entrada" name="usuario" placeholder="Usuário" required>
      <input type="password" class="entrada" name="senha" placeholder="Senha" required>
    </div>
    <button type="submit">Entrar</button>
  </form>
  <div class="secao-formulario">
    <p><a href="../autenticacao/login.html">← Voltar para login principal</a></p>
  </div>
</div>
</body>
</html>
