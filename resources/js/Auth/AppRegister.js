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
            console.log(response);
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