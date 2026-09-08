<?php

/**
 * Endpoint de Ocorrências (chamados)
 * Recebe POST do modal em dashboard.php e responde em JSON.
 * ações suportadas: criar | editar | deletar
 */

session_start();
header('Content-Type: application/json; charset=utf-8');
include '../config/conexao.php';

// Só usuário logado pode mexer nas ocorrências
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'erro' => 'Sessão expirada. Faça login novamente.']);
    exit;
}

$acao = $_POST['acao'] ?? '';

try {
    if ($acao === 'criar') {
        criarOcorrencia($conexao);
    } elseif ($acao === 'editar') {
        editarOcorrencia($conexao);
    } elseif ($acao === 'deletar') {
        deletarOcorrencia($conexao);
    } else {
        throw new Exception('Ação inválida.');
    }
} catch (PDOException $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => 'Erro no banco de dados: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}

function dadosDoFormulario(): array
{
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $categoria = $_POST['categoria'] ?? '';
    $prioridade = $_POST['prioridade'] ?? '';
    $status = $_POST['status'] ?? 'analise';
    $morador = $_POST['morador'] ?? '';

    if ($titulo === '' || $descricao === '' || $categoria === '' || $prioridade === '') {
        throw new Exception('Preencha todos os campos obrigatórios.');
    }

    $statusValidos = ['analise', 'andamento', 'resolvida', 'cancelada'];
    if (!in_array($status, $statusValidos, true)) {
        $status = 'analise';
    }

    return [
        'titulo' => $titulo,
        'descricao' => $descricao,
        'categoria' => (int) $categoria,
        'prioridade' => (int) $prioridade,
        'status' => $status,
        'morador' => $morador !== '' ? (int) $morador : null,
    ];
}

function criarOcorrencia(PDO $conexao): void
{
    $dados = dadosDoFormulario();

    if ($dados['morador'] === null) {
        throw new Exception('Selecione o morador responsável pela ocorrência.');
    }

    $stmt = $conexao->prepare(
        "INSERT INTO chamados (titulo, descricao, dataPedida, status, prioridade_idPrioridade, categoria_idCategoria, morador_idMorador)
         VALUES (:titulo, :descricao, NOW(), :status, :prioridade, :categoria, :morador)"
    );
    $stmt->execute([
        'titulo' => $dados['titulo'],
        'descricao' => $dados['descricao'],
        'status' => $dados['status'],
        'prioridade' => $dados['prioridade'],
        'categoria' => $dados['categoria'],
        'morador' => $dados['morador'],
    ]);

    echo json_encode(['ok' => true, 'id' => (int) $conexao->lastInsertId()]);
}

function editarOcorrencia(PDO $conexao): void
{
    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        throw new Exception('Ocorrência inválida.');
    }

    $dados = dadosDoFormulario();

    $campos = "titulo = :titulo, descricao = :descricao, status = :status,
               prioridade_idPrioridade = :prioridade, categoria_idCategoria = :categoria";
    $parametros = [
        'titulo' => $dados['titulo'],
        'descricao' => $dados['descricao'],
        'status' => $dados['status'],
        'prioridade' => $dados['prioridade'],
        'categoria' => $dados['categoria'],
        'id' => $id,
    ];

    if ($dados['morador'] !== null) {
        $campos .= ", morador_idMorador = :morador";
        $parametros['morador'] = $dados['morador'];
    }

    // Se marcou como resolvida, tenta registrar dataResolucao (coluna pode não existir ainda)
    if ($dados['status'] === 'resolvida') {
        try {
            $stmt = $conexao->prepare("UPDATE chamados SET $campos, dataResolucao = NOW() WHERE idChamados = :id");
            $stmt->execute($parametros);
            echo json_encode(['ok' => true]);
            return;
        } catch (PDOException $e) {
            // Coluna dataResolucao ainda não existe: continua sem ela abaixo
        }
    }

    $stmt = $conexao->prepare("UPDATE chamados SET $campos WHERE idChamados = :id");
    $stmt->execute($parametros);

    echo json_encode(['ok' => true]);
}

function deletarOcorrencia(PDO $conexao): void
{
    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        throw new Exception('Ocorrência inválida.');
    }

    $stmt = $conexao->prepare("DELETE FROM chamados WHERE idChamados = :id");
    $stmt->execute(['id' => $id]);

    echo json_encode(['ok' => true]);
}
