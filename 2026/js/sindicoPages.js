/* Utilidades compartilhadas das telas do sindico (moradores/ocorrencias/documentacao). */
function abrirModal(id) {
  document.getElementById(id).classList.add('active');
  document.body.style.overflow = 'hidden';
}
function fecharModal(id) {
  const alvos = id ? [document.getElementById(id)] : document.querySelectorAll('.sind-page .modal-overlay.active');
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
