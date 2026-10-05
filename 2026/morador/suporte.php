<?php
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_layout.php';

$msg = '';
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $tipo = trim($_POST['tipo'] ?? '');
        $assunto = trim($_POST['assunto'] ?? '');
        $mensagem = trim($_POST['mensagem'] ?? '');
        if (!in_array($tipo, ['Dúvida', 'Problema técnico'], true) || $assunto === '' || $mensagem === '') {
            throw new Exception('Preencha tipo, assunto e mensagem.');
        }
        $prioridade = (int) $conexao->query("SELECT idPrioridade FROM prioridade WHERE nome = 'Indefinida' LIMIT 1")->fetchColumn();
        if ($prioridade <= 0) throw new Exception('Não foi possível registrar. Tente novamente.');
        $stmt = $conexao->prepare(
            "INSERT INTO chamados (titulo, descricao, dataPedida, status, prioridade_idPrioridade, categoria_idCategoria, morador_idMorador, Condominio_idCondominio)
             VALUES (:t, :d, NOW(), 'analise', :p, 10, :m, :cond)"
        );
        $stmt->execute([
            't' => '[Suporte: ' . $tipo . '] ' . mb_substr($assunto, 0, 80),
            'd' => $mensagem, 'p' => $prioridade, 'm' => $idMorador, 'condominio' => $filtroCondominio,
        ]);
        $msg = 'Chamado enviado com sucesso. Acompanhe em Ocorrências.';
    } catch (PDOException $e) {
        $erro = 'Não foi possível enviar. Tente novamente.';
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

$rotuloTipo = ['proprietario' => 'Proprietário', 'inquilino' => 'Inquilino', 'dependente' => 'Dependente'];
$sub = ($rotuloTipo[$moradia['tipoMorador']] ?? 'Morador') . ' • Apto ' . $moradia['numResid'];

moradorHead('Suporte');
moradorSidebar('suporte');
moradorHeader($user['nome'] ?? 'Morador', $sub, 'Suporte');
moradorFlash($msg, $erro);
?>
            <section class="page-title">
                <h1>Suporte</h1>
                <p>Central de ajuda e atendimento</p>
            </section>
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
            <form method="post" class="panel support-form">
                <h2 class="panel-title" style="margin-bottom:20px">Abrir Chamado</h2>
                <div class="two">
                    <div class="field"><label>Tipo</label><select name="tipo" class="input"><option>Dúvida</option><option>Problema técnico</option></select></div>
                    <div class="field"><label>Assunto *</label><input class="input" name="assunto" placeholder="Assunto do chamado" required></div>
                </div>
                <div class="field"><label>Mensagem *</label><textarea name="mensagem" class="input" placeholder="Descreva detalhadamente o que precisa de ajuda..." required></textarea></div>
                <div class="form-actions"><button type="submit" class="btn green">Enviar Chamado</button></div>
            </form>
<?php moradorFoot(); ?>
