<?php
// Helpers centrais do fluxo de convites (ver explicacao-sistema-convites.md).
// Exige $conexao (PDO) já disponível. Não inicia sessão nem redireciona.

const CONVITE_TIPOS = ['proprietario', 'inquilino', 'dependente'];
const CONVITE_VALIDADE_DIAS = 7;

/** Condomínio de uma unidade (ou null se inexistente). */
function conviteCondominio(PDO $pdo, int $idUnidade): ?int {
    $stmt = $pdo->prepare("SELECT Condominio_idCondominio FROM unidade WHERE idUnidade = :u");
    $stmt->execute(['u' => $idUnidade]);
    $id = $stmt->fetchColumn();
    return $id === false ? null : (int) $id;
}

/** É síndico do condomínio? (tabela sindico) */
function eSindico(PDO $pdo, int $idUsuario, int $idCondominio): bool {
    $stmt = $pdo->prepare(
        "SELECT 1 FROM sindico WHERE idUsuario = :u AND Condominio_idCondominio = :c LIMIT 1"
    );
    $stmt->execute(['u' => $idUsuario, 'c' => $idCondominio]);
    return (bool) $stmt->fetchColumn();
}

/** Unidade tem proprietário com vínculo ativo? */
function unidadeTemProprietario(PDO $pdo, int $idUnidade): bool {
    $stmt = $pdo->prepare(
        "SELECT 1 FROM moradorunidade mu
         JOIN morador m ON m.idMorador = mu.Morador_idMorador
         WHERE mu.Unidade_idUnidade = :u AND mu.dataFim IS NULL
           AND m.tipoMorador = 'proprietario' LIMIT 1"
    );
    $stmt->execute(['u' => $idUnidade]);
    return (bool) $stmt->fetchColumn();
}

/** Usuário é proprietário (vínculo ativo) desta unidade? */
function eProprietarioUnidade(PDO $pdo, int $idUsuario, int $idUnidade): bool {
    $stmt = $pdo->prepare(
        "SELECT 1 FROM morador m
         JOIN moradorunidade mu ON mu.Morador_idMorador = m.idMorador AND mu.dataFim IS NULL
         WHERE m.idUsuario = :u AND mu.Unidade_idUnidade = :un
           AND m.tipoMorador = 'proprietario' LIMIT 1"
    );
    $stmt->execute(['u' => $idUsuario, 'un' => $idUnidade]);
    return (bool) $stmt->fetchColumn();
}

/** É administrador do sistema? (funcionario.funcao = 'Administrador') Passa livre. */
function eAdmin(PDO $pdo, int $idUsuario): bool {
    $stmt = $pdo->prepare(
        "SELECT 1 FROM funcionario WHERE idUsuario = :u AND LOWER(funcao) = 'administrador' LIMIT 1"
    );
    $stmt->execute(['u' => $idUsuario]);
    return (bool) $stmt->fetchColumn();
}

