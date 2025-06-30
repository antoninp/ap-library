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

        // Ap Fade Gallery
        $('.ap-fade-gallery').each(function(){
            var $gallery = $(this);
            var $imgs = $gallery.find('img');
            var $left = $gallery.find('.ap-fade-arrow.left');
            var $right = $gallery.find('.ap-fade-arrow.right');
            var $edgeLeft = $gallery.find('.ap-fade-edge-left');
            var $edgeRight = $gallery.find('.ap-fade-edge-right');
            var idx = 0;
            var delay = 4000;
            var auto = $gallery.data('auto') === true || $gallery.data('auto') === 'true';
            var timer = null;
            var lastX = null;
            var arrowTimeout = null;

            function show(idxNew) {
                $imgs.removeClass('active');
                idx = (idxNew + $imgs.length) % $imgs.length;
                $imgs.eq(idx).addClass('active');
            }
            function next() { show(idx + 1); }
            function prev() { show(idx - 1); }

            $left.on('click', function(){ prev(); resetAuto(); });
            $right.on('click', function(){ next(); resetAuto(); });
            $edgeLeft.on('click', function(e){
                prev();
                resetAuto();
                $left.addClass('edge-active');
                $right.removeClass('edge-active');
                scheduleHideArrows();
            });
            $edgeRight.on('click', function(e){
                next();
                resetAuto();
                $right.addClass('edge-active');
                $left.removeClass('edge-active');
                scheduleHideArrows();
            });

            function startAuto() {
                if(auto && $imgs.length > 1) {
                    timer = setInterval(next, delay);
                }
            }
            function resetAuto() {
                if(timer) clearInterval(timer);
                startAuto();
            }

            function isNearArrow(mouseX, arrowX, arrowWidth, threshold) {
                return Math.abs(mouseX - (arrowX + arrowWidth / 2)) < threshold;
            }

            $gallery.on('mousemove', function(e){
                var offset = $gallery.offset();
                var x = e.pageX - offset.left;
                var w = $gallery.width();

                if (lastX !== null) {
                    if (x > lastX + 2) { // moved right
                        $right.addClass('edge-active');
                        $left.removeClass('edge-active');
                    } else if (x < lastX - 2) { // moved left
                        $left.addClass('edge-active');
                        $right.removeClass('edge-active');
                    }
                }
                lastX = x;

                mouseX = x; // Save for hide check
                scheduleHideArrows();
            }).on('mouseleave', function(){
                hideArrows();
                lastX = null;
                mouseX = null;
                if (arrowTimeout) clearTimeout(arrowTimeout);
            });

            var mouseX = null;
            function hideArrows() {
                var threshold = 60; // px
                var leftX = $left.position().left;
                var rightX = $right.position().left;
                var leftW = $left.outerWidth();
                var rightW = $right.outerWidth();

                // Only hide if mouse is not near the visible arrow
                if (
                    ($left.hasClass('edge-active') && mouseX !== null && isNearArrow(mouseX, leftX, leftW, threshold)) ||
                    ($right.hasClass('edge-active') && mouseX !== null && isNearArrow(mouseX, rightX, rightW, threshold))
                ) {
                    // Mouse is near an arrow, don't hide
                    scheduleHideArrows();
                    return;
                }
                $left.removeClass('edge-active');
                $right.removeClass('edge-active');
            }

            function scheduleHideArrows() {
                if (arrowTimeout) clearTimeout(arrowTimeout);
                arrowTimeout = setTimeout(hideArrows, 2000);
            }

            show(0);
            startAuto();

            $gallery.on('mousemove', function(e){
                var offset = $gallery.offset();
                var x = e.pageX - offset.left;
                var w = $gallery.width();

                if (lastX !== null) {
                    if (x > lastX + 2) { // moved right
                        $right.addClass('edge-active');
                        $left.removeClass('edge-active');
                    } else if (x < lastX - 2) { // moved left
                        $left.addClass('edge-active');
                        $right.removeClass('edge-active');
                    }
                }
                lastX = x;

                mouseX = x; // Save for hide check
                scheduleHideArrows();
            }).on('mouseleave', function(){
                hideArrows();
                lastX = null;
                mouseX = null;
                if (arrowTimeout) clearTimeout(arrowTimeout);
            });

            // Prevent arrows from hiding when hovered
            $left.add($right).on('mouseenter', function(){
                if (arrowTimeout) clearTimeout(arrowTimeout);
            }).on('mouseleave', function(){
                scheduleHideArrows();
            });

            // Optional: show arrow on mousemove/enter over edge
            $edgeLeft.on('mousemove mouseenter', function(){
                $left.addClass('edge-active');
                $right.removeClass('edge-active');
                scheduleHideArrows();
            });
            $edgeRight.on('mousemove mouseenter', function(){
                $right.addClass('edge-active');
                $left.removeClass('edge-active');
                scheduleHideArrows();
            });
            $edgeLeft.on('mouseleave', function(){ scheduleHideArrows(); });
            $edgeRight.on('mouseleave', function(){ scheduleHideArrows(); });
        });

    });

})( jQuery );
