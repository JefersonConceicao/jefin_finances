const { htmlLoading, showMessagesValidator, color } = require('../Core/AppUsage')  

$(() => {
    habilitaEventos()
})

const habilitaEventos = () => {
    $("#forgotPassword").on("submit", function(e){
        e.preventDefault();
        formForgotPassword(true)
    }); 

    $("#resetPassword").on("submit", function(e){
        e.preventDefault()
        formForgotPassword(false)
    });
}

const formForgotPassword = reset => {
    const form = !!reset ? "#forgotPassword" : "#resetPassword";
    const type =  !!reset ? "POST" : "PUT";
    const url = !!reset ? "/sendMailForgotPassword" : "/resetPassword";

    $.ajax({
        type,
        url,
        data: $(form).serialize(),
        dataType: "JSON",
        beforeSend:function(){
            $(form + " .btn-primary").prop("disabled", true).html(htmlLoading);
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
            const errors = jqXHR.responseJSON.errors;
            
            if(!!errors){
                showMessagesValidator(form, errors);
            }
        },
        complete:function(){
            $(form + " .btn-primary").prop("disabled", false).html(`Salvar`);
        }
    });
}

module.exports = {
    habilitaEventos,
}