<?php
session_start();
if (!isset($_SESSION['logado_filial'])) {
    header("Location: login_filiais.php");
    exit;
}
?>
 
 
 <!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Menu Principal</title>
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
    }

    nav {
      background-color: #eee;
      padding: 10px;
    }

    nav ul {
      list-style: none;
      display: flex;
      justify-content: center;
      gap: 30px;
      margin: 0;
      padding: 0;
    }

    nav ul li a {
      text-decoration: none;
      color: #0077cc;
      font-weight: bold;
      padding: 8px 16px;
      border-radius: 4px;
    }

    nav ul li a:hover {
      background-color: #0077cc;
      color: white;
    }

    main {
      padding: 40px;
      text-align: center;
    }
  </style>
</head>
<body>

  <header>
    <h1>Gestão de Filiais</h1>
  </header>

  <nav>
    <ul>
      <li><a href="cadastrar_filial.php">Cadastrar Filial</a></li>
      <li><a href="listar_filiais.php">Listar Filiais</a></li>
      <li><a href="editar_filial.php?id=1">Editar Filial (Exemplo)</a></li>
      <li><a href="excluir_filial.php?id=1">Excluir Filial (Exemplo)</a></li>
    </ul>
  </nav>

  <main>
    <p>Bem-vindo ao sistema de gerenciamento de filiais. Use o menu acima para navegar.</p>
  </main>

</body>
</html>