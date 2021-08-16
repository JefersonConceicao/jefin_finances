const { htmlLoading, showMessagesValidator, color } = require('../Core/AppUsage');
$(() => {
    habilitaEventos()
});

const habilitaEventos = () => {
    $("#profileUpdate").on("submit", function(e){
        e.preventDefault();
        formProfile()
    });

    $("#changePassword").on("submit", function(e){
        e.preventDefault()
        formChangePassword()
    })
}

const formProfile = () => {
    const form = "#profileUpdate"; 
    const url = '/users/profileUpdate';
    const type = 'PUT';

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
            }); 

            $(".error_feedback").html("");
            $('.is-invalid').removeClass("is-invalid")
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

const formChangePassword = () => {
    const form = "#changePassword"; 
    const url = '/users/changePassword';
    const type = 'PUT';

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
            }); 

            $(".error_feedback").html("");
            $('.is-invalid').removeClass("is-invalid")
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
    })
}
module.exports = {
    habilitaEventos,
}