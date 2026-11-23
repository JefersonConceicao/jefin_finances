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
const grid = "#gridProventosFixos";
const searchForm = "#searchFormProventosFixos";

$(() => {
    if ($(grid).length == 0) return;

    habilitaBotoes()
    habilitaEventos()
})

const habilitaEventos = () => {
    $(searchForm).on("submit", function(e){
        e.preventDefault()
        getFilterProventosFixos()
    });
}

const habilitaBotoes = () => {
    $("#mostrarOcultas").on("change", function(){
        getFilterProventosFixos()
    });

    $(".btnOcultarProventoFixo").on("click", function(){
        const id = $(this).parents('tr').attr("id");
        const oculta = $(this).data("oculta");

        const ocultar = () => $.ajax({
            type: 'PUT',
            url: '/proventosFixos/ocultar/' + id,
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

                getFilterProventosFixos();
            },
            error: function () {
                Swal.fire({
                    toast:true,
                    position: 'bottom-left',
                    title: `<h5 style="color:white"> Não foi possível concluir. Recarregue a página e tente de novo. </h5>`,
                    icon: 'error',
                    showConfirmButton: false,
                    timer:3500,
                    background: 'red',
                });
            }
        });

        if(oculta == 1){
            Swal.fire(Object.assign({}, optionsSwalDelete, {
                title: 'Ocultar?',
                text: 'O Provento Fixo e todas as Ocorrências dele somem de todo o app (listas, totais e Projeção), sem excluir nada. Dá para desocultar depois em "Mostrar ocultos".',
                confirmButtonText: 'Sim, ocultar',
            })).then(result => {
                if(result.isConfirmed) ocultar();
            });
        }else{
            ocultar();
        }
    });

    $("#addProventoFixo").on("click", function(){
        loadModal('/proventosFixos/create', function(){
            $("#formAddProventoFixo").on("submit", function(e){
                e.preventDefault()
                formProventosFixos()
            });
        });
    });

    $(".btnEditProventoFixo").on("click", function(){
        const id = $(this).parents('tr').attr("id");

        loadModal('/proventosFixos/edit/' + id, function(){
            $("#formEditProventoFixo").on("submit", function(e){
                e.preventDefault()
                formProventosFixos(id)
            });

            $("#btnReajustarProventoFixo").on("click", function(){
                acaoProventoFixo('/proventosFixos/reajustar/' + id, '#reajusteProventoFixo', $(this))
            });

            $("#btnEncerrarProventoFixo").on("click", function(){
                const button = $(this);

                Swal.fire(Object.assign({}, optionsSwalDelete, {
                    title: 'Encerrar o Provento Fixo?',
                    text: 'As Ocorrências depois do mês escolhido serão excluídas.',
                    confirmButtonText: 'Sim, encerrar',
                })).then(result => {
                    if(result.isConfirmed){
                        acaoProventoFixo('/proventosFixos/encerrar/' + id, '#encerramentoProventoFixo', button)
                    }
                });
            });
        });
    });

    $(".btnDeleteProventoFixo").on("click", function(){
        const id = $(this).parents('tr').attr("id");
        const url = '/proventosFixos/delete/' + id;

        Swal.fire(Object.assign({}, optionsSwalDelete, {
            text: 'Esta ação é irreversível!',
        })).then(result => {
            if(result.isConfirmed){
                deleteRowForGrid(url, function(){
                    getFilterProventosFixos()
                });
            }
        });
    });
}

const formProventosFixos = id => {
    const isNew = typeof id === "undefined";
    const url = isNew ? '/proventosFixos/store' : '/proventosFixos/update/' + id;
    const form = isNew ? '#formAddProventoFixo' : '#formEditProventoFixo';
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

            getFilterProventosFixos();
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
const acaoProventoFixo = (url, secao, button) => {
    const form = '#formEditProventoFixo';
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

            getFilterProventosFixos();
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

const getFilterProventosFixos = () => {
    $.ajax({
        type: "GET",
        url: '/proventosFixos/',
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
