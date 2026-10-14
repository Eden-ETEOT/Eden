const perguntasFaq = document.querySelectorAll('.pergunta-faq');

perguntasFaq.forEach((pergunta) => {
    pergunta.addEventListener('click', () => {
        const itemFaq = pergunta.closest('.item-faq');

        itemFaq.classList.toggle('ativo');
    });
});

document.querySelectorAll('[data-detalhe]').forEach((btn) => {
    btn.addEventListener('click', () => {
        const box = document.getElementById(btn.dataset.detalhe);
        if (box) box.classList.toggle('aberto');
    });
});
