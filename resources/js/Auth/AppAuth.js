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

const authWithGoogle = googleUser => {
    if(Object.keys(googleUser).length == 0) return; 

    const profile = googleUser.getBasicProfile();
    const user = {
        name: profile.getName(),
        last_name: profile.getFamilyName(),
        email: profile.getEmail(),
    }

    if(!!user){
        $.post("/loginWithGoogle", user,
            function (response, textStatus, jqXHR) {
               if(response.error == false && response.msg === "Autenticado com sucesso"){
                   window.location.href = '/home';
               }
            },
            "JSON"
        );
    }   
}

module.exports = {
    habilitaBotoes,
    habilitaEventos,
    authWithGoogle,
}