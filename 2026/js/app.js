/* app.js — JS compartilhado da área logada (helpers + comportamentos).
   Unifica sindicoPages.js e FrontDev.js. dashboard.php usa dashboard.js. */

/* Utilidades compartilhadas das telas do sindico (moradores/ocorrencias/documentacao). */
function abrirModal(id) {
  document.getElementById(id).classList.add('active');
  document.body.style.overflow = 'hidden';
}
function fecharModal(id) {
  const alvos = id ? [document.getElementById(id)] : document.querySelectorAll('.modal-overlay.active');
  alvos.forEach(m => m && m.classList.remove('active'));
  document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') fecharModal(); });
document.addEventListener('click', e => {
  if (e.target.classList && e.target.classList.contains('modal-overlay')) fecharModal(e.target.id);
});
/* Filtro textual (+ status opcional). Usa classe f-hide; a paginacao usa style.display. */
function filtrarLinhas(tbodyId, texto, status) {
  texto = (texto || '').toLowerCase();
  document.querySelectorAll('#' + tbodyId + ' tr').forEach(tr => {
    const okTexto = !texto || tr.innerText.toLowerCase().includes(texto);
    const okStatus = !status || (tr.dataset.status || '') === status;
    tr.classList.toggle('f-hide', !(okTexto && okStatus));
  });
  if (pagEstado[tbodyId]) { pagEstado[tbodyId].pagina = 1; desenharPaginacao(tbodyId); }
}
let pagEstado = {};
function paginar(tbodyId, pagerId, porPagina) {
  if (!document.getElementById(tbodyId) || !document.getElementById(pagerId)) return;
  pagEstado[tbodyId] = { pagerId, porPagina, pagina: 1 };
  desenharPaginacao(tbodyId);
}
function desenharPaginacao(tbodyId) {
  const est = pagEstado[tbodyId];
  if (!est) return;
  const rows = [...document.querySelectorAll('#' + tbodyId + ' tr')].filter(tr => !tr.classList.contains('f-hide'));
  const total = Math.max(1, Math.ceil(rows.length / est.porPagina));
  if (est.pagina > total) est.pagina = total;
  rows.forEach((tr, i) => {
    tr.style.display = (Math.floor(i / est.porPagina) + 1 === est.pagina) ? '' : 'none';
  });
  const pager = document.getElementById(est.pagerId);
  pager.innerHTML = '';
  if (total <= 1) return;
  const btn = (label, pg, cls) => {
    const b = document.createElement('button');
    b.type = 'button';
    b.className = 'page' + (cls ? ' ' + cls : '');
    b.textContent = label;
    b.onclick = () => { pagEstado[tbodyId].pagina = pg; desenharPaginacao(tbodyId); };
    pager.appendChild(b);
  };
  btn('◀', Math.max(1, est.pagina - 1));
  for (let p = 1; p <= total; p++) btn(String(p), p, p === est.pagina ? 'active' : '');
  btn('▶', Math.min(total, est.pagina + 1));
}

/* FrontDev.js — comportamentos das telas-front-dev (lucide + filtros + modais).
   Cada bloco só atua se os elementos da página existirem. */
document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) lucide.createIcons();

    /* ===== Dashboard: pesquisa na tabela ===== */
    const searchInput = document.getElementById('searchOccurrence');
    const occRows = document.querySelectorAll('#occurrencesBody tr');
    if (searchInput && occRows.length) {
        searchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            occRows.forEach((row) => {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }

    /* ===== Dashboard: filtro Em análise ===== */
    const filterButton = document.getElementById('filterButton');
    if (filterButton && occRows.length) {
        let filtroAtivo = false;
        filterButton.addEventListener('click', () => {
            filtroAtivo = !filtroAtivo;
            occRows.forEach((row) => {
                row.style.display = (!filtroAtivo || row.textContent.toLowerCase().includes('em análise')) ? '' : 'none';
            });
            filterButton.textContent = filtroAtivo ? 'Limpar filtro' : 'Filtro';
        });
    }
});

/* ===== Apartamentos: modais ===== */
function fdAbrirModal(id) {
    const m = document.getElementById(id);
    if (m) m.classList.add('active');
}
function fdFecharModal(id) {
    const m = document.getElementById(id);
    if (m) m.classList.remove('active');
}
function fdFecharClicandoFora(event, id) {
    if (event.target && event.target.id === id) fdFecharModal(id);
}


