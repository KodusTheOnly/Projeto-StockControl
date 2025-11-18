<?php
// RF08 - Menu principal de gestão de filiais
session_start();
if (!isset($_SESSION['logado_filial'])) {
    header("Location: login_filiais.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestão de Filiais</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      margin: 0;
      padding: 0;
    }

    header {
      background-color: #0077cc;
      color: white;
      padding: 20px;
      text-align: center;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    header h1 {
      margin: 0;
      font-size: 28px;
    }

    .usuario-info {
      font-size: 14px;
      margin-top: 8px;
      opacity: 0.9;
    }

    nav {
      background-color: #eee;
      padding: 15px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    nav ul {
      list-style: none;
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 20px;
      margin: 0;
      padding: 0;
    }

    nav ul li a {
      text-decoration: none;
      color: #0077cc;
      font-weight: bold;
      padding: 10px 20px;
      border-radius: 6px;
      display: block;
      transition: all 0.3s ease;
    }

    nav ul li a:hover {
      background-color: #0077cc;
      color: white;
    }

    main {
      padding: 40px 20px;
      text-align: center;
      max-width: 800px;
      margin: 0 auto;
    }

    main h2 {
      color: #333;
      margin-bottom: 20px;
    }

    main p {
      color: #666;
      font-size: 16px;
      line-height: 1.6;
    }

    .menu-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-top: 30px;
    }

    .card {
      background: white;
      padding: 30px 20px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    }

    .card a {
      text-decoration: none;
      color: #0077cc;
      font-weight: bold;
      font-size: 18px;
    }

    .card p {
      margin-top: 10px;
      font-size: 14px;
      color: #888;
    }

    .logout {
      position: absolute;
      top: 20px;
      right: 20px;
      background-color: rgba(255,255,255,0.2);
      color: white;
      padding: 8px 16px;
      border-radius: 4px;
      text-decoration: none;
      font-size: 14px;
    }

    .logout:hover {
      background-color: rgba(255,255,255,0.3);
    }

    @media (max-width: 600px) {
      nav ul {
        flex-direction: column;
        gap: 10px;
      }
      
      .menu-cards {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>

  <header>
    <a href="logout_filiais.php" class="logout">Sair</a>
    <h1>Gestão de Filiais</h1>
    <div class="usuario-info">
      Usuário: <?php echo htmlspecialchars($_SESSION['usuario_filial'] ?? 'Admin'); ?>
    </div>
</header>

<nav>
    <ul>
      <li><a href="cadastrar_filial.php">Cadastrar Filial</a></li>
      <li><a href="listar_filiais.php">Listar Filiais</a></li>
      <li><a href="../autenticacao/login.html">Voltar ao Sistema Principal</a></li>
    </ul>
</nav>

  <main>
    <h2>Bem-vindo ao Sistema de Gerenciamento de Filiais</h2>
    <p>Use o menu acima ou os cards abaixo para navegar pelas funcionalidades do sistema.</p>

    <div class="menu-cards">
      <div class="card">
        <a href="cadastrar_filial.php">📝 Cadastrar</a>
        <p>Adicionar nova filial ao sistema</p>
      </div>
      
      <div class="card">
        <a href="listar_filiais.php">📋 Listar</a>
        <p>Ver todas as filiais cadastradas</p>
      </div>
    </div>
  </main>

</body>
</html>
