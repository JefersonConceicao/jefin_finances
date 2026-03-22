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

const togglePeriodoFields = value => {
    if(value === 'especifico'){
        $("#rowDataProvento").hide();
        $("#rowPeriodoEspecifico").show();
    } else if(value === 'anual'){
        $("#rowDataProvento").hide();
        $("#rowPeriodoEspecifico").hide();
    } else {
        $("#rowDataProvento").show();
        $("#rowPeriodoEspecifico").hide();
    }
};

const initPeriodoInputs = () => {
    const selectedPeriod = $("input[name='period_type']:checked").val();
    togglePeriodoFields(selectedPeriod);

    $("input[name='period_type']").off('click').on("click", function(){
        const current = $(this).val();
        if($(this).prop('checked')){
            if($(this).data('current') === current){
                // Permite desmarcar o radio atual (volta para nenhum selecionado)
                $(this).prop('checked', false);
                $(this).data('current', null);
                togglePeriodoFields(null);
                return;
            }

            $("input[name='period_type']").data('current', null);
            $(this).data('current', current);
            togglePeriodoFields(current);
        }
    });
};

const habilitaBotoes = () => {
    $("#addProvento").on("click", function(){
        const url = '/proventos/create';

        loadModal(url, function(){
            initPeriodoInputs();
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
            initPeriodoInputs();
            $("#formEditProvento").on("submit", function(e){
                e.preventDefault()
                formProventos(id)
            });
        });
    });

    $(".btnDeleteProvento").on("click", function(){
        const id = $(this).attr("id");
        const url = '/proventos/delete/' + id;

        Swal.fire({
            title: 'Deseja excluir este provento?',
            text: "Esta ação não poderá ser revertida!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: color().default,
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, excluir apenas este',
            cancelButtonText: 'Cancelar',
            showDenyButton: true,
            denyButtonText: 'Excluir este e os subsequentes',
            denyButtonColor: '#1cef38',
        }).then(result => {
            if(result.isConfirmed || result.isDenied){
                const deleteSubsequent = result.isDenied;
                
                $.ajax({
                    type: "DELETE",
                    url,
                    data: { 
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        delete_subsequent: deleteSubsequent 
                    },
                    dataType: "JSON",
                    success: function (response) {
                        Swal.fire({
                            toast:true,
                            position: 'bottom-left',
                            title: `<h5 style="color:white"> ${response.msg} </h5>`,
                            icon: !response.error ? 'success' : 'error',
                            showConfirmButton: false,
                            timer:3000,
                            background: response.error ? 'red' : color().default
                        });
                        getFilterProventos()
                    }
                });
            }
        })
    });

    $("#copyProventos").on("click", function(){
        const buttonElement = $(this);
        const url = '/proventos/copyProventos';
        const form = '#searchFormProventos';

       $.ajax({
           type: "POST",
           url,
           data: $(form).serialize(),
           dataType: "JSON",
           beforeSend:function(){
                buttonElement
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
                    background: response.error ? 'red' : color().default
                });

                getFilterProventos();
           },
           complete:function(){
               buttonElement
                    .prop("disabled", false)
                    .html(`Repetir proventos do mês anterior`)
           }
       });
    });
}

const formProventos = id => {
    const url =  typeof id  === "undefined" ? '/proventos/store' : '/proventos/update/' + id;
    const form = typeof id  === "undefined" ? '#formAddProvento' : '#formEditProvento';
    const type = typeof id  === "undefined" ? 'POST' : 'PUT';

    const executeSubmit = (updateSubsequent = false) => {
        let formData = $(form).serialize();
        if(updateSubsequent) {
            formData += '&update_subsequent=true';
        }

        $.ajax({
            type,
            url,
            data: formData,
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

    if(typeof id !== "undefined"){
        Swal.fire({
            title: 'Deseja alterar apenas este provento?',
            text: "Você pode aplicar esta alteração aos meses seguintes.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: color().default,
            cancelButtonColor: '#d33',
            confirmButtonText: 'Apenas este',
            cancelButtonText: 'Cancelar',
            showDenyButton: true,
            denyButtonText: 'Este e os subsequentes',
            denyButtonColor: '#1cef38',
        }).then(result => {
            if(result.isConfirmed){
                executeSubmit(false);
            } else if(result.isDenied){
                executeSubmit(true);
            }
        });
    } else {
        executeSubmit();
    }
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
