const { 
    loadingContent,
    loadModal,
    htmlLoading,
} = require('../Core/AppUsage');


$(() => {
    habilitaEventos()
    habilitaBotoes()
});

const modalObject = "#nivel1";
const grid = "#gridLancamentos"

const habilitaEventos = () => {
    getFilterLancamento()
    
    $("#searchFormLancamentos").on("submit", function(e){
        e.preventDefault()
        getFilterLancamento()
    });

    $("#addLancamento").on("click", function(){
        const url = '/lancamentos/create';

        loadModal(url, function(){
            optionsFormLancamento();
        });
    });

}

const habilitaBotoes = () => {

}

const formLancamento = () => {

}

const getFilterLancamento = () => {
    const url = '/lancamentos/';
    const form = '#searchFormLancamentos';

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

const optionsFormLancamento = () => {
   $(modalObject + ' select[name="despesa"]').on("change", function(){
        if($(this).val() == "S"){
            $("#inputDespesa").show()
        }else{
            $("#inputDespesa").hide()
        }
   });      
}

module.exports = {
    habilitaEventos,
    habilitaBotoes
}