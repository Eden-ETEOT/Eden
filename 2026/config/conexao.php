<?php
$config = require __DIR__ . '/database.php';

$host     = $config['host'];
$banco    = $config['database'];
$usuario  = $config['username'];
$senha    = $config['password'];

try {
    $conexao = new PDO("mysql:host=$host;dbname=$banco;charset=utf8", $usuario, $senha);
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
} catch (Exception $e) {
    die("Erro genérico: " . $e->getMessage());
}
