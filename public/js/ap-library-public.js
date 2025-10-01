(function( $ ) {
    'use strict';

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
