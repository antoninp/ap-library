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

    });

})( jQuery );
