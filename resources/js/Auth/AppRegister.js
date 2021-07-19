const { default: Swal } = require("sweetalert2");
const { color } = require('../Core/AppUsage');

$(function(){
    habilitaBotoes()
    habilitaEventos()
});

const habilitaBotoes = function(){}

const habilitaEventos = () => {
    $("#formAuthRegister").on("submit", function(e){
        e.preventDefault();
        formAuthRegister();
    })
}   

const formAuthRegister = () => {
    const url = '/requestSignUP'
    const form = "#formAuthRegister"
    
    $.ajax({
        type: "POST",
        url,
        data: $(form).serialize(),
        dataType: "JSON",
        beforeSend: function(){
            $(form)
                .find('.btn-primary')
                .prop("disabled", true)
                .html(`
                    <i class="fas fa-circle-notch"> </i>
                `)
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
                didClose:() => {
                    window.location.href = '/';  
                }
            });            
        },
        error:function(jqXHR, textStatus, error){
            if(jqXHR.responseJSON.errors){
                const errors = jqXHR.responseJSON.errors;
                AppUsage.showMessagesValidator(form, errors);
            }
        },
        complete:function(){
            $(form)
                .find('.btn-primary')
                .prop("disabled", false)
                .html(`
                   Cadastrar
                `)
        }
    });
}


module.exports = {
    habilitaBotoes,
    habilitaEventos
}