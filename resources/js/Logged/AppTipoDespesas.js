const { default: Swal } = require('sweetalert2');
const { 
    loadModal, 
    htmlLoading, 
    color, 
    loadingContent,
    deleteRowForGrid,
    optionsSwalDelete,
} = require('../Core/AppUsage');

$(() => {
    habilitaBotoes()
    habilitaEventos()
})

const modalObject = "#nivel1";
const grid = "#gridTiposDespesa";

const habilitaEventos = () => {
    $("#serachFormTiposDespesas").on("submit", function(e){
        e.preventDefault()
        getFilterTipoDespesas();
    });
}

const habilitaBotoes = () => {
    $("#addTiposDespesa").on("click", function(){
        const url = '/tiposDespesas/create';

        loadModal(url, function(){
            $("#formAddTipoDespesa").on("submit", function(e){
                e.preventDefault()
                formTipoDespesas()
            })
        });
    });

    $(".btnEditTiposDespesa").on("click", function(e){
        const id = $(this).attr("id");
        const url = '/tiposDespesas/edit/' + id;

        loadModal(url, function(){
            $("#formEditTipoDespesa").on("submit", function(e){
                e.preventDefault()
                formTipoDespesas(id)
            })
        })
    });

    $(".btnDeleteTiposDespesa").on("click", function(){
        const id = $(this).attr("id");
        const url = '/tiposDespesas/delete/' + id;

        Swal.fire(optionsSwalDelete).then(result => {
            if(result.isConfirmed){
                deleteRowForGrid(url, function(){
                    getFilterTipoDespesas()
                });
            }
        })
    })

}

const formTipoDespesas = (id) => {
    const url =  typeof id  === "undefined" ? '/tiposDespesas/store' : '/tiposDespesas/update/' + id;
    const form = typeof id  === "undefined" ? '#formAddTipoDespesa' : '#formEditTipoDespesa';
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
            
            getFilterTipoDespesas();
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

const getFilterTipoDespesas = () => {
    const url = '/tiposDespesas/';
    const form = "#serachFormTiposDespesas";

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