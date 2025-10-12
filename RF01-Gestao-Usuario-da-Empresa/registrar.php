<?php
// recebe o POST do criar-conta.html e grava no MySQL
include_once("conexao.php");
// Pega os campos
$nome  = isset($_POST["nome"])  ? trim($_POST["nome"])  : "";
$email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
$senha = isset($_POST["senha"]) ? $_POST["senha"] : "";
//! RF01.3 VALIDAÇÃO EMAIL
if ($nome === "" || $email === "" || $senha === "") {
    echo "<script>alert('Preencha todos os campos');history.back();</script>";
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('E-mail inválido');history.back();</script>";
    exit;
}
//! RF01.4 VALIDAÇÃO SENHA
if (strlen($senha) < 6) {
    echo "<script>alert('A senha precisa ter pelo menos 6 caracteres');history.back();</script>";
    exit;
}
//! RF01.5 CRIPTOGRAFAR SENHA
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);
//! RF01.1 CADASTRO USUARIO SQL
$sql = "INSERT INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
if(!$stmt){
    echo "<h3>Erro ao preparar a query: " . htmlspecialchars($conn->error) . "</h3>";
    exit;
}
$stmt->bind_param("sss", $nome, $email, $senha_hash);
// Executa
//! RF01.7 + RF01.8 MENSAGEM SUCESSO + REDIRECIONA 
if ($stmt->execute()) {
//! RF01.6 IMPEDIR EMAIL DUPLICADO
    echo "<script>alert('Conta criada com sucesso!');window.location.href='login.html';</script>";
} else {
    if ($conn->errno === 1062) {
        echo "<script>alert('Este e-mail já está cadastrado');history.back();</script>";
    } else {
        echo "<h3>Erro ao salvar: " . htmlspecialchars($conn->error) . "</h3>";
    }
}
$stmt->close();
$conn->close();
?>
