const { default: Swal } = require('sweetalert2');
const {
    loadingContent,
    loadModal,
    htmlLoading,
    color,
    optionsSwalDelete,
    deleteRowForGrid
} = require('../Core/AppUsage');

const modalObject = "#nivel1";
const grid = "#gridDespesasFixas";
const searchForm = "#searchFormDespesasFixas";

$(() => {
    if ($(grid).length == 0) return;

    habilitaBotoes()
    habilitaEventos()
})

const habilitaEventos = () => {
    $(searchForm).on("submit", function(e){
        e.preventDefault()
        getFilterDespesasFixas()
    });
}

const habilitaBotoes = () => {
    $("#addDespesaFixa").on("click", function(){
        loadModal('/despesasFixas/create', function(){
            $("#formAddDespesaFixa").on("submit", function(e){
                e.preventDefault()
                formDespesasFixas()
            });
        });
    });

    $(".btnEditDespesaFixa").on("click", function(){
        const id = $(this).parents('tr').attr("id");

        loadModal('/despesasFixas/edit/' + id, function(){
            $("#formEditDespesaFixa").on("submit", function(e){
                e.preventDefault()
                formDespesasFixas(id)
            });
        });
    });

    $(".btnDeleteDespesaFixa").on("click", function(){
        const id = $(this).parents('tr').attr("id");
        const url = '/despesasFixas/delete/' + id;

        Swal.fire(Object.assign({}, optionsSwalDelete, {
            text: 'Esta ação é irreversível!',
        })).then(result => {
            if(result.isConfirmed){
                deleteRowForGrid(url, function(){
                    getFilterDespesasFixas()
                });
            }
        });
    });
}

const formDespesasFixas = id => {
    const isNew = typeof id === "undefined";
    const url = isNew ? '/despesasFixas/store' : '/despesasFixas/update/' + id;
    const form = isNew ? '#formAddDespesaFixa' : '#formEditDespesaFixa';
    const type = isNew ? 'POST' : 'PUT';

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
                    if(!response.error) $(modalObject).modal('hide');
                }
            });

            getFilterDespesasFixas();
        },
        error:function(jqXHR){
            const errors = jqXHR.responseJSON ? jqXHR.responseJSON.errors : null;

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

const getFilterDespesasFixas = () => {
    $.ajax({
        type: "GET",
        url: '/despesasFixas/',
        data: $(searchForm).serialize(),
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
    habilitaBotoes,
    habilitaEventos
}
