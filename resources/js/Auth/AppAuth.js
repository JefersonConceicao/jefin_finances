const { default: Swal } = require('sweetalert2');

const {
    htmlLoading,
    showMessagesValidator,
    color
} = require('../Core/AppUsage');

$(function(){
    initAuth2();
    habilitaEventos()
    habilitaBotoes()
});

const habilitaBotoes = () => {}
const habilitaEventos = () => {
    $("#formLoginUser").on("submit", function(e){
        e.preventDefault()
        formLoginUser()
    });

    $("#logout").on("click", function(e){
        e.preventDefault()

        Swal.fire({
            icon: 'warning',
            title: 'Tem certeza que deseja sair?',
            showConfirmButton: true,
            showCancelButton: true,
            confirmButtonText: 'Sim, quero sair do sistema.',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: color().danger
        }).then(result => {
            if(result.isConfirmed){
                $.get($(this).attr("href"), function (response, textStatus, jqXHR) {
                    if(jqXHR.status == 200 && response.logout){
                        logoutUserGoogle()
                    }
                },
                "JSON"
            );
            }
        })
    })
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

const initAuth2 = () => {
    gapi.load('auth2', function(){
            auth2 = gapi.auth2.init({
            client_id: "23951275285-j7sp1aucu6lhegsvjfd1fni74bct3uip.apps.googleusercontent.com",
            cookiepolicy: 'localhost'
        })
        .then((result) => {
            result.attachClickHandler(document.getElementById('loginWithGoogle'), {}, function(googleUser){
                authWithGoogle(googleUser);
            })
        })
    })
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


const logoutUserGoogle = () => {
    const authInstance = gapi.auth2.getAuthInstance();

    authInstance.signOut().then(() => {
        console.log("logout");
    })

    window.location.href = '/';
}

module.exports = {
    habilitaBotoes,
    habilitaEventos,
    authWithGoogle,
}
