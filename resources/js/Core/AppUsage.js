$(() => initialize());

const initialize = () => {
    setActive()
    eventsHelper();
}

const color = () => {
    return {
        default: "#25396f",
    }
}

const htmlLoading = `<i style="font-size:20px;" class="fas fa-circle-notch fa-spin">  </i> carregando...`;

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

const loadModal = (url, callback = null) => {
    const modalElement = $("#nivel1");

    modalElement.modal('show')
    modalElement.find(".modal-content").load(`${url} >`, function(){
        if(!!callback){
            callback();
        }
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

module.exports = {
    htmlLoading,
    loadingContent,
    showMessagesValidator,
    color,
    loadModal,
}