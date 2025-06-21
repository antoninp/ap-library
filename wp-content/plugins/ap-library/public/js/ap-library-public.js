(function( $ ) {
    'use strict';

    /**
     * All of the code for your public-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

    document.addEventListener('DOMContentLoaded', function() {
        const toTop = document.querySelector('.back-to-top');
        if (!toTop) return;
        window.addEventListener("scroll", function() {
            if(window.pageYOffset > 100) {
                toTop.classList.add('active-to-top');
            } else {
                toTop.classList.remove('active-to-top');
            }
        });
        toTop.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({top: 0, behavior: 'smooth'});
        });
    });

})( jQuery );
