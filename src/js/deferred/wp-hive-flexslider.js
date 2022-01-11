import 'flexslider';

jQuery(window).on('load', function() {
    ph_init_slideshow();
});

// Resize flexsider image to prevent images showing as incorrect height when lazy loading
jQuery('#slider.flexslider .slides img').on('load', function(){
    setTimeout(function() { jQuery(window).trigger('resize'); }, 500);
});
jQuery('#carousel.thumbnails .slides img').on('load', function(){
    setTimeout(function() { jQuery(window).trigger('resize'); }, 500);
});

jQuery(window).on('resize', function() {
    // set height of all thumbnails to be the same (i.e. height of the first one)
    jQuery('#carousel.thumbnails .slides img').css('height', 'auto');
    var thumbnail_height = jQuery('#carousel.thumbnails .slides img:eq(0)').height();
    jQuery('#carousel.thumbnails .slides img').each(function()
    {
        jQuery(this).height(thumbnail_height);
    });
});

function ph_init_slideshow()
{
    // The slider being synced must be initialized first
    jQuery('#carousel').flexslider({
        animation: "slide",
        controlNav: false,
        animationLoop: false,
        slideshow: false,
        itemWidth: 170,
        itemMargin: 30,
        asNavFor: '#slider'
    });

    jQuery('#slider').flexslider({
        animation: "slide",
        controlNav: false,
        animationLoop: false,
        slideshow: false,
        //sync: "#carousel",
        smoothHeight: true,
        initDelay: 0,
        touch: true,
        start: function(slider) { // fires when the slider loads the first slide
            var slide_count = slider.count - 1;

            $(slider)
                .find('img.lazy:eq(0)')
                .each(function() {
                    var src = $(this).attr('data-src');
                    $(this).attr('src', src).removeAttr('data-src');
                });
        },
        before: function (slider) { // fires asynchronously with each slider animation
            var slides = slider.slides,
                index = slider.animatingTo,
                $slide = $(slides[index]),
                $img = $slide.find('img[data-src]'),
                current = index,
                nxt_slide = current + 1,
                prev_slide = current - 1;

            $slide
                .parent()
                .find('img.lazy:eq(' + current + '), img.lazy:eq(' + prev_slide + '), img.lazy:eq(' + nxt_slide + ')')
                .each(function () {
                    var src = $(this).attr('data-src');
                    $(this).attr('src', src).removeAttr('data-src');
                });
        }
    });
}
