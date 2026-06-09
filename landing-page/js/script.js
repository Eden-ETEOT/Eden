const perguntasFaq = document.querySelectorAll('.pergunta-faq');

perguntasFaq.forEach((pergunta) => {
    pergunta.addEventListener('click', () => {
        const itemFaq = pergunta.closest('.item-faq');

        itemFaq.classList.toggle('ativo');
    });
});
