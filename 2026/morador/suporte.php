<?php
$menuAtivo = 'suporte';
$tituloPagina = 'Suporte';
include __DIR__ . '/_top.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'chamado') {
    $assunto = trim($_POST['assunto'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');
    $msg = ($assunto !== '' && $mensagem !== '')
        ? 'Chamado enviado. Nossa equipe responderá em breve.'
        : 'Preencha assunto e mensagem.';
}
?>
<section class="page-title">
    <h1>Suporte</h1>
    <p>Central de ajuda e atendimento</p>
</section>
<?php if ($msg !== ''): ?><div class="panel" style="margin-bottom:14px"><p class="sub"><?= htmlspecialchars($msg) ?></p></div><?php endif; ?>
<div class="contact-cards">
    <div class="contact">
        <div class="cico" style="background:#fff0e0;color:#d77a00"><i data-lucide="mail"></i></div>
        <div>
            <h3>E-mail</h3><small>suporte@edensystems.com.br</small></div>
    </div>
    <div class="contact">
        <div class="cico" style="background:#e5efdf"><i data-lucide="phone"></i></div>
        <div>
            <h3>Telefone</h3><small>(11) 4002-8922</small></div>
    </div>
    <div class="contact">
        <div class="cico" style="background:#e5f0f5;color:#4c7891"><i data-lucide="messages-square"></i></div>
        <div>
            <h3>Chat Online</h3><small>Seg–Sex, 9h–18h</small></div>
    </div>
</div>
<div class="panel faq">
    <h2 class="panel-title">Perguntas Frequentes</h2>
    <div class="faq-row open">
        <div class="faq-q">Como registrar uma nova ocorrência?<span>⌄</span></div>
        <div class="faq-answer">Acesse Ocorrências e clique em “+ Ocorrência”. Preencha título, categoria e descrição e finalize o registro.</div>
    </div>
    <div class="faq-row">
        <div class="faq-q">Como acompanhar o status da minha ocorrência?<span>›</span></div>
        <div class="faq-answer">Na tela de Ocorrências, o status aparece ao lado de cada solicitação.</div>
    </div>
    <div class="faq-row">
        <div class="faq-q">Como atualizar meus dados de contato?<span>›</span></div>
        <div class="faq-answer">Acesse Configurações e altere seus dados cadastrais.</div>
    </div>
</div>
<div class="panel support-form">
    <h2 class="panel-title" style="margin-bottom:20px">Abrir Chamado</h2>
    <form method="post">
        <input type="hidden" name="acao" value="chamado">
        <div class="two">
            <div class="field"><label>Tipo</label><select name="tipo"><option>Dúvida</option><option>Problema técnico</option></select></div>
            <div class="field"><label>Assunto *</label><input class="input" name="assunto" placeholder="Assunto do chamado" required></div>
        </div>
        <div class="field"><label>Mensagem *</label><textarea name="mensagem" placeholder="Descreva detalhadamente o que precisa de ajuda..." required></textarea></div>
        <div class="form-actions"><button type="submit" class="btn green">Enviar Chamado</button></div>
    </form>
</div>
<?php include __DIR__ . '/_bottom.php'; ?>
