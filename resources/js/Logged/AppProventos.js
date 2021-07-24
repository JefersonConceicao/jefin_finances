const { default: Swal } = require('sweetalert2');
const { 
    loadingContent, 
    loadModal, 
    htmlLoading, 
    color,
    optionsSwalDelete, 
    deleteRowForGrid
} = require('../Core/AppUsage');

$(() => {
    habilitaBotoes()
    habilitaEventos()
})

const modalObject = "#nivel1";
const grid = "#gridProventos";

const habilitaEventos = () => {
    getFilterProventos();


    $("#searchFormProventos").on("submit", function(e){
        e.preventDefault()
        getFilterProventos()
    });
}

const habilitaBotoes = () => {
    $("#addProvento").on("click", function(){
        const url = '/proventos/create';

        loadModal(url, function(){
            $("#formAddProvento").on("submit", function(e){
                e.preventDefault()
                formProventos()
            }); 
        });
    })

    $(".btnEditProvento").on("click", function(){
        const id = $(this).attr("id");
        const url = '/proventos/edit/' + id;

        loadModal(url, function(){
            $("#formEditProvento").on("submit", function(e){
                e.preventDefault()
                formProventos(id)
            });
        });
    });

    $(".btnDeleteProvento").on("click", function(){
        const id = $(this).attr("id");
        const url = '/proventos/delete/' + id;

        Swal.fire(optionsSwalDelete).then(result => {
            if(result.isConfirmed){
                deleteRowForGrid(url, function(){
                    getFilterProventos()
                });
            }
        })
    });
}

const formProventos = id => {
    const url =  typeof id  === "undefined" ? '/proventos/store' : '/proventos/update/' + id;
    const form = typeof id  === "undefined" ? '#formAddProvento' : '#formEditProvento';
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
            
            getFilterProventos();
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