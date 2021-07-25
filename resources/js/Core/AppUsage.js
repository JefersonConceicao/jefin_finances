$(() => initialize());

const initialize = () => {
    setActive()
    eventsHelper();
    loadLibs();
}

const color = () => {
    return {
        default: "#25396f",
        danger: "#dc3545",
    }
}

const htmlLoading = `<i style="font-size:20px;" class="fas fa-circle-notch fa-spin">  </i> carregando...`;

const optionsSwalDelete = {
    icon: 'warning',
    title: 'Deseja realmente excluír o registro ?',
    text: 'Esta ação é irreversível!',
    showConfirmButton: true,
    showCancelButton: true,
    confirmButtonText: 'Sim, quero excluir!',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: color().danger
}


const showMessagesValidator = (form, errors) => {
    $(form).find('.is-invalid').removeClass("is-invalid");
    $(".error_feedback").html("");
    
    if($(form).length == 0 || !errors){
        return;
    }

    const nameInputs = Object.keys(errors);
    for (let i = 0; i < nameInputs.length; i++) {
        const fieldError = $(form +` [name="${nameInputs[i]}"]`);
 
        errors[nameInputs[i]].forEach(value => {
            fieldError.addClass('is-invalid');
            fieldError.parent().find('.error_feedback').html(`
                <p class="required"> ${value} </p> 
            `)
        })
    }
}

const setActive = () => {
   const sidebarLink = $(".sidebar-link");

    sidebarLink.map((element, value )=> {
       if($(value).attr("href") == window.location.pathname){
           $(value).parent().addClass("active");
       }

       //VERIFICA SE EXISTE SUB-MENU
       if($(value).parent().hasClass('has-sub')){
           const sidebarItem = $(value).parent();
           const subItens = sidebarItem.find('.submenu > .submenu-item > a');
           
            Array.from(subItens).forEach(element => {
                if($(element).attr("href") == window.location.pathname){
                    sidebarItem.addClass("active")
                    sidebarItem.find('.submenu').css('display', 'block');
                    $(element).parent().addClass("active");
                }
            })
       }
    })
}

const eventsHelper = () => {
    $('.table-responsive').on('shown.bs.dropdown', function (e) {
        let $table = $(this)
        let $menu = $(e.target).find('.dropdown-menu')
        let tableOffsetHeight = $table.offset().top + $table.height()
        let menuOffsetHeight = $menu.offset().top + $menu.outerHeight(true);

        if (menuOffsetHeight > tableOffsetHeight){
            $table.css("padding-bottom", menuOffsetHeight - tableOffsetHeight);
        }
    });
    
    $('.table-responsive').on('hide.bs.dropdown', function () {
        $(this).css("padding-bottom", "");
        $(this).css("overflow", "auto")
    })
}

const loadLibs = () => {
    configDateTimePicker()
    configMasks()
}

const configDateTimePicker = () => {
    $.datetimepicker.setLocale('pt-BR');
    $('.datepicker').datetimepicker({
        timepicker: false,
        format: 'd/m/Y'
    });
}

const configMasks = () => {
    $(".decimalValue").mask('#.##0,00', {
        reverse: true,
    })
}

const loadModal = (url, callback = null) => {
    const modalElement = $("#nivel1");

    modalElement.modal('show');
    modalElement.find(".modal-content").load(`${url} >`, function(){

        if(!!callback){
            callback();
        }

        loadLibs()
    });
}

const loadingContent = element => {
    if(!!element){
        $(element).closest(element).html(`
            <div class="alert alert-primary text-center">  
                <div class="spinner-border text-light"> </div>
            </div>
        `)
    }
}

const deleteRowForGrid = (url, onSuccess = null, onError = null) => {
    $.ajax({
        type: "DELETE",
        url,
        dataType: "JSON",
        success: function (response) {
            Swal.fire({
                toast:true,
                position: 'bottom-left',
                title: `<h5 style="color:white"> ${response.msg} </h5>`,
                icon: !response.error ? 'success' : 'error',
                showConfirmButton: false,
                timer:3500,
                background: response.error ? color().danger : color().default,
            }); 

            if(!!onSuccess){
                onSuccess();
            }
        },
        error:function(jqXHR, textStatus, error){
            Swal.fire({
                toast:true,
                position: 'bottom-left',
                title: `<h5 style="color:white"> 
                           Não foi possível excluir, pois o registro já está sendo utilizado.
                        </h5>`,
                icon: 'error',
                showConfirmButton: false,
                timer:3500,
                background: color().danger,
            }); 

            if(!!onError){
                onError();
            }
        },
    });
}


module.exports = {
    htmlLoading,
    loadingContent,
    showMessagesValidator,
    color,
    loadModal,
    optionsSwalDelete,
    deleteRowForGrid,
    loadLibs,
}