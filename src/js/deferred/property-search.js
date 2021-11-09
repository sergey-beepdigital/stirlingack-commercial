var SA_PropertySearch = SA_PropertySearch || {};

(function($){
    SA_PropertySearch = function () {
        var element = {

        };

        var action = {
            changeDepartment: '[data-change-department]'
        };

        return {
            init: function () {
                SA_PropertySearch.events();
            },

            events: function () {
                $(document)
                    .on('click', action.changeDepartment, SA_PropertySearch.changeDepartment)

                ;
            },

            changeDepartment: function () {
                var $this = $(this);
                var $form = $this.closest('form');
                var $nav = $this.closest('ul');
                var departmentValue = $this.data('department');

                $('li',$nav).removeClass('active');
                $this.parent().addClass('active');
                $('#department_' + departmentValue).click();

                if(departmentValue == 'residential-sales'){
                    $('#new-homes-checkbox').parent().show();
                } else {
                    $('#new-homes-checkbox').parent().hide();
                    $('#new-homes-checkbox').prop('checked',false);
                }

                return false;
            }
        }
    }();

    $(window).on('load', SA_PropertySearch.init);
})(jQuery);

window.SA_PropertySearch = SA_PropertySearch;
