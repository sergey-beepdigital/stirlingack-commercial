import 'owl.carousel';

var SA_GalleryCarousel = SA_GalleryCarousel || {};

(function($){
    SA_GalleryCarousel = function () {
        var $carousel;
        var $carouselThumbnail;
        var syncedSecondary = true;
        var slidesPerPage = 6;

        var element = {
            galleryCarousel: '[data-gallery-carousel]',
            galleryThumbnailCarousel: '[data-gallery-thumbnail-carousel]'
        };

        return {
            init: function () {
                /*$('#carousel').flexslider({
                    animation: "slide",
                    controlNav: false,
                    animationLoop: false,
                    slideshow: false,
                    itemWidth: 210,
                    itemMargin: 5,
                    asNavFor: '#slider'
                });

                $('#slider').flexslider({
                    animation: "slide",
                    controlNav: false,
                    animationLoop: false,
                    slideshow: false,
                    sync: "#carousel"
                });*/

                $carousel = $(element.galleryCarousel);
                $carouselThumbnail = $(element.galleryThumbnailCarousel);

                $carousel.owlCarousel({
                    items: 1,
                    slideSpeed: 2000,
                    nav: true,
                    autoplay: false,
                    dots: false,
                    loop: true,
                    responsiveRefreshRate: 200,
                }).on('changed.owl.carousel', SA_GalleryCarousel.syncPosition);

                $carouselThumbnail
                    .on('initialized.owl.carousel', function() {
                        $carouselThumbnail.find(".owl-item").eq(0).addClass("current");
                    })
                    .owlCarousel({
                        items: slidesPerPage,
                        dots: false,
                        nav: false,
                        smartSpeed: 200,
                        slideSpeed: 500,
                        //slideBy: slidesPerPage, //alternatively you can slide by 1, this way the active slide will stick to the first item in the second carousel
                        responsiveRefreshRate: 100
                    }).on('changed.owl.carousel', SA_GalleryCarousel.syncPosition2);

                $carouselThumbnail.on("click", ".owl-item", function(e) {
                    e.preventDefault();
                    var number = $(this).index();
                    $carousel.data('owl.carousel').to(number, 300, true);
                });
            },

            syncPosition: function(el) {
                console.log(el);
                console.log(el.item);

                //if you set loop to false, you have to restore this next line
                //var current = el.item.index;

                //if you disable loop you have to comment this block
                var count = el.item.count - 1;
                var current = Math.round(el.item.index - (el.item.count / 2) - .5);

                console.log(count);
                console.log(current);

                if (current < 0) {
                    current = count;
                }
                if (current > count) {
                    current = 0;
                }

                //end block

                $carouselThumbnail
                    .find(".owl-item")
                    .removeClass("current")
                    .eq(current)
                    .addClass("current");

                var onscreen = $carouselThumbnail.find('.owl-item.active').length - 1;
                var start = $carouselThumbnail.find('.owl-item.active').first().index();
                var end = $carouselThumbnail.find('.owl-item.active').last().index();

                console.log('count: ' + count);
                console.log('current: ' + current);
                console.log('onscreen: ' + onscreen);
                console.log('start: ' + start);
                console.log('end: ' + end);

                /*
count: 12
current: 7
onscreen: 5
start: 6
end: 11
                 */

                console.log($carouselThumbnail.data('owl.carousel'));

                if (current > end) {
                    console.log('current > end');
                    $carouselThumbnail.data('owl.carousel').to(current, 100, true);
                }
                if (current < start) {
                    console.log('current < start');
                    $carouselThumbnail.data('owl.carousel').to(current - onscreen, 100, true);
                }
            },

            syncPosition2: function(el) {
                if (syncedSecondary) {
                    var number = el.item.index;
                    $carousel.data('owl.carousel').to(number, 100, true);
                }
            }
        }
    }();

    $(window).on('load', SA_GalleryCarousel.init);
})(jQuery);