/* ===== Apartamentos: pesquisa e filtro ===== */
function fdPesquisarApartamento() {
    const q = (document.getElementById('aptSearchInput').value || '').toLowerCase().trim();
    document.querySelectorAll('#apartmentTable tr').forEach((row) => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
let fdAptFiltroInativos = false;
function fdFiltrarApartamentos() {
    fdAptFiltroInativos = !fdAptFiltroInativos;
    document.querySelectorAll('#apartmentTable tr').forEach((row) => {
        if (!fdAptFiltroInativos) {
            row.style.display = '';
            return;
        }
        row.style.display = (row.getAttribute('data-status') === 'Inativo') ? '' : 'none';
    });
}

/* ===== Apartamentos: detalhes e status ===== */
function fdVerApartamento(a) {
    document.getElementById('viewApartmentTitle').textContent = 'Apt. ' + a.numResid + ' - Bloco ' + a.bloco;
    document.getElementById('viewResident').textContent = a.proprietario || 'Sem morador';
    document.getElementById('viewApartment').textContent = a.numResid;
    document.getElementById('viewBlock').textContent = a.bloco;
    document.getElementById('viewFloor').textContent = a.andar;
    document.getElementById('viewArea').textContent = a.metragem !== null ? String(a.metragem).replace('.', ',') + ' m²' : '—';
    const ativo = parseInt(a.ativo, 10) === 1;
    document.getElementById('aptStatusId').value = a.idUnidade;
    document.getElementById('aptStatusAtivo').value = ativo ? '0' : '1';
    document.getElementById('aptStatusBtn').textContent = ativo ? 'Desativar Apartamento' : 'Reativar Apartamento';
    fdAbrirModal('apartmentDetailsModal');
}
function fdStatusApartamento(id, ativo, verbo) {
    if (!confirm('Deseja realmente ' + verbo + ' este apartamento?')) return;
    const f = document.createElement('form');
    f.method = 'post';
    f.innerHTML = '<input type="hidden" name="acao" value="status">'
        + '<input type="hidden" name="id" value="' + id + '">'
        + '<input type="hidden" name="ativo" value="' + ativo + '">';
    document.body.appendChild(f);
    f.submit();
}

/* ===== Apartamentos: novo bloco (opção local do formulário) ===== */
function fdCadastrarBloco(event) {
    event.preventDefault();
    const nome = document.getElementById('blockName').value.trim();
    if (!nome) return false;
    const sel = document.getElementById('newBlockSelect');
    let opt = Array.from(sel.options).find((o) => o.value === nome);
    if (!opt) {
        opt = document.createElement('option');
        opt.value = nome;
        opt.textContent = nome;
        sel.appendChild(opt);
    }
    sel.value = nome;
    document.getElementById('blockName').value = '';
    fdFecharModal('newBlockModal');
    fdAbrirModal('newApartmentModal');
    return false;
}

/* ===== Toast único da área logada ===== */
function toast(message, type = 'info', duration = 3000) {
    const colors = { success: '#4D8F14', error: '#C64539', warning: '#FFBF00', info: '#4C90B2' };
    if (!document.getElementById('fd-toast-keyframes')) {
        const st = document.createElement('style');
        st.id = 'fd-toast-keyframes';
        st.textContent = '@keyframes fdSlideIn{from{transform:translateX(400px);opacity:0}to{transform:translateX(0);opacity:1}}'
            + '@keyframes fdSlideOut{from{transform:translateX(0);opacity:1}to{transform:translateX(400px);opacity:0}}';
        document.head.appendChild(st);
    }
    const el = document.createElement('div');
    el.textContent = message;
    el.style.cssText = 'position:fixed;top:20px;right:20px;padding:16px 24px;'
        + 'background-color:' + (colors[type] || colors.info) + ';color:#fff;border-radius:8px;'
        + 'box-shadow:0 4px 12px rgba(0,0,0,.15);z-index:9999;animation:fdSlideIn .3s ease;'
        + 'font-family:Inter,sans-serif;font-size:14px;';
    document.body.appendChild(el);
    setTimeout(() => {
        el.style.animation = 'fdSlideOut .3s ease';
        setTimeout(() => el.remove(), 300);
    }, duration);
}
function fdToast(msg) {
    toast(msg, 'info');
}

/* ===== Relatórios: gráfico mensal ===== */
(function initFdChart() {
    const canvas = document.getElementById('occurrencesChart');
    if (!canvas || typeof Chart === 'undefined' || typeof FD_MESES === 'undefined') return;
    const labels = FD_MESES.map((m) => m.short);
    window.__fdChart = new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'Total', data: FD_MESES.map((m) => m.total) },
                { label: 'Resolvidas', data: FD_MESES.map((m) => m.resolvidas) }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
})();

function fdGerarRelatorio() {
    const tipo = document.getElementById('reportType').value;
    const periodo = document.getElementById('reportPeriod').value;
    if (tipo !== 'ocorrencias') {
        fdToast('Relatório "' + tipo + '" com geração guiada em breve. Use a exportação abaixo.');
        return;
    }
    const dados = (typeof FD_MESES !== 'undefined' ? FD_MESES : []).find((m) => m.ym === periodo);
    if (!dados) return;
    if (window.__fdChart) {
        const idx = FD_MESES.findIndex((m) => m.ym === periodo);
        const labels = FD_MESES.slice(0, idx + 1).map((m) => m.short);
        window.__fdChart.data.labels = labels;
        window.__fdChart.data.datasets[0].data = FD_MESES.slice(0, idx + 1).map((m) => m.total);
        window.__fdChart.data.datasets[1].data = FD_MESES.slice(0, idx + 1).map((m) => m.resolvidas);
        window.__fdChart.update();
    }
    document.getElementById('chartPeriod').textContent = FD_MESES[0].rotulo + ' — ' + dados.rotulo;
    document.getElementById('totalOccurrences').textContent = dados.total;
    const taxa = dados.total > 0 ? Math.round((dados.resolvidas / dados.total) * 100) : 0;
    document.getElementById('resolutionRate').textContent = taxa + '%';
    document.querySelectorAll('.stats-grid .stat-card small')[0].textContent = dados.rotulo;
    document.querySelectorAll('.stats-grid .stat-card small')[1].textContent = dados.rotulo;
    fdToast('Relatório de ' + dados.rotulo + ' gerado.');
}

function fdBaixarRelatorio(tipo) {
    const nomes = {
        ocorrencias: 'Relatório de Ocorrências',
        financeiro: 'Relatório Financeiro',
        moradores: 'Relatório de Moradores',
        infraestrutura: 'Relatório de Infraestrutura'
    };
    if (tipo === 'financeiro') {
        fdToast('Módulo financeiro ainda não possui dados para exportar.');
        return;
    }
    const linhas = (typeof FD_CSV !== 'undefined' && FD_CSV[tipo]) ? FD_CSV[tipo] : [];
    if (!linhas.length) {
        fdToast('Nenhum dado disponível para ' + (nomes[tipo] || 'o relatório') + '.');
        return;
    }
    const cab = Object.keys(linhas[0]);
    const esc = (v) => '"' + String(v === null || v === undefined ? '' : v).replace(/"/g, '""') + '"';
    const csv = '\uFEFF' + cab.join(';') + '\n' + linhas.map((r) => cab.map((c) => esc(r[c])).join(';')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = tipo + '-' + new Date().toISOString().slice(0, 10) + '.csv';
    document.body.appendChild(a);
    a.click();
    a.remove();
    fdToast(nomes[tipo] + ' exportado.');
}

/* ===== Permissões: matriz estática de acesso por módulo/papel ===== */
(function renderFdPermissions() {
    const tbody = document.getElementById('permissionsTable');
    if (!tbody) return;
    const matriz = [
        { modulo: 'Dashboard', sindico: true, administrador: true, porteiro: false, manutencao: false },
        { modulo: 'Ocorrências', sindico: true, administrador: true, porteiro: true, manutencao: true },
        { modulo: 'Moradores', sindico: true, administrador: true, porteiro: false, manutencao: false },
        { modulo: 'Apartamentos', sindico: true, administrador: true, porteiro: false, manutencao: true },
        { modulo: 'Documentação', sindico: true, administrador: true, porteiro: false, manutencao: false },
        { modulo: 'Relatórios', sindico: true, administrador: true, porteiro: false, manutencao: false },
        { modulo: 'Configurações', sindico: true, administrador: true, porteiro: false, manutencao: false },
        { modulo: 'Permissões', sindico: true, administrador: false, porteiro: false, manutencao: false }
    ];
    const cel = (v) => v
        ? '<div class="permission-indicator allowed"><i data-lucide="check"></i></div>'
        : '<div class="permission-indicator denied"></div>';
    tbody.innerHTML = matriz.map((item) =>
        '<tr><td>' + item.modulo + '</td>'
        + '<td>' + cel(item.sindico) + '</td>'
        + '<td>' + cel(item.administrador) + '</td>'
        + '<td>' + cel(item.porteiro) + '</td>'
        + '<td>' + cel(item.manutencao) + '</td></tr>'
    ).join('');
    if (window.lucide) lucide.createIcons();
})();

function fdEditarFuncionario(f) {
    document.getElementById('editEmployeeId').value = f.idFuncionario;
    document.getElementById('editEmployeeName').value = f.nome;
    document.getElementById('editEmployeeEmail').value = f.email;
    document.getElementById('editEmployeeRole').value = f.funcao;
    document.getElementById('editEmployeeStatus').value = parseInt(f.ativo, 10) === 1 ? 'Ativo' : 'Inativo';
    fdAbrirModal('editModal');
}

/* ===== Suporte: FAQ sanfona ===== */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.fd-suporte .faq-question').forEach((btn) => {
        btn.addEventListener('click', () => {
            const item = btn.closest('.faq-item');
            const aberto = item.classList.contains('open');
            document.querySelectorAll('.fd-suporte .faq-item.open').forEach((o) => o.classList.remove('open'));
            if (!aberto) item.classList.add('open');
        });
    });
});

/* ===== Suporte: envio do chamado (confirmação local) ===== */
function fdEnviarChamado(event) {
    event.preventDefault();
    document.getElementById('supportForm').reset();
    fdAbrirModal('successModal');
    return false;
}
