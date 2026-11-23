//DARK MODE: O TEMA JÁ FOI APLICADO NO <head> (layout/master); AQUI SÓ O BOTÃO DE ALTERNAR
const CHAVE = 'jefin-tema';

const temaAtual = () => document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';

const aplicarTema = (tema) => {
    document.documentElement.setAttribute('data-theme', tema);
    $("#btnAlternarTema i")
        .toggleClass('fa-moon', tema !== 'dark')
        .toggleClass('fa-sun', tema === 'dark');
}

$(() => {
    if ($("#btnAlternarTema").length == 0) return;

    aplicarTema(temaAtual());

    $("#btnAlternarTema").on("click", function(){
        const tema = temaAtual() === 'dark' ? 'light' : 'dark';
        aplicarTema(tema);

        //SEM localStorage (NAVEGAÇÃO PRIVADA, DADOS BLOQUEADOS) O TEMA SÓ VALE ATÉ RECARREGAR
        try { localStorage.setItem(CHAVE, tema); } catch (e) {}
    });
});

module.exports = {
    aplicarTema
}
