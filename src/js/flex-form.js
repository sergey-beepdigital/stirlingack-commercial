/* exported flexForm */
var flexForm = (function(){
    'use strict';

    var flexForms = [],
        form,
        formID,
        fields = {},
        submitButton,
        loadingBar,
        timeline,
        statusMsg,
        anchor;

    function formSubmitHandler(e) {
        e.preventDefault();

        form = e.currentTarget;
        formID = form.dataset.formId;
        [].forEach.call(form.elements, function(el) {
            if (el.name) {
                fields[el.name] = el.value;
            }
        });

        submitButton = form.querySelector('[type="submit"]');
        statusMsg = form.querySelector('[data-status-msg]');
        submitButton.style.width = submitButton.offsetWidth + "px";
        submitButton.innerHTML = '<span class="loading-bar"></span>' + submitButton.dataset.sendingMsg + '...';
        submitButton.disabled = true;
        submitButton.classList.add('sending');
        loadingBar = submitButton.querySelector('.loading-bar');

        ajax().post(crowdAjax, {
            'action'      : 'flex_form',
            'form_id'     : formID,
            'fields'      : JSON.stringify(fields)
        }).then(function(r) {
            statusMsg.innerHTML = r.message;

            if (r.download) {
                anchor = document.createElement('a');
                anchor.href = r.download;
				anchor.setAttribute("download", true);
				anchor.style.display = "none";
                document.body.appendChild(anchor);
                anchor.click();
                anchor.remove();
            }
        });
    }

    function init() {
        flexForms = [].slice.call(document.querySelectorAll('[data-flex-form]'));

        flexForms.forEach(function(ele){
            ele.addEventListener('submit', formSubmitHandler, false);
        });
    }

    document.addEventListener('DOMContentLoaded', init, false);

    return {
        init: init
    }
}());
