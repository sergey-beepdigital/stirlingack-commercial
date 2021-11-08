import 'bootstrap';
import 'owl.carousel';

var SA_Common = SA_Common || {};

(function($){
    SA_Common = function () {
        var element = {
            ctaCarousel: '[data-carousel="cta"]'
        };

        var action = {

        };

        return {
            init: function () {
                SA_Common.events();
                SA_Common.initCtaCarousel();
            },

            events: function () {

            },

            initCtaCarousel: function () {
                var $carousel = $(element.ctaCarousel);

                if($carousel.length) {
                    $carousel.owlCarousel({
                        items: 1
                    });
                }
            },

            openFancyModal: function (modalId) {
                if(modalId != undefined) {
                    parent.jQuery.fancybox.open({
                        src: modalId
                    });
                } else {
                    alert('Please, set modal ID');
                }
            }
        }
    }();

    $(window).on('load', SA_Common.init);
})(jQuery);

window.SA_Common = SA_Common;
