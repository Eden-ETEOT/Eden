<?php 
session_start();
require_once "../config/conexao.php";
?>


<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing Page - test</title>
    <link rel="stylesheet" href="../CSS/LandingPage.css">
    <link rel="stylesheet" href="../CSS/variaveis.css">

</head>

<body>

    <main>

        <header class="barra-navegacao">
            <figure>
                <div>
                    <img src="../assets/PNG/original.png" alt="logo éden" class="logotipo-header">
                </div>
            </figure>

            <nav class="menu-principal">
                <a href="#" class="ativo">Home</a>
                <a href="#">Planos</a>
                <a href="#">Serviços</a>
                <a href="#">Suporte</a>
            </nav>

            <a href="../Login/loginMockup/index.php"><button class="botao-primario">Entrar</button></a>
        </header>

        <!-- Início seção HERO -->


        <section class="secao-hero">
            <div class="conteudo-hero">

                <h1 class="titulo-hero">Transforme sua gestão <br>
                    com tecnologia</h1>

                <p>
                    Temos a solução perfeita para gerenciamento condominial, pensado visando máxima eficiência
                </p>

                <button class="botao-secundario">
                    Assine já!
                </button>
            </div>

            <div class="modelos-dispositivos">
                <div class="desktop"></div>
                <div class="tablet"></div>
                <div class="celular"></div>
            </div>
        </section>

        <!-- Fim seção HERO -->

        <!-- Inicio seção sobre -->

        <section class="secao-sobre">


            <div class="container-sobre">

                <div class="texto-sobre">
                    <h2>
                        Controle total das ocorrências <br>
                        do seu condomínio
                    </h2>

                    <p class="descricao-sobre">
                        Registre, acompanhe e resolva problemas com organização e transparência. Centralize tudo em um
                        único sistema com histórico completo e comunicação em tempo real.
                    </p>

                    <button class="botao-primario-sc-sobre">
                        Saiba mais
                    </button>
                </div>

                <div class="imagem-sobre">

                </div>



            </div>
        </section>

        <!-- Fim seção sobre -->

        <!-- Início seção diferenciais -->

        <section class="secao-recursos">

            <div class="cabecalho-recursos">
                <h1 class="titulo-recursos">Nossos diferenciais</h1>
                <br><!-- remover -->
                <p class="desc-recursos">Tudo que você precisa para gerenciar ocorrências condominiais com eficiência
                </p>
            </div>

            <div class="grade-recursos">

                <div class="cartao-recurso">
                    <div class="icone"> <img src="../assets/icones/diagrama.png" alt=""></div>
                    <h3>Registro estruturado</h3>
                    <p>
                        Registre ocorrências com tipo, descrição, local e imagens, evitando perda de informação.
                    </p>
                    <button class="botao-primario btn-sobre"> Saiba mais</button>
                </div>

                <div class="cartao-recurso">
                    <div class="icone"> <img src="../assets/icones/lista-check.png" alt=""></div>
                    <h3>Histórico de ocorrências</h3>
                    <p>
                        Monitore o status e o histórico das ocorrências para identificar recorrências.
                    </p>
                    <button class="botao-primario btn-sobre"> Saiba mais</button>
                </div>

                <div class="cartao-recurso">
                    <div class="icone"> <img src="../assets/icones/chat.png" alt=""></div>
                    <h3>Comunicação integrada</h3>
                    <p>
                        Converse diretamente dentro de cada chamado, sem depender de e-mails ou aplicativos externos.
                    </p>
                    <button class="botao-primario btn-sobre"> Saiba mais</button>
                </div>

            </div>
        </section>

        <!-- Fim seção diferenciais -->

        <!-- Início seção estatísticas -->

        <section class="secao-estatisticas">

            <div class="cabecalho-estatisticas">
                <h1>Gestão Inteligente de Ocorrências <br>
                    Condominiais</h1>
                <br><!-- remover -->
                <p>
                    Controle, registre e acompanhe todas as ocorrências do condomínio em tempo real, garantindo <br>
                    mais organização, transparência e segurança para moradores e síndico.
                </p>
            </div>

            <div class="caixa-estatisticas">

                <div class="metrica">
                    <h3>Ocorrências registradas</h3>
                    <span>13 mil</span>
                </div>

                <div class="divisor-vertical"></div>

                <div class="metrica">
                    <h3>Taxa de resolução de chamados</h3>
                    <span>94%</span>
                </div>

                <div class="divisor-vertical"></div>

                <div class="metrica">
                    <h3>Índice de satisfação</h3>
                    <span>4.9/5</span>
                </div>

            </div>
        </section>

        <!-- Fim seção estatísticas -->

        <!-- Início seção planos -->

        <section class="secao-precos">
            <div class="cabecalho-secao-precos">
                <h1 class="titulo-principal-precos">Escolha o plano ideal <br> para o seu condomínio</h1>
                <br><!-- remover -->
                <p class="descricao-secao-precos">
                    Gerencie ocorrências, comunicação e histórico em um só lugar.
                </p>
            </div>

            <div class="grade-precos">
                <article class="cartao-preco">
                    <div class="cabecalho-cartao">
                        <h3 class="nome-plano">Básico</h3>
                        <div class="valor">R$167,90</div>
                        <p class="periodo">por mês</p>
                        <p class="slogan">Ideal para condomínios pequenos que querem organizar o registro de
                            ocorrências.</p>
                    </div>

                    <hr class="divisor-horizontal">

                    <div class="conteudo-cartao">
                        <ul class="lista-recursos">
                            <li class="item-recurso">
                                <span class="icone-check">✓</span>
                                <p>Até 30 unidades habitacionais</p>
                            </li>
                            <li class="item-recurso">
                                <span class="icone-check">✓</span>
                                <p>Registro de ocorrências</p>
                            </li>
                            <li class="item-recurso">
                                <span class="icone-check">✓</span>
                                <p>Acompanhamento de status</p>
                            </li>
                            <li class="item-recurso">
                                <span class="icone-check">✓</span>
                                <p>Histórico dos últimos 3 meses</p>
                            </li>
                            <li class="item-recurso">
                                <span class="icone-check">✓</span>
                                <p>1 administrador / síndico</p>
                            </li>
                            <li class="item-recurso">
                                <span class="icone-check">✓</span>
                                <p>Suporte por e-mail</p>
                            </li>
                        </ul>
                    </div>

                    <hr class="divisor-horizontal">

                    <div class="rodape-cartao">
                        <button class="botao-secundario">
                            Saiba mais
                        </button>
                    </div>
                </article>

                <article class="cartao-preco">
                    <div class="cabecalho-cartao">
                        <h3 class="nome-plano">Profissional</h3>
                        <div class="valor">R$267,90</div>
                        <p class="periodo">por mês</p>
                        <p class="slogan">Para condomínios que precisam de comunicação ágil e controle completo das
                            demandas.</p>
                    </div>

                    <hr class="divisor-horizontal">

                    <div class="conteudo-cartao">
                        <ul class="lista-recursos">
                            <li class="item-recurso">
                                <span class="icone-check">✓</span>
                                <p class="descricao-recurso">Até 100 unidades habitacionais</p>
                            </li>
                            <li class="item-recurso">
                                <span class="icone-check">✓</span>
                                <p class="descricao-recurso">Registro de ocorrência e anexo de imagens</p>
                            </li>
                            <li class="item-recurso">
                                <span class="icone-check">✓</span>
                                <p class="descricao-recurso">Chat por ocorrência</p>
                            </li>
                            <li class="item-recurso">
                                <span class="icone-check">✓</span>
                                <p class="descricao-recurso">Histórico completo e relatórios mensais</p>
                            </li>
                            <li class="item-recurso">
                                <span class="icone-check">✓</span>
                                <p class="descricao-recurso">Até 3 funcionários / administradores</p>
                            </li>
                            <li class="item-recurso">
                                <span class="icone-check">✓</span>
                                <p class="descricao-recurso">Notificações por e-mail em tempo real</p>
                            </li>
                        </ul>
                    </div>

                    <hr class="divisor-horizontal">

                    <div class="rodape-cartao">
                        <button class="botao-secundario">
                            Saiba mais
                        </button>
                    </div>
                </article>

                <article class="cartao-preco">
                    <div class="cabecalho-cartao">
                        <h3 class="nome-plano">Empresarial</h3>
                        <div class="valor">R$367,90</div>
                        <p class="periodo">por mês</p>
                        <p class="slogan">Solução completa para administradoras e condomínios de grande porte.</p>
                    </div>

                    <hr class="divisor-horizontal">

                    <div class="conteudo-cartao">
                        <ul class="lista-recursos">
                            <li class="item-recurso">
                                <span class="icone-check">✓</span>
                                <p class="descricao-recurso">Unidades ilimitadas</p>
                            </li>
                            <li class="item-recurso">
                                <span class="icone-check">✓</span>
                                <p class="descricao-recurso">Todos os recursos do Profissional</p>
                            </li>
                            <li class="item-recurso">
                                <span class="icone-check">✓</span>
                                <p class="descricao-recurso">Funcionários e administradores ilimitados</p>
                            </li>
                            <li class="item-recurso">
                                <span class="icone-check">✓</span>
                                <p class="descricao-recurso">Dashboard analítico de ocorrências</p>
                            </li>
                            <li class="item-recurso">
                                <span class="icone-check">✓</span>
                                <p class="descricao-recurso">Gráficos e análises detalhadas</p>
                            </li>
                            <li class="item-recurso">
                                <span class="icone-check">✓</span>
                                <p class="descricao-recurso">Exportação de relatórios em PDF</p>
                            </li>

                        </ul>
                    </div>

                    <hr class="divisor-horizontal">

                    <div class="rodape-cartao">
                        <button class="botao-secundario">
                            Saiba mais
                        </button>
                    </div>
                </article>
            </div>
        </section>

        <!-- Fim seção planos -->

        <!-- Início seção faq -->

        <section class="container-faq">
            <div class="cabecalho-faq">
                <h1 class="titulo-faq">FAQ - Perguntas frequentes</h1>
                <hr class="linha-faq">
                <p class=" subtitulo-faq">Entenda como o Éden transforma a gestão de conflitos e a manutenção do seu
                    condomínio em um processo digital e transparente.</p>
            </div>

            <div class="lista-faq">

                <div class="item-faq ativo">
                    <button class="pergunta-faq">
                        <span>Como o sistema centraliza as reclamações e pedidos?</span>
                        <div class="caixa-icone">
                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path class="seta-faq" d="M4.5 2.5L8 6L4.5 9.5" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                    </button>
                    <div class="envoltura-resposta-faq">
                        <div class="resposta-faq">
                            <p>O Éden substitui grupos de WhatsApp e registros informais por um portal estruturado. Cada
                                morador abre seu próprio chamado (ticket), anexa fotos e descreve o problema.</p>
                        </div>
                    </div>
                </div>

                <div class="item-faq">
                    <button class="pergunta-faq">
                        <span>É possível acompanhar o progresso de uma solicitação em tempo real?</span>
                        <div class="caixa-icone">
                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path class="seta-faq" d="M4.5 2.5L8 6L4.5 9.5" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                    </button>
                    <div class="envoltura-resposta-faq">
                        <div class="resposta-faq">
                            <p>Sim, o sistema permite que os moradores acompanhem o progresso de suas solicitações em
                                tempo real, com atualizações automáticas e notificações.</p>
                        </div>
                    </div>
                </div>

                <div class="item-faq">
                    <button class="pergunta-faq">
                        <span>Como funciona a comunicação direta entre morador e administração?</span>
                        <div class="caixa-icone">
                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path class="seta-faq" d="M4.5 2.5L8 6L4.5 9.5" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                    </button>
                    <div class="envoltura-resposta-faq">
                        <div class="resposta-faq">
                            <p>O sistema permite a comunicação direta entre moradores e a administração por meio de um
                                canal de mensagens integrado, onde todas as interações são registradas e acessíveis para
                                ambas as partes.</p>
                        </div>
                    </div>
                </div>

                <div class="item-faq">
                    <button class="pergunta-faq">
                        <span>O sistema gera relatórios para as assembleias e prestação de contas?</span>
                        <div class="caixa-icone">
                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path class="seta-faq" d="M4.5 2.5L8 6L4.5 9.5" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                    </button>
                    <div class="envoltura-resposta-faq">
                        <div class="resposta-faq">
                            <p>Sim, o sistema gera relatórios detalhados que podem ser utilizados nas assembleias e para
                                a prestação de contas, proporcionando transparência e rastreabilidade das solicitações.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- Fim seção faq -->


    </main>

    <!-- Início rodapé -->

    <footer class="rodape-site">
        <div class="container-rodape">

            <div class="rodape-topo">
                <div class="coluna-rodape coluna-marca">
                    <figure>
                        <img src="../assets/PNG/logobranca-laranja.png" alt="" class="logotipo-rodape">
                </div>

                <div class="coluna-rodape coluna-links">
                    <h4 class="titulo-rodape">Links rápidos</h4>
                    <ul class="links-rodape">
                        <li><a href="#">Início</a></li>
                        <li><a href="#">Sobre nós</a></li>
                        <li><a href="#">Funcionalidades</a></li>
                        <li><a href="#">Planos</a></li>
                        <li><a href="#">Contato</a></li>
                    </ul>
                </div>

                <div class="coluna-rodape coluna-links">
                    <h4 class="titulo-rodape">Recursos</h4>
                    <ul class="links-rodape">
                        <li><a href="#">Abrir chamado</a></li>
                        <li><a href="#">Painel do síndico</a></li>
                        <li><a href="#">Área do morador</a></li>
                        <li><a href="#">Suporte</a></li>
                    </ul>
                </div>

                <div class="coluna-rodape coluna-social">
                    <h4 class="titulo-rodape">Informações</h4>
                    <p class="texto-social">Rio de Janeiro - RJ <br> contato@edensystems.com <br> (21) 98765-4321</p>

                    <div class="icones-sociais">
                        <a href="#" class="icone-social" aria-label="Instagram">
                            <img src="../assets/icones/instagram.png" alt="Instagram">
                        </a>
                        <a href="#" class="icone-social" aria-label="LinkedIn">
                            <img src="../assets/icones/linkedin.png" alt="LinkedIn">
                        </a>
                        <a href="#" class="icone-social" aria-label="Facebook">
                            <img src="../assets/icones/facebook.png" alt="Facebook">
                        </a>
                        <a href="#" class="icone-social" aria-label="Twitter/X">
                            <img src="../assets/icones/twitter.png" alt="Twitter/X">
                        </a>
                    </div>
                </div>
            </div>

            <hr class="divisor-rodape">

            <div class="rodape-inferior">
                <div class="informacoes-copyright">
                    <span>© 2025 Éden Systems. Todos os direitos reservados.</span>
                </div>

                <div class="links-legais">
                    <a href="#">Termos de Uso</a>
                    <a href="#">Política de Privacidade</a>
                </div>
            </div>

        </div>
    </footer>



    <!-- fim rodapé -->

</body>
<script src="../js/LandingPage.js"></script>

</html>