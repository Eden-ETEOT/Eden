<?php
// Conexão com o banco de dados
// Padrão XAMPP: localhost / root / senha vazia / banco 'eden'
$host     = "localhost";
// Override via ambiente (ex.: EDEN_DB=eden_teste) para testar features sem tocar o banco compartilhado.
$banco    = getenv("EDEN_DB") ?: "eden";
$usuario  = "root";
$senha    = "";

try {
    $conexao = new PDO("mysql:host=$host;dbname=$banco;charset=utf8", $usuario, $senha);
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
} catch (Exception $e) {
    die("Erro genérico: " . $e->getMessage());
}
