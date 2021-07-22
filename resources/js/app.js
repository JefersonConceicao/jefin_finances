//LIBS
window.Swal = require('sweetalert2');

window.AppRegister = require('./Auth/AppRegister');
window.AppAuth = require('./Auth/AppAuth');
window.AppUsage = require('./Core/AppUsage');
window.AppUsers = require('./Logged/AppUsers');
window.AppProventos = require('./Logged/AppProventos');

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $("meta[name='csrf-token']").attr("content")
    }
})