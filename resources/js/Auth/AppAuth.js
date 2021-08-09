const { 
    htmlLoading, 
    showMessagesValidator, 
    color 
} = require('../Core/AppUsage');

$(function(){
    habilitaEventos()
    habilitaBotoes()
});

const habilitaBotoes = () => {}

const habilitaEventos = () => {
    $("#formLoginUser").on("submit", function(e){
        e.preventDefault()
        formLoginUser()
    });
}

const formLoginUser = () => {
    const url = '/login';
    const form = "#formLoginUser";

    $.ajax({
        type: "POST",
        url,
        data: $(form).serialize(),
        dataType: "JSON",
        beforeSend: function(){
            $(form + " .btn-primary")
                .prop("disabled", true)
                .html(htmlLoading)
        },
        success: function (response) {
            if(response.error){
                Swal.fire({
                    toast:true,
                    position: 'bottom-left',
                    title: `<h5 style="color:white"> ${response.msg} </h5>`,
                    icon: !response.error ? 'success' : 'error',
                    showConfirmButton: false,
                    timer:3000,
                    background: response.error ? 'red' : color().default,
                });  
            }else{
                window.location.href = '/home';
            }
        },
        error:function(jqXHR, textStatus, error){
            if(!!jqXHR.responseJSON.errors){
                const errors = jqXHR.responseJSON.errors;
                showMessagesValidator(form, errors);
            }
        },  
        complete:function(){
            $(form + " .btn-primary")
                .prop("disabled", false)
                .html('Log in')
        },
    });
}

module.exports = {
    habilitaBotoes,
    habilitaEventos
}