<?php
include 'conexao.php';

$id = $_GET['id'];

$sql = "DELETE FROM cadastro_usuarios.filiais WHERE id=$id";

if (mysqli_query($conn, $sql)) {
    echo "Filial excluída!";
} else {
    echo "Erro: " . mysqli_error($conn);
}
?>
<a href="listar_filiais.php">Voltar</a>