const { htmlLoading, showMessagesValidator } = require('../Core/AppUsage')  

$(() => {
    habilitaEventos()
})

const habilitaEventos = () => {
    $("#forgotPassword").on("submit", function(e){
        e.preventDefault();
        formForgotPassword()
    }); 
}

const formForgotPassword = () => {
    const form = "#forgotPassword";
    const type = "POST";
    const url = "/sendMailForgotPassword";

    $.ajax({
        type,
        url,
        data: $(form).serialize(),
        dataType: "JSON",
        beforeSend:function(){
            $(form + " .btn-primary").prop("disabled", true).html(htmlLoading);
        },
        success: function (response) {
            
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