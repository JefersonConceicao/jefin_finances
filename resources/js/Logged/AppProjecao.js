//LINHA DO TEMPO DOS ALERTAS (MODAL DA PROJEÇÃO): FILTRO TUDO/ENTRADAS/SAÍDAS, ALTURA DAS BARRAS E ROLAGEM ATÉ O MÊS
const moeda = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

const reais = (valor) => moeda.format(Math.abs(valor)).replace(/\s/g, ' ');

//VALOR QUE A BARRA E O CABEÇALHO DO MÊS MOSTRAM PARA O FILTRO: EFEITO LÍQUIDO, SÓ O QUE ENTRA OU SÓ O QUE SAI
//EM CENTAVOS, PARA ENTRADAS IGUAIS ÀS SAÍDAS DAREM ZERO EXATO
const valorNoFiltro = (entra, sai, filtro) => {
    let valor = entra - sai;
    if (filtro === 'entra') valor = entra;
    if (filtro === 'sai') valor = -sai;
    return Math.round(valor * 100) / 100;
}

//MÊS SEM NENHUM ALERTA DO LADO FILTRADO (CONTA OS ALERTAS, NÃO A SOMA: UM ALERTA DE R$ 0,00 AINDA CONTA)
const vazioNoFiltro = ($barra, filtro) => {
    const entram = parseInt($barra.data('qtd-entra'), 10) || 0;
    const saem = parseInt($barra.data('qtd-sai'), 10) || 0;
    if (filtro === 'entra') return entram === 0;
    if (filtro === 'sai') return saem === 0;
    return entram + saem === 0;
}

const lerValores = ($el) => [parseFloat($el.data('entra')) || 0, parseFloat($el.data('sai')) || 0];

const desenharBarras = ($modal, filtro) => {
    const $barras = $modal.find('.ldt-barra');
    const leituras = $barras.map(function(){
        const [entra, sai] = lerValores($(this));
        return { valor: valorNoFiltro(entra, sai, filtro), vazio: vazioNoFiltro($(this), filtro) };
    }).get();
    const valores = leituras.map((l) => l.valor);
    const maximo = Math.max(...valores.map(Math.abs), 0);

    $barras.each(function(i){
        const valor = valores[i];
        const altura = maximo > 0 ? Math.round(Math.abs(valor) / maximo * 100) : 0;
        //BARRA DE MÊS COM ALGUM VALOR TEM PELO MENOS 4% PARA NÃO SUMIR AO LADO DE UM MÊS MUITO MAIOR
        const visivel = valor !== 0 ? Math.max(altura, 4) : 0;
        const vazio = leituras[i].vazio;
        //MÊS COM ALERTAS QUE SE ANULAM: MARCA NEUTRA CINZA NO EIXO, PARA NÃO PARECER UM MÊS VAZIO
        const neutro = !vazio && valor === 0;
        const descricao = $(this).data('label') + ': ' + (vazio ? 'nada no filtro' : (valor < 0 ? '-' : '+') + reais(valor));

        $(this).find('.ldt-cima .ldt-coluna')
            .toggleClass('ldt-neutra', neutro)
            .css('height', (valor > 0 ? visivel : (neutro ? 6 : 0)) + '%');
        $(this).find('.ldt-baixo .ldt-coluna').css('height', (valor < 0 ? visivel : 0) + '%');
        $(this).prop('disabled', vazio).attr({ title: descricao, 'aria-label': descricao });
    });
}

const filtrarLinha = ($modal, filtro) => {
    $modal.find('.ldt-item').each(function(){
        $(this).toggle(filtro === 'tudo' || $(this).data('lado') === filtro);
    });

    $modal.find('.ldt-mes').each(function(){
        const [entra, sai] = lerValores($(this));
        const valor = valorNoFiltro(entra, sai, filtro);
        const temItens = filtro === 'tudo' || $(this).find('.ldt-item[data-lado="' + filtro + '"]').length > 0;

        $(this).toggle(temItens);
        $(this).find('.ldt-efeito')
            .text((valor < 0 ? '-' : '+') + reais(valor))
            .toggleClass('text-danger', valor < 0)
            .toggleClass('text-success', valor >= 0);
    });
}

const aplicarFiltro = ($modal, filtro) => {
    $modal.find('[data-filtro]').each(function(){
        const ativo = $(this).data('filtro') === filtro;
        $(this).toggleClass('active', ativo).attr('aria-pressed', ativo ? 'true' : 'false');
    });
    desenharBarras($modal, filtro);
    filtrarLinha($modal, filtro);
}

$(() => {
    const $modal = $("#modalLinhaDoTempo");
    if ($modal.length == 0) return;

    aplicarFiltro($modal, 'tudo');

    $modal.on('click', '[data-filtro]', function(){
        aplicarFiltro($modal, $(this).data('filtro'));
    });

    $modal.on('click', '.ldt-barra', function(){
        const alvo = document.getElementById($(this).data('alvo'));
        if (!alvo) return;

        alvo.scrollIntoView({ behavior: 'smooth', block: 'start' });
        //UM CLIQUE NOVO REINICIA O DESTAQUE EM VEZ DE SER CORTADO PELO TIMER DO CLIQUE ANTERIOR
        clearTimeout($(alvo).data('timerDestaque'));
        $(alvo).addClass('ldt-destaque');
        $(alvo).data('timerDestaque', setTimeout(() => $(alvo).removeClass('ldt-destaque'), 1200));
    });
});

module.exports = {
    valorNoFiltro
}
