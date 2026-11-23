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
    $("#mostrarOcultas").on("change", function(){
        getFilterDespesasFixas()
    });

    $(".btnOcultarDespesaFixa").on("click", function(){
        const id = $(this).parents('tr').attr("id");
        const oculta = $(this).data("oculta");

        const ocultar = () => $.ajax({
            type: 'PUT',
            url: '/despesasFixas/ocultar/' + id,
            data: { oculta },
            dataType: "JSON",
            success: function (response) {
                Swal.fire({
                    toast:true,
                    position: 'bottom-left',
                    title: `<h5 style="color:white"> ${response.msg} </h5>`,
                    icon: !response.error ? 'success' : 'error',
                    showConfirmButton: false,
                    timer:3000,
                    background: response.error ? 'red' : color().default,
                });

                getFilterDespesasFixas();
            }
        });

        if(oculta == 1){
            Swal.fire(Object.assign({}, optionsSwalDelete, {
                title: 'Ocultar?',
                text: 'A Despesa Fixa e todas as Ocorrências dela somem de todo o app (listas, totais e Projeção), sem excluir nada. Dá para desocultar depois em "Mostrar ocultas".',
                confirmButtonText: 'Sim, ocultar',
            })).then(result => {
                if(result.isConfirmed) ocultar();
            });
        }else{
            ocultar();
        }
    });

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

            $("#btnReajustarDespesaFixa").on("click", function(){
                acaoDespesaFixa('/despesasFixas/reajustar/' + id, '#reajusteDespesaFixa', $(this))
            });

            $("#btnEncerrarDespesaFixa").on("click", function(){
                const button = $(this);

                Swal.fire(Object.assign({}, optionsSwalDelete, {
                    title: 'Encerrar a Despesa Fixa?',
                    text: 'As Ocorrências depois do mês escolhido serão excluídas.',
                    confirmButtonText: 'Sim, encerrar',
                })).then(result => {
                    if(result.isConfirmed){
                        acaoDespesaFixa('/despesasFixas/encerrar/' + id, '#encerramentoDespesaFixa', button)
                    }
                });
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

//REAJUSTAR E ENCERRAR ENVIAM SÓ OS CAMPOS DA PRÓPRIA SEÇÃO DO MODAL
const acaoDespesaFixa = (url, secao, button) => {
    const form = '#formEditDespesaFixa';
    const textoBotao = button.html();

    $.ajax({
        type: 'PUT',
        url,
        data: $(secao + ' :input').serialize(),
        dataType: "JSON",
        beforeSend:function(){
            button.prop("disabled", true).html(htmlLoading)
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
            button.prop("disabled", false).html(textoBotao)
        }
    });
}

const getFilterDespesasFixas = () => {
    $.ajax({
        type: "GET",
        url: '/despesasFixas/',
        //"MOSTRAR OCULTAS" FICA NO CABEÇALHO DA GRADE, FORA DO FORMULÁRIO DE FILTRO
        data: $(searchForm).serialize() + ($("#mostrarOcultas").is(":checked") ? '&mostrar_ocultas=1' : ''),
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
