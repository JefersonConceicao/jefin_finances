const { default: Swal } = require('sweetalert2');
const {
    loadingContent,
    loadModal,
    htmlLoading,
    color,
    optionsSwalDelete,
    deleteRowForGrid
} = require('../Core/AppUsage');

$(() =>{
    habilitaBotoes()
    habilitaEventos()
})

const modalObject = "#nivel1";
const grid = "#gridDespesas"

const habilitaEventos = () => {
    getFilterDespesas()

    $("#searchFormDespesas").on("submit", function(e){
        e.preventDefault()
        getFilterDespesas()
    });
}

const habilitaBotoes = () => {
    $("#addDespesa").on("click", function(){
        const url = '/despesas/create';

        loadModal(url, function(){
            //SETA O MES DO FILTRO PARA A DATA DE CRIAÇÃO DA DESPESA
            let mesFiltrado = $("select[name='mes'] option:selected").val();
            let anoFiltrado = $("input[name='ano']").val();

            if(!mesFiltrado || !anoFiltrado) return;

            let stringDate = `01/${mesFiltrado}/${anoFiltrado}`
            $("input[name='created_at']").val(stringDate);

            $("#formAddDespesas").on("submit", function(e){
                e.preventDefault()
                formDespesas()
            });

            settingsInModal();
        });
    });

    $(".rowSettingsDespesa").on("click", function(e){
        if(e.target.tagName != "TD"){
            return;
        }

        const id = $(this).attr("id");
        const url = '/despesas/edit/' + id;

        loadModal(url, function(){
            $("#formEditDespesas").on("submit", function(e){
                e.preventDefault()
                formDespesas(id)
            });

            settingsInModal();
        });
    });

    $(".btnDeleteDespesa").on("click", function(){
        const id = $(this).parents('tr').attr("id");
        const url = '/despesas/delete/' + id;

        Swal.fire(optionsSwalDelete).then(result => {
            if(result.isConfirmed){
                deleteRowForGrid(url, function(){
                    getFilterDespesas()
                });
            }
        });
    });

    $(".btnDespesaPago").on("click", function(){
        const buttonElement = $(this);
        const id = buttonElement.parents('tr').attr("id");
        const url = '/despesas/payDespesa/' + id

        $.ajax({
            type: "PUT",
            url,
            dataType: "JSON",
            beforeSend:function(){
                buttonElement.prop("disabled", true).html(`<i style="font-size:20px;" class="fas fa-circle-notch fa-spin">  </i>`)
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
                });

               getFilterDespesas();
            },
            complete:function(){
                buttonElement.prop("disabled", true).html('<i class="fas fa-check-square"></i>')
            }
        });
    });

    $("#copyDespesas").on("click", function(){
        const buttonElement = $(this);
        const url = '/despesas/copyDespesas';
        const form = "#searchFormDespesas"

        $.ajax({
            type: "POST",
            url,
            data: $(form).serialize(),
            dataType: "JSON",
            beforeSend:function(){
                buttonElement.prop("disabled", true).html(htmlLoading);
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
                });

                getFilterDespesas();
            },
            complete:function(){
                buttonElement.prop("disabled", false).html(` Repetir utlimas despesas `);
            }
        });
    });
}

const formDespesas = id => {
    const url =  typeof id  === "undefined" ? '/despesas/store' : '/despesas/update/' + id;
    const form = typeof id  === "undefined" ? '#formAddDespesas' : '#formEditDespesas';
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

            getFilterDespesas();
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

const getFilterDespesas = () => {
    const url = '/despesas/';
    const form = "#searchFormDespesas";

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

const settingsInModal = () => {
    const url = '/tiposDespesas/store';

    $("#btnAddTipoDespesa").on("click", function(){
        const button = $(this);
        const formData = {
            nome: $(`input[name="nome"]`).val(),
            ativo: $(`select[name="ativo"]`).val()
        }

        $.ajax({
            type: "POST",
            url,
            data: formData,
            dataType: "JSON",
            beforeSend:function(){
                button
                .prop("disabled", true)
                .html(`<i class="fa fa-spinner fa-spin"> </i>`)
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

                new bootstrap.Collapse($("#formAddTipoDespesa"), {
                    hide:true,
                });

                $(`input[name="nome"]`).val("");
                AppUsage.updateOptionsField($(`select[name='despesa_tipo_id']`), '/tiposDespesas/optionsDespesasJSON');
            },
            error:function(jqXHR, textStatus, error){
                const errors = jqXHR.responseJSON.errors;

                if(!!errors){
                    AppUsage.showMessagesValidator("#formAddTipoDespesa", errors);
                }
            },
            complete:function(){
                button
                    .prop("disabled", false).html(`Salvar`)
            },
        });
    });
}

module.exports = {
    habilitaBotoes,
    habilitaEventos
}
