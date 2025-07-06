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

    $(function(){

        // Back to top button logic
        var $toTop = $('.back-to-top');
        if ($toTop.length) {
            $(window).on('scroll', function() {
                if(window.pageYOffset > 100) {
                    $toTop.addClass('active-to-top');
                } else {
                    $toTop.removeClass('active-to-top');
                }
            });
            $toTop.on('click', function(e) {
                e.preventDefault();
                window.scrollTo({top: 0, behavior: 'smooth'});
            });
        }

        // Meow Gallery single image height fix
        $('.wp-block-meow-gallery-gallery .mg-images').each(function(){
            var $container = $(this);
            var $images = $container.find('img');
            if ($images.length === 1) {
                $container.css('height', 'auto');
                $images.css({
                    'max-height': '400px',
                    'height': 'auto',
                    'width': 'auto',
                    'object-fit': 'contain',
                    'margin-left': 'auto',
                    'margin-right': 'auto',
                    'display': 'block'
                });
            }
        });
        $('.wp-block-meow-gallery-gallery .mg-images').each(function(){
            var $container = $(this);
            var $rows = $container.find('.mgl-row');
            if ($rows.length === 1) {
                $rows.css({
                    'height': 'auto',
                    'max-height': '400px'
                });
            }
        });


    });

})( jQuery );
