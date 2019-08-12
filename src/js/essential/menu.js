
var navbar = (function(){
    'use strict';

    var navbarEle,
        headroom,
        burgerBtn,
        pageBody;

    function isNavVisible(nav) {
        return ( pageBody.classList.contains('menu-active') ? true : false );
    }

    function init() {
        navbarEle = document.querySelector('.nav-main');
        burgerBtn = document.querySelector('.menu-btn');
        pageBody = document.querySelector('body');

        burgerBtn.addEventListener('click', function(event){
            event.preventDefault();
            pageBody.classList.toggle('menu-active');
        })

        var media = window.matchMedia('(min-width: 960px)');
        media.addListener(function(data) {
            if ( data.matches ) {
                pageBody.classList.remove('menu-active');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', init, false);

    return {
        init: init
    }
}());
