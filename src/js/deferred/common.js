import 'bootstrap';
import 'owl.carousel';
/*import 'aos'*/
import AOS from 'aos';

var SA_Common = SA_Common || {};

(function($){
    SA_Common = function () {
        var $owl_slider;

        var element = {
            ctaCarousel: '[data-carousel="cta"]',
            featuredPropertiesCarousel: '.propertyhive-featured-properties-shortcode > .properties',
            galleryCarousel: '[data-gallery-carousel]',
            galleryThumbnailCarousel: '[data-gallery-thumbnail-carousel]'
        };

        var action = {
            branchContactPopup: '[data-action="branchContact"]',
            branchContactForm: '[data-form="branchContact"]',
            mobileMenuLink: 'a[href="#mobile-menu"]',
            mobileArrangeViewing: '[data-action="mobileArrangeViewingToggle"]',
            mobileToggleCategories: '.wp-block-group.categories-list > .wp-block-group__inner-container > h5',
            mobileToggleBranchContacts: '[data-action="mobileToggleBranchContacts"]',
            mobileTogglePropertiesFilter: '[data-action="mobileTogglePropertiesFilter"]',
        };

        return {
            init: function () {
                SA_Common.events();
                SA_Common.initCtaCarousel();
                SA_Common.initFeaturedPropertiesCarousel();
                //SA_Common.initGalleryCarousel();
                AOS.init();
                SA_Common.initKeywordAutocomplete();

                $('select[name="sortby"]').on('change', function() {
                    $(this).closest('form').submit();
                });
            },

            events: function () {
                $(document)
                    .on('click', action.branchContactPopup, SA_Common.branchContactPopup)
                    .on('submit', action.branchContactForm, SA_Common.branchContactSubmit)
                    .on('click', action.mobileMenuLink, SA_Common.toggleMobileMenu)
                    .on('click', action.mobileArrangeViewing, SA_Common.arrangeViewingToggle)
                    .on('click', action.mobileToggleCategories, SA_Common.mobileToggleCategories)
                ;
            },

            toggleMobileMenu: function() {
                var $body = $('body');

                $body.toggleClass('menu-opened');

                return false;
            },

            mobileToggleCategories: function() {
                var $this = $(this);
                var $ul = $this.next();

                if($ul.css('display') == 'none') {
                    $ul.slideDown();
                    $this.parent().addClass('list-opened');
                } else {
                    $ul.slideUp();
                    $this.parent().removeClass('list-opened');
                }
            },

            initCtaCarousel: function () {
                var $carousel = $(element.ctaCarousel);

                if($carousel.length) {
                    $carousel.owlCarousel({
                        items: 1,
                        autoplay: true,
                        autoplayHoverPause: true,
                        loop: true
                    });
                }
            },

            initFeaturedPropertiesCarousel: function() {
                var $carousel = $(element.featuredPropertiesCarousel);

                if($carousel.length) {
                    $carousel
                        .addClass('owl-carousel owl-theme')
                        .owlCarousel({
                            items: 3,
                            autoplay: true,
                            autoplayHoverPause: true,
                            margin: 30,
                            responsive: {
                                0: {
                                    items: 1
                                },
                                767: {
                                    items: 2
                                },
                                991: {
                                    items: 3
                                }
                            }
                        });
                }
            },

            _initGalleryCarousel: function() {
                var sync1 = $(element.galleryCarousel);
                var sync2 = $(element.galleryThumbnailCarousel);

                var thumbnailItemClass = '.owl-item';

                var slides = sync1.owlCarousel({
                    //video:true,
                    //startPosition: 12,
                    items:1,
                    loop:true,
                    margin:10,
                    autoplay:true,
                    autoplayTimeout:6000,
                    autoplayHoverPause:false,
                    nav: false,
                    dots: true
                }).on('changed.owl.carousel', syncPosition);

                function syncPosition(el) {
                    $owl_slider = $(this).data('owl.carousel');
                    var loop = $owl_slider.options.loop;

                    if(loop){
                        var count = el.item.count-1;
                        var current = Math.round(el.item.index - (el.item.count/2) - .5);
                        if(current < 0) {
                            current = count;
                        }
                        if(current > count) {
                            current = 0;
                        }
                    }else{
                        var current = el.item.index;
                    }

                    var owl_thumbnail = sync2.data('owl.carousel');
                    var itemClass = "." + owl_thumbnail.options.itemClass;


                    var thumbnailCurrentItem = sync2
                        .find(itemClass)
                        .removeClass("synced")
                        .eq(current);

                    thumbnailCurrentItem.addClass('synced');

                    if (!thumbnailCurrentItem.hasClass('active')) {
                        var duration = 300;
                        sync2.trigger('to.owl.carousel',[current, duration, true]);
                    }
                }
                var thumbs = sync2.owlCarousel({
                    //startPosition: 12,
                    items:6,
                    loop:false,
                    margin:10,
                    autoplay:false,
                    nav: false,
                    dots: false,
                    onInitialized: function (e) {
                        var thumbnailCurrentItem =  $(e.target).find(thumbnailItemClass).eq(this._current);
                        thumbnailCurrentItem.addClass('synced');
                    },
                })
                    .on('click', thumbnailItemClass, function(e) {
                        e.preventDefault();
                        var duration = 300;
                        var itemIndex =  $(e.target).parents(thumbnailItemClass).index();
                        sync1.trigger('to.owl.carousel',[itemIndex, duration, true]);
                    }).on("changed.owl.carousel", function (el) {
                        var number = el.item.index;
                        $owl_slider = sync1.data('owl.carousel');
                        $owl_slider.to(number, 100, true);
                    });

            },

            initGalleryCarousel: function() {
                /*var $carousel = $(element.galleryCarousel);
                var $carouselThumbnail = $(element.galleryThumbnailCarousel);

                if($carousel.length) {
                    $carousel.owlCarousel({
                        items: 1,
                        dots: false
                    });
                }

                if($carouselThumbnail.length) {
                    $carouselThumbnail.owlCarousel({
                        items: 6,
                        dots: false,
                        nav: false
                    });
                }*/

                /*===*/

                var $carousel = $(element.galleryCarousel);
                var $carouselThumbnail = $(element.galleryThumbnailCarousel);

                var slidesPerPage = 6; //globaly define number of elements per page
                var syncedSecondary = true;

                $carousel.owlCarousel({
                    items: 1,
                    slideSpeed: 2000,
                    nav: true,
                    autoplay: false,
                    dots: false,
                    loop: true,
                    responsiveRefreshRate: 200,
                }).on('changed.owl.carousel', syncPosition);

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
                    }).on('changed.owl.carousel', syncPosition2);

                function syncPosition(el) {
                    //if you set loop to false, you have to restore this next line
                    //var current = el.item.index;

                    //if you disable loop you have to comment this block
                    var count = el.item.count - 1;
                    var current = Math.round(el.item.index - (el.item.count / 2) - .5);

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

                    if (current > end) {
                        $carouselThumbnail.data('owl.carousel').to(current, 100, true);
                    }
                    if (current < start) {
                        $carouselThumbnail.data('owl.carousel').to(current - onscreen, 100, true);
                    }
                }

                function syncPosition2(el) {
                    if (syncedSecondary) {
                        var number = el.item.index;
                        $carousel.data('owl.carousel').to(number, 100, true);
                    }
                }

                $carouselThumbnail.on("click", ".owl-item", function(e) {
                    e.preventDefault();
                    var number = $(this).index();
                    $carousel.data('owl.carousel').to(number, 300, true);
                });

            },

            /*openFancyModal: function (modalId) {
                if(modalId != undefined) {
                    parent.jQuery.fancybox.open({
                        src: modalId
                    });
                } else {
                    alert('Please, set modal ID');
                }
            },*/

            viewAddressMapImage: function (address, wrap_css_class) {
                if(address != '') {
                    wrap_css_class = wrap_css_class ? wrap_css_class : '';

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
            },

            viewGoogleMap: function (id) {
                parent.jQuery.fancybox.open({
                    src: id,
                    type: 'inline',
                    touch: false,
                    momentum: false,
                    /*opts : {
                        afterShow : function( instance, current ) {
                            google.maps.event.trigger(map, 'resize');
                        }
                    }*/
                });
            },

            branchContactPopup: function () {
                var $that = $(this);
                var id = $that.data('id');

                $.ajax({
                    method: "POST",
                    url: crowdAjax,
                    beforeLoad: ajaxLoader(true),
                    data: {
                        action: 'branch_contact_popup',
                        id: id
                    },
                    success: function (response) {
                        parent.jQuery.fancybox.open({
                            src: response,
                            type: 'inline',
                            touch: false,
                            momentum: false
                        });

                        ajaxLoader(false);
                    }
                });
            },

            branchContactSubmit: function (e) {
                var data = new FormData(e.target);

                data.append('action', 'branch_contact_submit');

                $.ajax({
                    url: crowdAjax,
                    method: "POST",
                    data: new URLSearchParams(data).toString(),
                    success: function (response) {
                        var $form = $(e.target);

                        if(response.status) {
                            e.target.reset();

                            if (typeof gtag === 'function') {
                                gtag('event', "Office Property", {
                                    'event_category': 'Office Viewing',
                                    'event_label': 'Submit Form'
                                });
                            }

                            /*if(ewt !== undefined) {
                                ewt.track({name: 'page', type: 'Property Office'});
                            }*/

                            location.href = response.redirect_url;

                            /*$form
                                .hide()
                                .after('<div class="alert alert-success">' + response.message + '</div>')
                            ;*/
                        } else if(response.errors) {

                            $('.invalid-feedback',$form).remove();
                            $('.form-control',$form).removeClass('is-invalid');

                            $.each(response.errors, function (input_name,message) {
                                $('[name="' + input_name + '"]', $form)
                                    .addClass('is-invalid')
                                    .after('<div class="invalid-feedback"><i class="fas fa-times"></i> ' + message + '</div>')
                                ;
                            });
                        } else {
                            alert(response.message);
                        }
                    }
                });

                e.preventDefault();
            },

            arrangeViewingToggle: function () {
                var $this = $(this);
                var anchor = $this.attr('href');
                var closeText = $this.data('close_text');
                var openText = $this.data('open_text');
                var $box = $(anchor);

                if($box.length) {
                    if($box.css('display') == 'none') {
                        $box.slideDown();
                        $this.addClass('box-opened');
                        $('span',$this).text(closeText)
                    } else {
                        $box.slideUp();
                        $this.removeClass('box-opened');
                        $('span',$this).text(openText)
                    }
                }

                return false;
            },

            branchMobileContactsToggle: function (status) {
                var $action = $(action.mobileToggleBranchContacts);

                if($action.length) {
                    $action.off();

                    if (status) {
                        $action.on('click', function () {
                            var $this = $(this);
                            var $box = $this.next();

                            if($box.css('display') == 'none') {
                                $this.addClass('item-opened');
                                $box.slideDown();
                            } else {
                                $this.removeClass('item-opened');
                                $box.slideUp();
                            }
                        });
                    }
                }
            },

            propertiesMobileFilterToggle: function (status) {
                var $action = $(action.mobileTogglePropertiesFilter);

                if($action.length) {
                    $action.off();

                    if (status) {
                        $action.on('click', function () {
                            var $this = $(this);
                            var $box = $($this.attr('href'));

                            if($box.css('display') == 'none') {
                                $this.addClass('item-opened');
                                $box.slideDown();
                            } else {
                                $this.removeClass('item-opened');
                                $box.slideUp();
                            }

                            return false;
                        });
                    }
                }
            },

            initKeywordAutocomplete: function () {
                var inputPostCode = document.getElementById('address_keyword');
                var inputPostCodeLat = document.getElementById('address_lat');
                var inputPostCodeLng = document.getElementById('address_lng');

                var autoPostcode = new google.maps.places.Autocomplete(inputPostCode, {
                    types: ['(regions)'],

                    fields: ['ALL'],
                    componentRestrictions: { country: "gb" }
                });

                google.maps.event.addListener(autoPostcode, 'place_changed', function() {
                    const place = autoPostcode.getPlace();

                    /*console.log(place);
                    console.log(place.geometry.location.lat());
                    console.log(place.geometry.location.lng());*/

                    inputPostCodeLat.value = place.geometry.location.lat();
                    inputPostCodeLng.value = place.geometry.location.lng();

                    inputPostCode.value = getPostCode(place);
                })

                function getPostCode(place){
                    for (var i = 0; i < place.address_components.length; i++) {
                        console.log(place.address_components);
                        for (var j = 0; j < place.address_components[i].types.length; j++) {
                            /*console.log(place.address_components[i].types[j]);
                            if (place.address_components[i].types[j] == "postal_code" || place.address_components[i].types[j] == "locality") {*/
                                return place.address_components[i].long_name;
                            //}
                        }
                    }
                }
            }
        }
    }();

    $(window).on('load', SA_Common.init);
    $(window).on('load resize', function () {
        if (window.matchMedia("(max-width: 991px)").matches) {
            SA_Common.propertiesMobileFilterToggle(true);
        } else {
            SA_Common.propertiesMobileFilterToggle(false);
        }

        if (window.matchMedia("(max-width: 767px)").matches) {
            SA_Common.branchMobileContactsToggle(true);
        } else {
            SA_Common.branchMobileContactsToggle(false);
        }
    });
})(jQuery);

window.SA_Common = SA_Common;
