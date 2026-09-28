<?php
require_once __DIR__ . '/_guard.php';

$pageTitle = 'Suporte';
$menuAtivo = 'suporte';
$msg = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'chamado') {
    try {
        $tipo = trim($_POST['tipo'] ?? '');
        $assunto = trim($_POST['assunto'] ?? '');
        $mensagem = trim($_POST['mensagem'] ?? '');
        if ($tipo === '' || $assunto === '' || $mensagem === '') {
            throw new Exception('Preencha todos os campos obrigatórios.');
        }
        $stmt = $conexao->prepare(
            "INSERT INTO suporte_chamado (idUsuario, tipo, assunto, mensagem) VALUES (:u, :t, :a, :m)"
        );
        $stmt->execute(['u' => $idUsuario, 't' => $tipo, 'a' => $assunto, 'm' => $mensagem]);
        $msg = 'Chamado enviado. Nossa equipe responderá em breve.';
    } catch (PDOException $e) {
        $erro = 'Erro no banco de dados.';
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<?php pageHead('Suporte - Eden Systems', ['../CSS/tabelas.css', '../CSS/morador.css'], ['https://unpkg.com/lucide@latest'], '..'); ?>
<body>
<div class="dashboard-wrapper">
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="main-content">
<?php include __DIR__ . '/header.php'; ?>
<div class="dashboard-content">
                <h1 class="page-title">Suporte</h1>
                <?php banner($msg, $erro); ?>

                <div class="mor-contact-grid">
                    <div class="mor-contact-card">
                        <div class="mor-contact-icon" style="background:#fff0e6;color:#d77a00"><i data-lucide="mail"></i></div>
                        <div><strong>E-mail</strong><small>suporte@edensystems.com.br</small></div>
                    </div>
                    <div class="mor-contact-card">
                        <div class="mor-contact-icon" style="background:#eef5f2;color:#28533f"><i data-lucide="phone"></i></div>
                        <div><strong>Telefone</strong><small>(11) 4002-8922</small></div>
                    </div>
                    <div class="mor-contact-card">
                        <div class="mor-contact-icon" style="background:#eef4f8;color:#4c90b2"><i data-lucide="messages-square"></i></div>
                        <div><strong>Chat Online</strong><small>Seg–Sex, 9h–18h</small></div>
                    </div>
                </div>

                <div class="mor-panel">
                    <h2 class="mor-panel-title">Perguntas Frequentes</h2>
                    <div class="mor-faq-item open">
                        <button type="button" class="mor-faq-q">Como registrar uma nova ocorrência?<i data-lucide="chevron-down"></i></button>
                        <div class="mor-faq-a">Acesse Ocorrências e clique em + Ocorrência. Preencha título, categoria e descrição e finalize o registro.</div>
                    </div>
                    <div class="mor-faq-item">
                        <button type="button" class="mor-faq-q">Como acompanhar o status da minha ocorrência?<i data-lucide="chevron-down"></i></button>
                        <div class="mor-faq-a">Na tela de Ocorrências, o status aparece ao lado de cada solicitação.</div>
                    </div>
                    <div class="mor-faq-item">
                        <button type="button" class="mor-faq-q">Como atualizar meus dados de contato?<i data-lucide="chevron-down"></i></button>
                        <div class="mor-faq-a">Acesse Configurações e altere seus dados cadastrais.</div>
                    </div>
                </div>

                <div class="mor-panel">
                    <h2 class="mor-panel-title" style="margin-bottom:16px">Abrir Chamado</h2>
                    <form method="post">
                        <input type="hidden" name="acao" value="chamado">
                        <div class="mor-form-row">
                            <div class="mor-field">
                                <label>Tipo</label>
                                <select name="tipo" required>
                                    <option value="Dúvida">Dúvida</option>
                                    <option value="Problema técnico">Problema técnico</option>
                                </select>
                            </div>
                            <div class="mor-field">
                                <label>Assunto *</label>
                                <input type="text" name="assunto" placeholder="Assunto do chamado" required>
                            </div>
                        </div>
                        <div class="mor-field">
                            <label>Mensagem *</label>
                            <textarea name="mensagem" placeholder="Descreva detalhadamente o que precisa de ajuda..." required></textarea>
                        </div>
                        <div class="mor-form-actions" style="grid-template-columns:1fr">
                            <button type="submit" class="btn btn-green">Enviar Chamado</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="<?= assetUrl('../js/app.js') ?>"></script>
    <script>
        if (window.lucide) lucide.createIcons();
        document.querySelectorAll('.mor-faq-q').forEach(btn => btn.addEventListener('click', () => {
            const item = btn.closest('.mor-faq-item');
            const aberto = item.classList.contains('open');
            document.querySelectorAll('.mor-faq-item.open').forEach(o => o.classList.remove('open'));
            if (!aberto) item.classList.add('open');
        }));
    </script>
</body>
</html>
