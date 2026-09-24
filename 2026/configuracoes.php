<?php
include './Elements/auth.php';
include './Elements/ui.php';
$msg = '';

// Condomínio da sessão
$cond = null;
try {
    $stmt = $conexao->prepare("SELECT * FROM condominio WHERE idCondominio = :c LIMIT 1");
    $stmt->execute(['c' => $filtroCondominio]);
    $cond = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (PDOException $e) {
    $cond = null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'salvar') {
    try {
        foreach (['tel_cond' => 'Telefone do condomínio', 'tel_sind' => 'Telefone do síndico'] as $campo => $rotulo) {
            $dig = preg_replace('/\D/', '', $_POST[$campo] ?? '');
            if ($dig !== '' && (strlen($dig) < 10 || strlen($dig) > 11)) {
                throw new Exception($rotulo . ' inválido! Use DDD + número.');
            }
        }
        if (!$cond || !$idCondominio) {
            throw new Exception('Usuário sem condomínio vinculado.');
        }
        if ($cond) {
            $stmt = $conexao->prepare(
                "UPDATE condominio SET nome = :nome, CNPJ = :cnpj, telefone = :tel, email = :email,
                        logradouro = :log WHERE idCondominio = :id"
            );
            $stmt->execute([
                'nome' => trim($_POST['nome_cond'] ?? $cond['nome']),
                'cnpj' => trim($_POST['cnpj'] ?? $cond['CNPJ']),
                'tel' => trim($_POST['tel_cond'] ?? ''),
                'email' => trim($_POST['email_cond'] ?? ''),
                'log' => trim($_POST['endereco'] ?? $cond['logradouro']),
                'id' => $cond['idCondominio'],
            ]);
            $cond = $conexao->query("SELECT * FROM condominio WHERE idCondominio = " . (int) $cond['idCondominio'])->fetch(PDO::FETCH_ASSOC);
        }
        $stmt = $conexao->prepare(
            "UPDATE usuario SET nome = :nome, telefone = :tel, email = :email WHERE idUsuario = :id"
        );
        $stmt->execute([
            'nome' => trim($_POST['nome_sind'] ?? ''),
            'tel' => trim($_POST['tel_sind'] ?? ''),
            'email' => trim($_POST['email_sind'] ?? ''),
            'id' => $idUsuario,
        ]);
        $msg = 'Alterações salvas com sucesso.';
    } catch (Exception $e) {
        $msg = $e instanceof PDOException
            ? 'Não foi possível salvar. Verifique os dados e tente novamente.'
            : $e->getMessage();
    }
}

$pageTitle = 'Configurações';
$menuAtivo = 'configuracoes';

// Totais calculados
$total_unidades = 0;
$total_blocos = 0;
try {
    $total_unidades = (int) $conexao->query("SELECT COUNT(*) FROM unidade")->fetchColumn();
    $total_blocos = (int) $conexao->query("SELECT COUNT(DISTINCT bloco) FROM unidade WHERE bloco IS NOT NULL AND bloco <> ''")->fetchColumn();
} catch (PDOException $e) {
    // mantém zero
}

$endereco = $cond
    ? trim($cond['logradouro'] . ', ' . $cond['numero'] . ' - ' . $cond['bairro'] . ', ' . $cond['cidade'] . '/' . $cond['UF'])
    : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<?php pageHead('Configurações - Eden Systems', ['./CSS/FrontDev.css', './CSS/tabelas.css'], ['https://unpkg.com/lucide@latest']); ?>
<body>
    <?php layoutOpen(); ?>
                <div class="fd-configuracoes">
                    <div class="page-intro">
                        <h1>Configurações</h1>
                        <p>Dados e preferências do condomínio</p>
                    </div>

                    <form id="configForm" method="post">
                        <input type="hidden" name="acao" value="salvar">

                        <section class="settings-card">
                            <div class="card-title">Dados do Condomínio</div>
                            <div class="card-content">
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="nomeCondominio">Nome do Condomínio</label>
                                        <input type="text" id="nomeCondominio" name="nome_cond" value="<?= htmlspecialchars($cond['nome'] ?? '') ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="cnpj">CNPJ</label>
                                        <input type="text" id="cnpj" name="cnpj" placeholder="00.000.000/0000-00" value="<?= htmlspecialchars($cond['CNPJ'] ?? '') ?>" inputmode="numeric" maxlength="18" autocomplete="off">
                                    </div>
                                    <div class="form-group full">
                                        <label for="endereco">Endereço</label>
                                        <input type="text" id="endereco" name="endereco" value="<?= htmlspecialchars($endereco) ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="telCond">Telefone</label>
                                        <input type="text" id="telCond" name="tel_cond" placeholder="(00) 00000-0000" maxlength="15" value="<?= htmlspecialchars($cond['telefone'] ?? '') ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="emailCond">E-mail</label>
                                        <input type="email" id="emailCond" name="email_cond" value="<?= htmlspecialchars($cond['email'] ?? '') ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="totalUnidades">Total de Unidades</label>
                                        <input type="number" id="totalUnidades" value="<?= $total_unidades ?>" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalBlocos">Total de Blocos</label>
                                        <input type="number" id="totalBlocos" value="<?= $total_blocos ?>" readonly>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="settings-card">
                            <div class="card-title">Dados do Síndico</div>
                            <div class="card-content">
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="nomeSindico">Nome</label>
                                        <input type="text" id="nomeSindico" name="nome_sind" value="<?= htmlspecialchars($user['nome'] ?? '') ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="telefoneSindico">Telefone</label>
                                        <input type="tel" id="telefoneSindico" name="tel_sind" placeholder="(00) 00000-0000" maxlength="15" value="<?= htmlspecialchars($user['telefone'] ?? '') ?>">
                                    </div>
                                    <div class="form-group full">
                                        <label for="emailSindico">E-mail</label>
                                        <input type="email" id="emailSindico" name="email_sind" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="settings-card">
                            <div class="card-title">Notificações</div>
                            <div class="card-content notifications-content">
                                <div class="notification-row">
                                    <div class="notification-description"><strong>Notificações por E-mail</strong><span>Receber alertas por e-mail</span></div>
                                    <label class="switch"><input type="checkbox" id="notificacaoEmail" checked><span class="slider"></span></label>
                                </div>
                                <div class="notification-row">
                                    <div class="notification-description"><strong>Notificações por WhatsApp</strong><span>Receber alertas por WhatsApp</span></div>
                                    <label class="switch"><input type="checkbox" id="notificacaoWhatsapp"><span class="slider"></span></label>
                                </div>
                                <div class="notification-row">
                                    <div class="notification-description"><strong>Alertas de Novas Ocorrências</strong><span>Notificar ao registrar nova ocorrência</span></div>
                                    <label class="switch"><input type="checkbox" id="alertaOcorrencias" checked><span class="slider"></span></label>
                                </div>
                                <div class="notification-row">
                                    <div class="notification-description"><strong>Relatório Mensal Automático</strong><span>Gerar e enviar relatório todo dia 1º</span></div>
                                    <label class="switch"><input type="checkbox" id="relatorioMensal" checked><span class="slider"></span></label>
                                </div>
                            </div>
                        </section>

                        <div class="form-actions">
                            <button type="button" id="btnCancelar" class="btn-cancel" onclick="history.back()">Cancelar</button>
                            <button type="submit" class="btn-save">Salvar Alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script src="<?= assetUrl('./js/app.js') ?>"></script>
    <script src="<?= assetUrl('./js/mascaras.js') ?>"></script>
    <?php if ($msg): ?>
    <script>document.addEventListener('DOMContentLoaded', () => fdToast(<?= json_encode($msg, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>));</script>
    <?php endif; ?>
</body>
</html>
