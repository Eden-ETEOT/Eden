lucide.createIcons();
const pb = document.getElementById('profileButton'),
    pm = document.getElementById('profileMenu');
if (pb && pm) {
    pb.onclick = e => {
        e.stopPropagation();
        pm.classList.toggle('open')
    };
    pm.onclick = e => e.stopPropagation();
    document.addEventListener('click', () => pm.classList.remove('open'))
}
document.querySelectorAll('.switch').forEach(x => x.onclick = () => x.classList.toggle('on'));
document.querySelectorAll('.faq-row').forEach(x => x.onclick = () => x.classList.toggle('open'));
document.querySelectorAll('[data-open]').forEach(b => b.onclick = () => document.getElementById(b.dataset.open).classList.add('open'));
document.querySelectorAll('[data-close]').forEach(b => b.onclick = () => b.closest('.modal-overlay').classList.remove('open'));
document.querySelectorAll('.modal-overlay').forEach(m => m.onclick = e => { if (e.target === m) m.classList.remove('open') });