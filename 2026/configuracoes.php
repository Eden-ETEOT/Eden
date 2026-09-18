<?php
include './Elements/auth.php';
include './Elements/ui.php';
$msg = '';

// Condomínio de referência (último criado)
$cond = null;
try {
    $cond = $conexao->query("SELECT * FROM condominio ORDER BY idCondominio DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $cond = null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'salvar') {
    try {
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
    } catch (PDOException $e) {
        $msg = 'Não foi possível salvar. Verifique os dados e tente novamente.';
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
    <div class="dashboard-wrapper">
        <?php include './Elements/sidebar.php'; ?>

        <div class="main-content">
            <?php include './Elements/header.php'; ?>

            <div class="dashboard-content">
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
                                        <input type="text" id="cnpj" name="cnpj" value="<?= htmlspecialchars($cond['CNPJ'] ?? '') ?>">
                                    </div>
                                    <div class="form-group full">
                                        <label for="endereco">Endereço</label>
                                        <input type="text" id="endereco" name="endereco" value="<?= htmlspecialchars($endereco) ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="telCond">Telefone</label>
                                        <input type="text" id="telCond" name="tel_cond" value="<?= htmlspecialchars($cond['telefone'] ?? '') ?>">
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
                                        <input type="tel" id="telefoneSindico" name="tel_sind" value="<?= htmlspecialchars($user['telefone'] ?? '') ?>">
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

    <div id="fdToast" class="fd-toast" role="status"></div>

    <script src="./js/app.js"></script>
    <?php if ($msg): ?>
    <script>document.addEventListener('DOMContentLoaded', () => fdToast(<?= json_encode($msg, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>));</script>
    <?php endif; ?>
</body>
</html>
