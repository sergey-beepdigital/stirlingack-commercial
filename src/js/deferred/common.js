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
            },

            viewAddressMapImage: function (address, wrap_css_class) {
                if(address != '') {
                    var wrap_css_class = wrap_css_class ? wrap_css_class : '';
                    var map_url = 'https://maps.googleapis.com/maps/api/staticmap';
                    var map_params = $.param({
                        size: '512x512',
                        markers: 'icon:' + sg_config.google_maps.marker_url + '|' + address,
                        key: sg_config.google_maps.api_key,
                        scale: '2'
                    });

                    parent.jQuery.fancybox.open({
                        src: '<div class="google-map-image-wrap ' + wrap_css_class + '"><img src="' + map_url + '?' + map_params + '"></div>',
                        type: 'html'
                    });
                } else {
                    alert('Address is empty')
                }
            }
        }
    }();

    $(window).on('load', SA_Common.init);
})(jQuery);

window.SA_Common = SA_Common;
