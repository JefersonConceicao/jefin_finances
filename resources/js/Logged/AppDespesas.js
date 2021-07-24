const { 
    loadingContent, 
    loadModal, 
    htmlLoading, 
    color,
    optionsSwalDelete, 
    deleteRowForGrid
} = require('../Core/AppUsage');

$(() =>{
    habilitaBotoes()
    habilitaEventos()
})

const habilitaEventos = () => {

}

const habilitaBotoes = () => {
    $("#addDespesa").on("click", function(){
        const url = '/despesas/create';

        loadModal(url, function(){

        });
    }); 
}   

module.exports = {
    habilitaBotoes,
    habilitaEventos
}