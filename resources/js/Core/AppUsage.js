const color = () => {
    return {
        default: "#25396f",
    }
}

const showMessagesValidator = (form, errors) => {
    $(form).find('.is-invalid').removeClass("is-invalid");
    $(".error_feedback").html("");
    
    if($(form).length == 0 || !errors){
        return;
    }

    const nameInputs = Object.keys(errors);
    for (let i = 0; i < nameInputs.length; i++) {
        const fieldError = $(`[name="${nameInputs[i]}"]`);

        errors[nameInputs[i]].forEach(value => {
            fieldError.addClass('is-invalid');
            fieldError.parent().find('.error_feedback').html(`
                <p class="required"> ${value} </p> 
            `)
        })
    }
}

const optionSwalDelete = {}

module.exports = {
    showMessagesValidator,
    color,
}