//LIBS
window.Swal = require('sweetalert2');

//MODULES
window.AppRegister = require('./Auth/AppRegister');
window.AppAuth = require('./Auth/AppAuth');
window.AppUsage = require('./Core/AppUsage');
window.AppTema = require('./Core/AppTema');
window.AppUsers = require('./Logged/AppUsers');
window.AppProventos = require('./Logged/AppProventos');
window.AppDespesas = require('./Logged/AppDespesas');
window.AppDespesasFixas = require('./Logged/AppDespesasFixas');
window.AppProventosFixos = require('./Logged/AppProventosFixos');
window.AppLancamentos = require('./Logged/AppLancamentos');
window.AppProjecao = require('./Logged/AppProjecao');
window.AppProfile = require('./Logged/AppProfile');
window.AppForgotPassword = require('./Auth/AppForgotPassword');

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $("meta[name='csrf-token']").attr("content")
    }
})