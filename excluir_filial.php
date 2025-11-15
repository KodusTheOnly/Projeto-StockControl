<?php
// RF08.4 - Excluir Filial
session_start();
if (!isset($_SESSION['logado_filial'])) {
    header("Location: login_filiais.php");
    exit;
}

include 'conexao.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    echo "<script>alert('ID inválido');window.location='listar_filiais.php';</script>";
    exit;
}

// Busca nome da filial antes de excluir
$stmt = $conn->prepare("SELECT nome FROM filiais WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$filial = $result->fetch_assoc();
$stmt->close();

if (!$filial) {
    echo "<script>alert('Filial não encontrada');window.location='listar_filiais.php';</script>";
    exit;
}

// Executa exclusão
$stmt = $conn->prepare("DELETE FROM filiais WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $msg = "Filial '{$filial['nome']}' excluída com sucesso!";
        echo "<script>alert('" . addslashes($msg) . "');window.location='listar_filiais.php';</script>";
    } else {
        echo "<script>alert('Filial não encontrada');window.location='listar_filiais.php';</script>";
    }
} else {
    echo "<script>alert('Erro ao excluir filial');window.location='listar_filiais.php';</script>";
}

$stmt->close();
$conn->close();
?>