/** Usuário tem algum vínculo ativo (qualquer unidade)? Retorna a unidade ou null. */
function vinculoAtivo(PDO $pdo, int $idUsuario): ?array {
    $stmt = $pdo->prepare(
        "SELECT mu.Unidade_idUnidade AS unidade, m.idMorador, m.tipoMorador
         FROM morador m
         JOIN moradorunidade mu ON mu.Morador_idMorador = m.idMorador AND mu.dataFim IS NULL
         WHERE m.idUsuario = :u LIMIT 1"
    );
    $stmt->execute(['u' => $idUsuario]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

/**
 * Pode gerar convite para a unidade?
 * Síndico (só se a unidade ainda não tem proprietário) ou proprietário da unidade.
 */
function podeConvidar(PDO $pdo, int $idUsuario, int $idUnidade): bool {
    if (eAdmin($pdo, $idUsuario)) return true;
    $idCondominio = conviteCondominio($pdo, $idUnidade);
    if ($idCondominio === null) return false;
    if (eProprietarioUnidade($pdo, $idUsuario, $idUnidade)) return true;
    if (eSindico($pdo, $idUsuario, $idCondominio) && !unidadeTemProprietario($pdo, $idUnidade)) return true;
    return false;
}

/** Convite guardado na sessão (fluxo cadastro), já validado. Retorna dados ou null. */
function conviteDaSessao(PDO $pdo): ?array {
    $token = trim($_SESSION['convite_token'] ?? '');
    if ($token === '') return null;
    [$ok, $dados] = validarConvite($pdo, $token);
    return $ok ? $dados : null;
}

/** Valida estado do convite. Retorna [ok, dados|erro]. */
function validarConvite(PDO $pdo, string $token): array {
    $stmt = $pdo->prepare(
        "SELECT c.*, u.numResid, u.bloco, u.Condominio_idCondominio AS condominio,
                co.nome AS condominioNome
         FROM convite c
         JOIN unidade u ON u.idUnidade = c.Unidade_idUnidade
         JOIN condominio co ON co.idCondominio = u.Condominio_idCondominio
         WHERE c.token = :t LIMIT 1"
    );
    $stmt->execute(['t' => $token]);
    $c = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$c) return [false, 'Convite não encontrado.'];
    if ($c['status'] === 'usado') return [false, 'Este convite já foi utilizado.'];
    if ($c['status'] === 'cancelado') return [false, 'Este convite foi cancelado.'];
    if (strtotime($c['dataExpiracao']) < time()) return [false, 'Este convite expirou.'];
    return [true, $c];
}

/** Gera convite. Retorna [ok, token|erro]. */
function gerarConvite(PDO $pdo, int $criadoPor, int $idUnidade, string $tipo,
                      ?string $emailEsperado = null, int $diasValidade = CONVITE_VALIDADE_DIAS): array {
    if (!in_array($tipo, CONVITE_TIPOS, true)) return [false, 'Tipo de morador inválido.'];
    $stmt = $pdo->prepare("SELECT 1 FROM unidade WHERE idUnidade = :u AND ativo = 1");
    $stmt->execute(['u' => $idUnidade]);
    if (!$stmt->fetchColumn()) return [false, 'Apartamento inválido ou inativo.'];
    if (!podeConvidar($pdo, $criadoPor, $idUnidade)) {
        return [false, 'Você não tem permissão para convidar para esta unidade.'];
    }
    $emailEsperado = ($emailEsperado !== null && trim($emailEsperado) !== '') ? trim($emailEsperado) : null;
    if ($emailEsperado !== null && !filter_var($emailEsperado, FILTER_VALIDATE_EMAIL)) {
        return [false, 'E-mail esperado inválido.'];
    }
    if ($diasValidade < 1 || $diasValidade > 90) $diasValidade = CONVITE_VALIDADE_DIAS;
    $token = bin2hex(random_bytes(16));
    $stmt = $pdo->prepare(
        "INSERT INTO convite (token, Unidade_idUnidade, tipoMorador, emailEsperado, criadoPor, dataExpiracao)
         VALUES (:t, :u, :tipo, :email, :c, DATE_ADD(NOW(), INTERVAL :d DAY))"
    );
    $stmt->execute(['t' => $token, 'u' => $idUnidade, 'tipo' => $tipo,
                    'email' => $emailEsperado, 'c' => $criadoPor, 'd' => $diasValidade]);
    return [true, $token];
}

/**
 * Aceita convite: valida com trava de linha, confere e-mail, grava vínculo.
 * Retorna [ok, mensagem].
 */
function aceitarConvite(PDO $pdo, string $token, int $idUsuario, string $dataNascimento): array {
    if (trim($dataNascimento) === '') return [false, 'Informe a data de nascimento.'];
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT * FROM convite WHERE token = :t LIMIT 1 FOR UPDATE");
        $stmt->execute(['t' => $token]);
        $c = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$c) { $pdo->rollBack(); return [false, 'Convite não encontrado.']; }
        if ($c['status'] !== 'pendente') {
            $pdo->rollBack();
            return [false, $c['status'] === 'usado' ? 'Este convite já foi utilizado.' : 'Este convite foi cancelado.'];
        }
        if (strtotime($c['dataExpiracao']) < time()) { $pdo->rollBack(); return [false, 'Este convite expirou.']; }

        $stmt = $pdo->prepare("SELECT email FROM usuario WHERE idUsuario = :u");
        $stmt->execute(['u' => $idUsuario]);
        $emailAtual = $stmt->fetchColumn();
        if ($c['emailEsperado'] !== null && strcasecmp($c['emailEsperado'], (string) $emailAtual) !== 0) {
            $pdo->rollBack();
            return [false, 'Este convite foi emitido para outro e-mail.'];
        }
        if (vinculoAtivo($pdo, $idUsuario) !== null) {
            $pdo->rollBack();
            return [false, 'Você já está vinculado a uma unidade. Fale com o síndico para transferência.'];
        }
        $stmt = $pdo->prepare("SELECT idMorador FROM morador WHERE idUsuario = :u LIMIT 1");
        $stmt->execute(['u' => $idUsuario]);
        $idMorador = $stmt->fetchColumn();
        if ($idMorador === false) {
            $stmt = $pdo->prepare(
                "INSERT INTO morador (idUsuario, tipoMorador, dataNascimento) VALUES (:u, :t, :n)"
            );
            $stmt->execute(['u' => $idUsuario, 't' => $c['tipoMorador'], 'n' => $dataNascimento]);
            $idMorador = (int) $pdo->lastInsertId();
        } else {
            $stmt = $pdo->prepare(
                "UPDATE morador SET tipoMorador = :t, dataNascimento = :n WHERE idMorador = :m"
            );
            $stmt->execute(['t' => $c['tipoMorador'], 'n' => $dataNascimento, 'm' => $idMorador]);
        }
        $stmt = $pdo->prepare(
            "INSERT INTO moradorunidade (Morador_idMorador, Unidade_idUnidade, dataInicio, dataFim)
             VALUES (:m, :u, CURDATE(), NULL)"
        );
        $stmt->execute(['m' => $idMorador, 'u' => $c['Unidade_idUnidade']]);
        $stmt = $pdo->prepare(
            "UPDATE convite SET status = 'usado', dataUso = NOW() WHERE idConvite = :id"
        );
        $stmt->execute(['id' => $c['idConvite']]);
        $pdo->commit();
        return [true, 'Vínculo criado com sucesso. Bem-vindo(a)!'];
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return [false, 'Erro no banco de dados.'];
    }
}

