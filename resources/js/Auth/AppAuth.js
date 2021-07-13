$(function(){
    habilitaBotoes()
    habilitaEventos()
});

const habilitaBotoes = function(){}

const habilitaEventos = function(){
    $("#formAuthRegister").on("submit", function(e){
        e.preventDefault();
        
        $(this).find('.btn-primary')
            .prop("disabled", true)
            .html(`<div class="text-center"> 
                    <i class="fa fa-circle-notch fa-spin"> </i>
                </div>
            `)
    })
}   

module.exports = {
    habilitaBotoes,
    habilitaEventos
}