window.AppRegister = require('./Auth/AppRegister');
window.AppAuth = require('./Auth/AppAuth');
window.AppUsage = require('./Core/AppUsage');

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $("meta[name='csrf-token']").attr("content")
    }
})