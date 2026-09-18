<?php
include './Elements/auth.php';
include './Elements/ui.php';

$pageTitle = 'Suporte';
$menuAtivo = 'suporte';

$faqs = [
    ['Como registrar uma nova ocorrência?', 'Acesse a opção “Ocorrências” no menu lateral e clique no botão “+ Ocorrência”. Preencha os dados solicitados e clique em “Registrar Ocorrência”.'],
    ['Como alterar o status de uma ocorrência?', 'Na tela de Ocorrências, clique no ícone de visualização da ocorrência desejada. No modal de detalhes, selecione o novo status.'],
    ['Como exportar um relatório?', 'Acesse “Relatórios” no menu lateral, escolha o relatório desejado e utilize a opção de exportação disponível na página.'],
    ['Como cadastrar um novo morador?', 'Acesse “Moradores”, clique em “Novo Morador”, informe os dados solicitados e confirme o cadastro.'],
    ['Como adicionar documentos ao sistema?', 'Acesse “Documentação”, clique em “Novo Documento”, preencha as informações, anexe o arquivo e registre o documento.'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<?php pageHead('Suporte - Eden Systems', ['./CSS/FrontDev.css', './CSS/tabelas.css'], ['https://unpkg.com/lucide@latest']); ?>
<body>
    <div class="dashboard-wrapper">
        <?php include './Elements/sidebar.php'; ?>

        <div class="main-content">
            <?php include './Elements/header.php'; ?>

            <div class="dashboard-content">
                <div class="fd-suporte">
                    <section class="page-title">
                        <h1>Suporte</h1>
                        <p>Central de ajuda e atendimento</p>
                    </section>

                    <section class="contact-grid">
                        <article class="contact-card">
                            <div class="contact-icon email-icon"><i data-lucide="mail"></i></div>
                            <div class="contact-info"><strong>E-mail</strong><span>suporte@edensystems.com.br</span></div>
                        </article>
                        <article class="contact-card">
                            <div class="contact-icon phone-icon"><i data-lucide="phone"></i></div>
                            <div class="contact-info"><strong>Telefone</strong><span>(11) 4002-8922</span></div>
                        </article>
                        <article class="contact-card">
                            <div class="contact-icon chat-icon"><i data-lucide="message-square-text"></i></div>
                            <div class="contact-info"><strong>Chat Online</strong><span>Seg–Sex, 9h–18h</span></div>
                        </article>
                    </section>

                    <section class="support-card faq-card">
                        <div class="card-header">
                            <h2>Perguntas Frequentes</h2>
                        </div>
                        <div class="faq-list">
                            <?php foreach ($faqs as $faq): ?>
                            <div class="faq-item">
                                <button class="faq-question" type="button">
                                    <span><?= htmlspecialchars($faq[0]) ?></span>
                                    <i data-lucide="chevron-down"></i>
                                </button>
                                <div class="faq-answer"><p><?= htmlspecialchars($faq[1]) ?></p></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section class="support-card ticket-card">
                        <div class="ticket-header">
                            <h2>Abrir Chamado</h2>
                            <p>Nossa equipe responde em até 24 horas úteis</p>
                        </div>
                        <form id="supportForm" class="support-form" onsubmit="return fdEnviarChamado(event)">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="ticketType">Tipo</label>
                                    <select id="ticketType" required>
                                        <option value="Dúvida">Dúvida</option>
                                        <option value="Problema">Problema</option>
                                        <option value="Sugestão">Sugestão</option>
                                        <option value="Solicitação">Solicitação</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="ticketSubject">Assunto *</label>
                                    <input id="ticketSubject" type="text" placeholder="Assunto do chamado" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="ticketMessage">Mensagem *</label>
                                <textarea id="ticketMessage" placeholder="Descreva detalhadamente o que precisa de ajuda..." required></textarea>
                            </div>
                            <div class="form-footer">
                                <button type="submit" class="send-button">
                                    <i data-lucide="send"></i>
                                    Enviar Chamado
                                </button>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Chamado Enviado -->
    <div class="fd-suporte">
        <div class="modal-overlay" id="successModal" onclick="fdFecharClicandoFora(event, 'successModal')">
            <div class="success-modal">
                <button class="modal-close" onclick="fdFecharModal('successModal')" type="button"><i data-lucide="x"></i></button>
                <div class="success-icon"><i data-lucide="check"></i></div>
                <h2>Chamado enviado!</h2>
                <p>Seu chamado foi registrado com sucesso. Nossa equipe responderá em até 24 horas úteis.</p>
                <button type="button" class="success-button" onclick="fdFecharModal('successModal')">Entendi</button>
            </div>
        </div>
    </div>

    <script src="./js/FrontDev.js"></script>
</body>
</html>
