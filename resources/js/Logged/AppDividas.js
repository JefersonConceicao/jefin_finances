const { 
    loadingContent, 
    htmlLoading, 
    loadModal, 
    showMessagesValidator, 
    color, 
    optionsSwalDelete,
    deleteRowForGrid,
} = require('../Core/AppUsage');

$(() => {
    habilitaBotoes()
    habilitaEventos()
});

const modalOjbect = "#nivel1";
const grid = "#gridDebts";
const habilitaEventos = () => {
    $("#formFilterDividas").on("submit", function(e){
        e.preventDefault();
        getFilter();
    })
}

const habilitaBotoes = () => {
    $("#addDebt").on("click", function(){
        const url = '/dividas/create';
        loadModal(url, function(){
            $("#formAddDebt").on("submit", function(e){
                e.preventDefault()
                formDebts();
            });

            configInModal();
        });
    });

    $("#gridDebts  table > tbody > tr").on("click", function(e){
        if(e.target.tagName != "TD") return;
        e.preventDefault();

        const id = $(this).attr("key");
        const url = `/dividas/show/${id}`
        loadModal(url)
    })  

    $(".payDebt").on("click", function(){
        const id = $(this).attr("id");
        payDebts(id, $(this));
    });

    $(".deleteDebt").on("click", function(){
        const url = `/dividas/delete/${$(this).attr("id")}`;
        Swal.fire(optionsSwalDelete).then(result => !!result.isConfirmed && deleteRowForGrid(url, getFilter()))
    })
}   

const getFilter = () => {
    const form = "#formFilterDividas";
    const url = '/dividas';

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
            habilitaBotoes(); 
        },
    });
}

const formDebts = id => {
    const url = typeof id === "undefined" ? '/dividas/store' : `/dividas/update/${id}`
    const form = typeof id === "undefined" ? '#formAddDebt' : '#formUpdateDebt'
    const type = typeof id === "undefined" ? 'POST' : 'PUT';

    $.ajax({
        type,
        url,
        data: $(form).serialize(),
        dataType: "JSON",
        beforeSend:function(){
            $(form + " .btn-primary").prop('disabled', true).html(htmlLoading);
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

            $(modalOjbect).modal('hide');
            getFilter();
        },
        error:function(jqXHR, textStatus, error){
            if(jqXHR.responseJSON.errors){
                showMessagesValidator(form, jqXHR.responseJSON.errors);
            }
        },
        complete:function(){
            $(form + " .btn-primary").prop('disabled', false).html(`Salvar`);
        }
    });
}

const payDebts = (id, element) => {
    const url = `/dividas/payDebt/${id}`

    $.ajax({
        type: "PUT",
        url,
        dataType: "JSON",
        beforeSend:function(){
            element.prop('disabled', true).html(`<i class="fa fa-spinner fa-spin"> </i>`);
        },              
        success: function (response) {
            Swal.fire({
                toast:true,
                position: 'bottom-left',
                title: `<h5 style="color:white"> ${response.msg} </h5>`,
                icon: !response.error ? 'success' : 'error',
                showConfirmButton: false,
                timer:3000,
                background: !!response.error ? 'red' : color().default,
            });   
        },
        error:function(){
           Swal.fire({
                toast:true,
                position: 'bottom-left',
                title: `<h5 style="color:white"> Ocorreu um erro interno, tente de novo </h5>`,
                icon: 'error',
                showConfirmButton: false,
                timer:3000,
                background: response.error ? 'red' : color().default,
           });
        },          
        complete:function(){
            getFilter();
        }
    });
}


module.exports = {
    habilitaBotoes,
    habilitaEventos,
}