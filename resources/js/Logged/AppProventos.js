const { loadingContent, loadModal } = require('../Core/AppUsage');
$(() => {
    habilitaBotoes()
    habilitaEventos()
})

const grid = "#gridProventos";

const habilitaEventos = () => {
    $("#searchFormProventos").on("submit", function(e){
        e.preventDefault()
        getFilterProventos()
    });
}

const habilitaBotoes = () => {
    $("#addProvento").on("click", function(){
        const url = '/proventos/create';

        loadModal(url, function(){

        });
    })
}

const formProventos = id => {

}

const getFilterProventos = urlPaginate => {
    const url = '/proventos/';
    const form = '#searchFormProventos';

    $.ajax({
        type: "GET",
        url,
        data: $(form).serialize(),
        dataType: "HTML",
        beforeSend:function(){
            loadingContent(grid);
        },
        success: function (response) {
            $(grid).html($(response).find(grid + " >"));    
            habilitaBotoes()
        }
    });
}

module.exports = {
    habilitaEventos,
    habilitaBotoes
}  