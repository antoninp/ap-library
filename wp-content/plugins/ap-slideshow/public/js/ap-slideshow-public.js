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

        // Ap Fade Gallery
        $('.ap-slideshow').each(function(){
            var $gallery = $(this);
            var $imgs = $gallery.find('img');
            var $left = $gallery.find('.ap-slideshow-arrow.left');
            var $right = $gallery.find('.ap-slideshow-arrow.right');
            var $edgeLeft = $gallery.find('.ap-slideshow-edge-left');
            var $edgeRight = $gallery.find('.ap-slideshow-edge-right');
            var $dots = $gallery.find('.ap-slideshow-dots');
            var idx = 0;
            var lastIdx = 0;
            var direction = 'right';
            var lastMouseX = null;
            var hideArrowTimer = null;
            var timer = null;

            // Read options from data attributes
            var auto = $gallery.data('auto') === true || $gallery.data('auto') === 'true';
            var delay = parseInt($gallery.data('delay'), 10) || 4000;
            var effect = $gallery.data('effect') || 'fade';
            var showArrows = $gallery.data('show-arrows') === true || $gallery.data('show-arrows') === 'true';
            var showDots = $gallery.data('show-dots') === true || $gallery.data('show-dots') === 'true';
            var pauseOnHover = $gallery.data('pause-on-hover') === true || $gallery.data('pause-on-hover') === 'true';
            var randomize = $gallery.data('randomize') === true || $gallery.data('randomize') === 'true';
            var loop = $gallery.data('loop') === true || $gallery.data('loop') === 'true';
            var showCaptions = $gallery.data('show-captions') === true || $gallery.data('show-captions') === 'true';
            var arrowColor = $gallery.data('arrow-color') || '#AEACA6';
            var captionSource = $gallery.data('caption-source') || 'caption';

            // Apply arrow color
            $gallery.find('.ap-slideshow-arrow-svg path').attr('stroke', arrowColor);

            // Randomize images if needed
            if (randomize && $imgs.length > 1) {
                $imgs.sort(function(){ return 0.5 - Math.random(); });
                $imgs.detach().appendTo($gallery);
                $imgs = $gallery.find('img'); // <-- Update $imgs to match new DOM order
            }

            // Dots logic
            if (showDots && $dots.length) {
                $dots.empty();
                for (var i = 0; i < $imgs.length; i++) {
                    $dots.append('<span class="ap-slideshow-dot" data-idx="'+i+'"></span>');
                }
                $dots.on('click', '.ap-slideshow-dot', function(){
                    show(parseInt($(this).data('idx'), 10));
                });
            }

            function show(idxNew) {
                $imgs.removeClass('active in-out slide-left slide-right zoom-in');
                direction = (idxNew > idx || (idxNew === 0 && idx === $imgs.length - 1)) ? 'right' : 'left';
                idx = (idxNew + $imgs.length) % $imgs.length;
                var $current = $imgs.eq(idx);

                if (effect === 'fade') {
                    $current.addClass('active');
                } else if (effect === 'slide') {
                    $current.addClass('active in-out ' + (direction === 'right' ? 'slide-right' : 'slide-left'));
                } else if (effect === 'zoom') {
                    $current.addClass('active zoom-in');
                } else {
                    $current.addClass('active');
                }

                if (showDots && $dots.length) {
                    $dots.find('.ap-slideshow-dot').removeClass('active').eq(idx).addClass('active');
                }
                if (showCaptions) {
                    var $captionBox = $gallery.find('.ap-slideshow-caption');
                    var $img = $imgs.eq(idx);
                    var caption = '';
                    if (captionSource === 'title') {
                        caption = $img.attr('title') || '';
                    } else if (captionSource === 'description') {
                        caption = $img.data('description') || '';
                    } else {
                        caption = $img.data('caption') || $img.attr('alt') || '';
                    }
                    $captionBox.text(caption);
                }
                var $linkBox = $gallery.find('.ap-slideshow-link');
                var $img = $imgs.eq(idx);
                var link = $img.data('link') || '';
                var linkTitle = $img.data('link-title') || '';
                if (link) {
                    $linkBox.html('<a href="' + link + '" class="ap-slideshow-link-inner">' + (linkTitle || link) + '</a>');
                    $linkBox.show();
                } else {
                    $linkBox.hide();
                }
                lastIdx = idx;
            }
            function next() {
                if (!loop && idx + 1 >= $imgs.length) return;
                show(idx + 1);
            }
            function prev() {
                if (!loop && idx - 1 < 0) return;
                show(idx - 1);
            }

            if (showArrows) {

                // Show left arrow when mouse is over left edge
                $edgeLeft.on('mouseenter mousemove', function() {
                    $left.addClass('edge-active');
                    $right.removeClass('edge-active');
                });
                $edgeLeft.on('mouseleave', function() {
                    $left.removeClass('edge-active');
                });
                $edgeRight.on('mouseenter mousemove', function() {
                    $right.addClass('edge-active');
                    $left.removeClass('edge-active');
                });
                $edgeRight.on('mouseleave', function() {
                    $right.removeClass('edge-active');
                });
                var lastMouseX = null;
                var hideArrowTimer = null;

                function showArrowTemp($arrow) {
                    $arrow.addClass('edge-active');
                    if (hideArrowTimer) clearTimeout(hideArrowTimer);
                    hideArrowTimer = setTimeout(function() {
                        $left.removeClass('edge-active');
                        $right.removeClass('edge-active');
                    }, 800); // Hide after 800ms of no movement
                }

                $gallery.on('mousemove', function(e) {
                    var offset = $gallery.offset();
                    var x = e.pageX - offset.left;
                    var width = $gallery.width();
                    var edgeZone = 100;

                    // Edge zone logic (always takes priority)
                    if (x < edgeZone) {
                        $left.addClass('edge-active');
                        $right.removeClass('edge-active');
                        lastMouseX = x;
                        if (hideArrowTimer) clearTimeout(hideArrowTimer);
                        return;
                    } else if (x > width - edgeZone) {
                        $right.addClass('edge-active');
                        $left.removeClass('edge-active');
                        lastMouseX = x;
                        if (hideArrowTimer) clearTimeout(hideArrowTimer);
                        return;
                    }

                    // Not in edge zone: show arrow based on movement direction
                    if (lastMouseX !== null) {
                        if (x < lastMouseX - 5) { // moved left
                            showArrowTemp($left);
                            $right.removeClass('edge-active');
                        } else if (x > lastMouseX + 5) { // moved right
                            showArrowTemp($right);
                            $left.removeClass('edge-active');
                        }
                    }
                    lastMouseX = x;
                });

                $gallery.on('mouseleave', function() {
                    $left.removeClass('edge-active');
                    $right.removeClass('edge-active');
                    lastMouseX = null;
                    if (hideArrowTimer) clearTimeout(hideArrowTimer);
                });

                // Edge overlays: only handle click and not mouseenter/mousemove/mouseleave
                $edgeLeft.on('click', function(){ prev(); resetAuto(); });
                $edgeRight.on('click', function(){ next(); resetAuto(); });

                // Arrows clickable as well
                $left.on('click', function(){ prev(); resetAuto(); });
                $right.on('click', function(){ next(); resetAuto(); });
            }

            function startAuto() {
                if(auto && $imgs.length > 1) {
                    timer = setInterval(next, delay);
                }
            }
            function resetAuto() {
                if(timer) clearInterval(timer);
                startAuto();
            }

            if (pauseOnHover) {
                $gallery.on('mouseenter', '.ap-slideshow-link', function(){ if(timer) clearInterval(timer); });
                $gallery.on('mouseleave', '.ap-slideshow-link', function(){ startAuto(); });
            }

            // Show first image
            show(0);
            startAuto();
        });

    });

})( jQuery );
