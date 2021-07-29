const { 
    loadingContent,
    loadModal,
    htmlLoading,
    color
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

            $("#formAddLancamento").on("submit", function(e){
                e.preventDefault();
                formLancamento();
            }); 
        });
    });

}

const habilitaBotoes = () => {
    

}

const formLancamento = id => {
    const url =  typeof id  === "undefined" ? '/lancamentos/store' : '/lancamentos/update/' + id;
    const form = typeof id  === "undefined" ? '#formAddLancamento' : '#formEditLancamento';
    const type = typeof id  === "undefined" ? 'POST' : 'PUT';

    $.ajax({
        type,
        url,
        data: $(form).serialize(),
        dataType: "JSON",
        beforeSend:function(){
            $(form + " .btnSubmit")
                .prop("disabled", true)
                .html(htmlLoading)
        },
        success: function (response) {
            Swal.fire({
                toast:true,
                position: 'bottom-left',
                title: `<h5 style="color:white"> ${response.msg} </h5>`,
                icon: !response.error ? 'success' : 'error',
                showConfirmButton: false,
                timer:3000,
                background: response.error ? 'red' : color().default,
                didOpen:() => {
                   $(modalObject).modal('hide');
                }
            }); 
            
            getFilterLancamento();
        },
        error:function(jqXHR, textStatus, error){
            const errors = jqXHR.responseJSON.errors;

            if(!!errors){
                AppUsage.showMessagesValidator(form, errors);
            }
        },
        complete:function(){
            $(form + " .btnSubmit")
                .prop("disabled", false)
                .html("Salvar")
        }
    });
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
            $("#inputDespesa").prop("disabled", false);
        }else{
            $("#inputDespesa").hide()
            $("#inputDespesa").prop("disabled", true);
        }
   });      
}

module.exports = {
    habilitaEventos,
    habilitaBotoes
}