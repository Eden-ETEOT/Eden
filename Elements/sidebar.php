<link rel="stylesheet" href="/eden/CSS/sidebar.css">

<aside class="sidebarEstrutura">
    <div class="sidebarCabeçalho">
        <div class="conta">
            <a href="#" class="linkConta">A</a>
            <div class="infosLinkConta">
                <a href="#" class="linkContaNome">Administração</a>
                <p class="planoConta">Plano Avançado</p>
            </div>
        </div>
    </div>
    <hr>
    <nav class="sidebarCorpo">
        <ul class="sidebarMenu">
            <!-- inicio do menu-->
            <span class="menusPrincipais">
                <li><a href="#" class="guiaSemSeta"><img src="/eden/elements/icons/dashboard.svg" alt="" class="icone">Dashboard</a></li>
            </span>
            <!-- menu com submenu 1-->
            <li class="menusPrincipais">
                <a href="#" class="guiaComSeta" onclick="toggleSubmenu('gerenciamentoADM'); return false;">
                    <img src="/eden/elements/icons/Gerenciamento e unidades.svg" alt="" class="icone">
                    Gerenciamento Administrativo
                    <svg class="seta" width="24" height="16" viewBox="0 0 12 12">
                    <polyline points="3,4 6,8 9,4" fill="none" stroke="#fff" stroke-width="1.5"/>
                    </svg>
                </a>
                <ul id="submenu-gerenciamentoADM" class="submenu">
                    <li><a href="#" class="linkCorpo"><img src="/Eden/Elements/icons/Gerenciamento e unidades.svg" alt="" class="icone">Unidades</a></li>
                    <li><a href="#" class="linkCorpo"><img src="/Eden/Elements/icons/serviços.svg" alt="" class="icone">Serviços</a></li>
                    <li><a href="#" class="linkCorpo"><img src="/Eden/Elements/icons/Contratos.svg" alt="" class="icone">Contratos</a></li>
                    <li><a href="#" class="linkCorpo"><img src="/Eden/Elements/icons/Manutenção.svg" alt="" class="icone">Manutenções</a></li>
                    <li><a href="#" class="linkCorpo"><img src="/Eden/Elements/icons/Gestão de Usuarios.svg" alt="" class="icone">Gestão de usuários</a></li>
                    <li><a href="#" class="linkCorpo"><img src="/Eden/Elements/icons/funcionarios.svg" alt="" class="icone">Funcionários</a></li>
                </ul>
            </li>
            <!-- menu com submenu 2-->
            <li class="menusPrincipais">
                <a href="#" class="guiaComSeta" onclick="toggleSubmenu('portaria'); return false;">
                    <img src="/eden/elements/icons/portaria.svg" alt="" class="icone">
                    Portaria
                    <svg class="seta" width="24" height="16" viewBox="0 0 12 12">
                    <polyline points="3,4 6,8 9,4" fill="none" stroke="#fff" stroke-width="1.5"/>
                    </svg>
                </a>
                <ul id="submenu-portaria" class="submenu">
                    <li><a href="#" class="linkCorpo"><img src="/eden/elements/icons/visitantes.svg" alt="" class="icone">Visitantes</a></li>
                    <li><a href="#" class="linkCorpo"><img src="/eden/elements/icons/veículos.svg" alt="" class="icone">Veículos</a></li>
                    <li><a href="#" class="linkCorpo"><img src="/eden/elements/icons/encomendas.svg" alt="" class="icone">Encomendas</a></li>
                    <li><a href="#" class="linkCorpo"><img src="/eden/elements/icons/câmeras.svg" alt="" class="icone">Câmeras</a></li>
                </ul>
            </li>
            <!-- menu com submenu 3-->
            <li class="menusPrincipais">
                <a href="#" class="guiaComSeta" onclick="toggleSubmenu('comunicacao'); return false;">
                    <img src="/eden/elements/icons/comunicação.svg" alt="" class="icone">
                    Comunicação
                    <svg class="seta" width="24" height="16" viewBox="0 0 12 12">
                    <polyline points="3,4 6,8 9,4" fill="none" stroke="#fff" stroke-width="1.5"/>
                    </svg>
                </a>
                <ul id="submenu-comunicacao" class="submenu">
                    <li><a href="#" class="linkCorpo"><img src="/eden/elements/icons/chat.svg" alt="" class="icone">Chat</a></li>
                    <li><a href="#" class="linkCorpo"><img src="/eden/elements/icons/feedback.svg" alt="" class="icone">Feedbacks</a></li>
                    <li><a href="#" class="linkCorpo"><img src="/eden/elements/icons/mural.svg" alt="" class="icone">Mural</a></li>
                    <li><a href="#" class="linkCorpo"><img src="/eden/elements/icons/atas de reunião.svg" alt="" class="icone">Atas de reunião</a></li>
                </ul>
            </li>
            <!-- menu com submenu 4-->
            <li class="menusPrincipais">
                <a href="#" class="guiaComSeta" onclick="toggleSubmenu('areaEventos'); return false;">
                    <img src="/eden/elements/icons/Áreas & Eventos.svg" alt="" class="icone">
                    Áreas & Eventos
                    <svg class="seta" width="24" height="16" viewBox="0 0 12 12">
                    <polyline points="3,4 6,8 9,4" fill="none" stroke="#fff" stroke-width="1.5"/>
                    </svg>
                </a>
                <ul id="submenu-areaEventos" class="submenu">
                    <li><a href="#" class="linkCorpo"><img src="/eden/elements/icons/Reserva Área.svg" alt="" class="icone">Reservas</a></li>
                    <li><a href="#" class="linkCorpo"><img src="/eden/elements/icons/agenda.svg" alt="" class="icone">Agendadas</a></li>
                </ul>
            </li>
            <!-- menu com submenu 5-->
            <li class="menusPrincipais">
                <a href="#" class="guiaComSeta" onclick="toggleSubmenu('financeiro'); return false;">
                    <img src="/eden/elements/icons/financeiro.svg" alt="" class="icone">
                    Financeiro
                    <svg class="seta" width="24" height="16" viewBox="0 0 12 12">
                    <polyline points="3,4 6,8 9,4" fill="none" stroke="#fff" stroke-width="1.5"/>
                    </svg>
                </a>
                <ul id="submenu-financeiro" class="submenu">
                    <li><a href="#" class="linkCorpo"><img src="/eden/elements/icons/receitas.svg" alt="" class="icone">Receitas</a></li>
                    <li><a href="#" class="linkCorpo"><img src="/eden/elements/icons/despesas.svg" alt="" class="icone">Despesas</a></li>
                    <li><a href="#" class="linkCorpo"><img src="/eden/elements/icons/inadimplências.svg" alt="" class="icone">Inadimplências</a></li>
                    <li><a href="#" class="linkCorpo"><img src="/eden/elements/icons/boletos.svg" alt="" class="icone">Boletos</a></li>
                    <li><a href="#" class="linkCorpo"><img src="/eden/elements/icons/relatório.svg" alt="" class="icone">Relatórios</a></li>
                </ul>
            </li>
        </ul>
    </nav>

    <div class="sidebarRodape">
        <ul>
            <li class="menusPrincipais"><a href="#" class="link-rodape"><img src="/eden/elements/icons/configurações.svg" alt="" class="icone">Configurações</a></li>
            <li class="menusPrincipais"><a href="../login/login.php" class="link-rodape"><img src="/eden/elements/icons/logout.svg" alt="" class="icone">Logout</a></li>
        </ul>
     
    </div>
</aside>
<script src="/eden/js/sidebar.js">


</script>