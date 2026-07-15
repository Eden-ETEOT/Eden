<?php
require_once "../config/conexao.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT);
    $cpf = $_POST["cpf"];
    $telefone = $_POST["telefone"];

    $sql = "INSERT INTO usuario (nome, email, senha, cpf, telefone)
            VALUES (:nome, :email, :senha, :cpf, :telefone)";

    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(":nome", $nome);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":senha", $senha);
    $stmt->bindParam(":cpf", $cpf);
    $stmt->bindParam(":telefone", $telefone);

    if ($stmt->execute()) {
        echo "Usuário cadastrado!";
    } else {
        echo "Erro ao cadastrar.";
        die();
    }
}
?>

<form method="POST">
    <input type="text" name="nome" placeholder="Nome" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="senha" placeholder="Senha" required>
    <input type="text" name="cpf" placeholder="CPF" required>
    <input type="text" name="telefone" placeholder="Telefone" required>
    <button type="submit">Cadastrar</button>
</form>