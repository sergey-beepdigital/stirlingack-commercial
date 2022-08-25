var SA_PropertyDetail = SA_PropertyDetail || {};

(function($){
    SA_PropertyDetail = function () {
        var element = {

        };

        var action = {
            openPropertyTab: '[data-open-property-tab]',
            openVirtualTour: 'a.action-virtual-tour'
        };

        return {
            init: function () {
                SA_PropertyDetail.events();
            },

            events: function () {
                $(document)
                    .on('click', action.openPropertyTab, SA_PropertyDetail.openPropertyTab)
                    .on('click', action.openVirtualTour, SA_PropertyDetail.openVirtualTour)

                ;
            },

            openPropertyTab: function (e) {
                var $this = $(this);
                var $tabAnchor = $this.attr('href');
                var $tabLink = $('.property-detail-tabs a[href="'+$tabAnchor+'"]');
                var $header = $('#header');

                $tabLink.click();

                $('html, body').animate({
                    scrollTop: $tabLink.offset().top - $header.innerHeight()
                }, 2000);

                return false;
            },

            openVirtualTour: function () {
                var $this = $(this);
                var href = $this.attr('href');

                parent.jQuery.fancybox.open({
                    src: href,
                    type: 'iframe'
                });

                return false;
            }
        }
    }();

    $(window).on('load', SA_PropertyDetail.init);
})(jQuery);

window.SA_PropertyDetail = SA_PropertyDetail;
