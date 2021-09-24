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
    const form = "#addTiposDespesas";

    $(form + " .btn-success").on("click", function(){   
        $.ajax({
            type: "POST",
            url,
            data: $(form).serialize(),
            dataType: "JSON",
            beforeSend:function(){
                $(form + " .btn-success")
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
            },  
            error:function(jqXHR, textStatus, error){
                const errors = jqXHR.responseJSON.errors;
                if(!!errors){
                    AppUsage.showMessagesValidator(form, errors);
                }
            },
            complete:function(){
                $(form + " .btn-success").prop("disabled", false).html(`Salvar`)
            },
        });
    });
}

const optionsTiposDespesasJSON = () => {}

module.exports = {
    habilitaBotoes,
    habilitaEventos
}