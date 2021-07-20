const { loadModal, 
        loadingContent, 
        htmlLoading,
        color,
    } = require('../Core/AppUsage');

$(() => {
    habilitaEventos()
    habilitaBotoes()
});

const modalObject = "#nivel1";
const grid = "#gridUsers";

const habilitaEventos = () => {
    $("#searchFormUsers").on("submit", (e) => {
        e.preventDefault()
        getFilterUsers()
    });
}

const habilitaBotoes = function(){
    $("#addUser").on("click", function(){
        const url = '/users/create';
    
        AppUsage.loadModal(url, function(){
            $("#formAddUser").on("submit", function(e){
                e.preventDefault();
                formUser();
            });
        });
    })  

    $(".btnEditUser").on("click", function(){
        const id = $(this).attr("id");
        const url = '/users/edit/' + id;

        AppUsage.loadModal(url, function(){
            $("#changePassword").on("click", function(){
                const inputPassword = $(modalObject + ' input[name="password"]');
                const inputPasswordConf = $(modalObject + ' input[name="password_confirmation"]');

                if(inputPassword.prop("disabled") && inputPasswordConf.prop("disabled")){
                    inputPassword.prop("disabled", false);
                    inputPasswordConf.prop("disabled", false);
                }else{
                    inputPassword.prop("disabled", true);
                    inputPasswordConf.prop("disabled", true);
                }

            });
        });
    });
}

const formUser = id => {
    const url =  typeof id  === "undefined" ? '/users/store' : '/users/update/' + id;
    const form = typeof id  === "undefined" ? '#formAddUser' : '#formEditUser';
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
            
            getFilterUsers();
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

const getFilterUsers = (urlPaginate) => {
    const url = '/users/';
    const form = "#searchFormUsers";

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

       
        }, 
        complete:function(){
            habilitaBotoes()
        }
    });
}

module.exports = {
    habilitaBotoes,
}