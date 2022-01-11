window.ajaxLoader = function (status) {
    const container = '<div id="ajax-loader" class="ajax-loader"><div class="ajax-loader--inner"><i class="fas fa-spinner fa-spin"></i></div></div>'
    if(status) {
        document.body.innerHTML += container;
    } else {
        const elem = document.getElementById('ajax-loader');
        elem.remove();
    }
}

