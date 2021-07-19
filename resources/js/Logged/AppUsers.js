const { loadModal, loadingContent } = require('../Core/AppUsage');

$(() => {
    habilitaEventos()
    habilitaBotoes()
});

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
    
        AppUsage.loadModal(url, function(){});
    })  
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