/** Cancela convite pendente. Só o criador ou síndico do condomínio. */
function cancelarConvite(PDO $pdo, int $idConvite, int $idUsuario): array {
    $stmt = $pdo->prepare(
        "SELECT c.*, u.Condominio_idCondominio AS condominio FROM convite c
         JOIN unidade u ON u.idUnidade = c.Unidade_idUnidade WHERE c.idConvite = :id LIMIT 1"
    );
    $stmt->execute(['id' => $idConvite]);
    $c = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$c) return [false, 'Convite não encontrado.'];
    if ($c['status'] !== 'pendente') return [false, 'Só é possível cancelar convites pendentes.'];
    if ((int) $c['criadoPor'] !== $idUsuario && !eAdmin($pdo, $idUsuario) && !eSindico($pdo, $idUsuario, (int) $c['condominio'])) {
        return [false, 'Sem permissão para cancelar este convite.'];
    }
    $stmt = $pdo->prepare("UPDATE convite SET status = 'cancelado' WHERE idConvite = :id");
    $stmt->execute(['id' => $idConvite]);
    return [true, 'Convite cancelado.'];
}

/** Convites pendentes criados pelo usuário ou do seu condomínio (para síndico). */
function convitesPendentes(PDO $pdo, int $idUsuario, int $idCondominio): array {
    $eSind = eSindico($pdo, $idUsuario, $idCondominio);
    $sql = "SELECT c.idConvite, c.token, c.tipoMorador, c.emailEsperado, c.dataExpiracao,
                   u.numResid, u.bloco, us.nome AS criador
            FROM convite c
            JOIN unidade u ON u.idUnidade = c.Unidade_idUnidade
            JOIN usuario us ON us.idUsuario = c.criadoPor
            WHERE c.status = 'pendente' AND u.Condominio_idCondominio = :c";
    $params = ['c' => $idCondominio];
    if (!$eSind) {
        $sql .= " AND c.criadoPor = :u";
        $params['u'] = $idUsuario;
    }
    $sql .= " ORDER BY c.dataCriacao DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
