<?php
// Isolamento por condomínio (multi-tenancy: 1 banco central + filtro por sessão).
// Resolve o condomínio do usuário: sindico > morador (vínculo ativo) > funcionario (ativo).
require_once __DIR__ . '/convites.php';

/** Condomínio do usuário ou null (sem vínculo). */
function resolverCondominio(PDO $pdo, int $idUsuario): ?int {
    $stmt = $pdo->prepare(
        "SELECT Condominio_idCondominio FROM sindico WHERE idUsuario = :u LIMIT 1"
    );
    $stmt->execute(['u' => $idUsuario]);
    if (($c = $stmt->fetchColumn()) !== false) return (int) $c;

    $stmt = $pdo->prepare(
        "SELECT u.Condominio_idCondominio FROM morador m
         JOIN moradorunidade mu ON mu.Morador_idMorador = m.idMorador AND mu.dataFim IS NULL
         JOIN unidade u ON u.idUnidade = mu.Unidade_idUnidade
         WHERE m.idUsuario = :u LIMIT 1"
    );
    $stmt->execute(['u' => $idUsuario]);
    if (($c = $stmt->fetchColumn()) !== false) return (int) $c;

    $stmt = $pdo->prepare(
        "SELECT Condominio_idCondominio FROM funcionariocondominio fc
         JOIN funcionario f ON f.idFuncionario = fc.Funcionario_idFuncionario
         WHERE f.idUsuario = :u AND fc.dataDesligamento IS NULL LIMIT 1"
    );
    $stmt->execute(['u' => $idUsuario]);
    if (($c = $stmt->fetchColumn()) !== false) return (int) $c;

    return null;
}

/** Funcionário com vínculo ativo no condomínio? Retorna a função ou null. */
function funcaoNoCondominio(PDO $pdo, int $idUsuario, int $idCondominio): ?string {
    $stmt = $pdo->prepare(
        "SELECT f.funcao FROM funcionario f
         JOIN funcionariocondominio fc ON fc.Funcionario_idFuncionario = f.idFuncionario
         WHERE f.idUsuario = :u AND fc.Condominio_idCondominio = :c
           AND fc.dataDesligamento IS NULL LIMIT 1"
    );
    $stmt->execute(['u' => $idUsuario, 'c' => $idCondominio]);
    $f = $stmt->fetchColumn();
    return $f === false ? null : (string) $f;
}

/** Papel de exibição: Síndico > função > tipo de morador > Usuário. */
function papelUsuario(PDO $pdo, int $idUsuario, ?int $idCondominio): string {
    if ($idCondominio !== null) {
        if (eSindico($pdo, $idUsuario, $idCondominio)) return 'Síndico';
        $f = funcaoNoCondominio($pdo, $idUsuario, $idCondominio);
        if ($f !== null && $f !== '') return $f;
        $stmt = $pdo->prepare(
            "SELECT m.tipoMorador FROM morador m
             JOIN moradorunidade mu ON mu.Morador_idMorador = m.idMorador AND mu.dataFim IS NULL
             JOIN unidade u ON u.idUnidade = mu.Unidade_idUnidade
             WHERE m.idUsuario = :u AND u.Condominio_idCondominio = :c LIMIT 1"
        );
        $stmt->execute(['u' => $idUsuario, 'c' => $idCondominio]);
        $t = $stmt->fetchColumn();
        if ($t !== false) return ucfirst((string) $t);
    }
    return 'Usuário';
}

/** Pode gerenciar (síndico ou funcionário ativo do condomínio)? */
function podeGerenciar(PDO $pdo, int $idUsuario, ?int $idCondominio): bool {
    if ($idCondominio === null) return false;
    if (eSindico($pdo, $idUsuario, $idCondominio)) return true;
    return funcaoNoCondominio($pdo, $idUsuario, $idCondominio) !== null;
}

/** Condomínio da sessão (null = sem vínculo: telas mostram vazio). */
function condominioDaSessao(): ?int {
    $c = $_SESSION['id_condominio'] ?? null;
    return ($c === null || (int) $c <= 0) ? null : (int) $c;
}

/**
 * Totais/leituras usam este valor no WHERE. Sem vínculo retorna -1 (não casa com nada).
 */
function condominioFiltro(): int {
    return condominioDaSessao() ?? -1;
}

/**
 * Pertencimento genérico: o registro $id da $tabela é do condomínio $idCondominio?
 * Tabelas com Condominio_idCondominio direto: condominio(idCondominio), unidade, documentos.
 */
function pertenceAoCondominio(PDO $pdo, string $tabela, int $id, int $idCondominio): bool {
    $map = [
        'condominio' => ['pk' => 'idCondominio', 'fk' => null],
        'unidade' => ['pk' => 'idUnidade', 'fk' => 'Condominio_idCondominio'],
        'documentos' => ['pk' => 'idDocumento', 'fk' => 'Condominio_idCondominio'],
    ];
    if (!isset($map[$tabela])) return false;
    $pk = $map[$tabela]['pk'];
    if ($tabela === 'condominio') {
        return $id === $idCondominio;
    }
    $stmt = $pdo->prepare("SELECT 1 FROM $tabela WHERE $pk = :id AND {$map[$tabela]['fk']} = :c LIMIT 1");
    $stmt->execute(['id' => $id, 'c' => $idCondominio]);
    return (bool) $stmt->fetchColumn();
}

/** Ocorrência (chamados) pertence ao condomínio? Via vínculo ativo do autor (estrito: órfão não aparece). */
function chamadoDoCondominio(PDO $pdo, int $idChamado, int $idCondominio): bool {
    $stmt = $pdo->prepare(
        "SELECT 1 FROM chamados c
         JOIN morador m ON m.idMorador = c.morador_idMorador
         JOIN morador m2 ON m2.idUsuario = m.idUsuario
         JOIN moradorunidade mu ON mu.Morador_idMorador = m2.idMorador AND mu.dataFim IS NULL
         JOIN unidade u ON u.idUnidade = mu.Unidade_idUnidade
         WHERE c.idChamados = :id AND u.Condominio_idCondominio = :c LIMIT 1"
    );
    $stmt->execute(['id' => $idChamado, 'c' => $idCondominio]);
    return (bool) $stmt->fetchColumn();
}

/** Morador pertence ao condomínio? (vínculo ativo em unidade do condomínio) */
function moradorDoCondominio(PDO $pdo, int $idMorador, int $idCondominio): bool {
    $stmt = $pdo->prepare(
        "SELECT 1 FROM morador m
         JOIN moradorunidade mu ON mu.Morador_idMorador = m.idMorador AND mu.dataFim IS NULL
         JOIN unidade u ON u.idUnidade = mu.Unidade_idUnidade
         WHERE m.idMorador = :m AND u.Condominio_idCondominio = :c LIMIT 1"
    );
    $stmt->execute(['m' => $idMorador, 'c' => $idCondominio]);
    return (bool) $stmt->fetchColumn();
}

/** Funcionário pertence ao condomínio? (vínculo ativo) */
function funcionarioDoCondominio(PDO $pdo, int $idFuncionario, int $idCondominio): bool {
    $stmt = $pdo->prepare(
        "SELECT 1 FROM funcionariocondominio
         WHERE Funcionario_idFuncionario = :f AND Condominio_idCondominio = :c
           AND dataDesligamento IS NULL LIMIT 1"
    );
    $stmt->execute(['f' => $idFuncionario, 'c' => $idCondominio]);
    return (bool) $stmt->fetchColumn();
}
