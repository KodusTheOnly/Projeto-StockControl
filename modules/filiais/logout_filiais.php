<?php
session_start();
session_unset();   // limpa variáveis de sessão
session_destroy(); // encerra a sessão

// redireciona para a página de cadastro de produtos
header("Location: /StoqInsp/modules/filiais/login_filiais.php");
exit;
?>