//LIBS
window.Swal = require('sweetalert2');

//MODULES
window.AppRegister = require('./Auth/AppRegister');
window.AppAuth = require('./Auth/AppAuth');
window.AppUsage = require('./Core/AppUsage');
window.AppUsers = require('./Logged/AppUsers');
window.AppProventos = require('./Logged/AppProventos');
window.AppDespesas = require('./Logged/AppDespesas');
window.AppTipoDespesas = require('./Logged/AppTipoDespesas');
window.AppLancamentos = require('./Logged/AppLancamentos');
window.AppDashboard = require('./Logged/AppDashboard');
window.AppProfile = require('./Logged/AppProfile');
window.AppForgotPassword = require('./Auth/AppForgotPassword');
window.AppDividas = require('./Logged/AppDividas');

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $("meta[name='csrf-token']").attr("content")
    }
})