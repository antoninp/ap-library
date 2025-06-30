<?php
class Ap_Library_Fade_Gallery_Block {
    public function register_block() {
        wp_register_script(
            'ap-fade-gallery-block',
            plugins_url( '../public/js/ap-library-fade-gallery-block.js', __FILE__ ),
            array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-block-editor' ),
            filemtime( plugin_dir_path( __FILE__ ) . '../public/js/ap-library-fade-gallery-block.js' ),
            true
        );

        register_block_type( 'ap-library/fade-gallery', array(
            'editor_script'   => 'ap-fade-gallery-block',
            'render_callback' => array( $this, 'render_block' ),
            'attributes'      => array(
                'images' => array(
                    'type'    => 'array',
                    'default' => array(),
                    'items'   => array(
                        'type' => 'object',
                    ),
                ),
            ),
        ) );
    }

    public function render_block( $attributes ) {
        if ( empty( $attributes['images'] ) ) return '';

        // Set defaults and sanitize
        $auto         = !empty($attributes['auto']) ? 'true' : 'false';
        $delay        = !empty($attributes['delay']) ? intval($attributes['delay']) : 4000;
        $effect       = !empty($attributes['effect']) ? esc_attr($attributes['effect']) : 'fade';
        $showArrows   = isset($attributes['showArrows']) ? ($attributes['showArrows'] ? 'true' : 'false') : 'true';
        $showDots     = isset($attributes['showDots']) ? ($attributes['showDots'] ? 'true' : 'false') : 'false';
        $pauseOnHover = isset($attributes['pauseOnHover']) ? ($attributes['pauseOnHover'] ? 'true' : 'false') : 'true';
        $randomize    = isset($attributes['randomize']) ? ($attributes['randomize'] ? 'true' : 'false') : 'false';
        $loop         = isset($attributes['loop']) ? ($attributes['loop'] ? 'true' : 'false') : 'true';
        $showCaptions = isset($attributes['showCaptions']) ? ($attributes['showCaptions'] ? 'true' : 'false') : 'false';
        $arrowColor   = !empty($attributes['arrowColor']) ? esc_attr($attributes['arrowColor']) : '#AEACA6';

        $arrow_left_svg = '
            <svg class="ap-fade-arrow-svg" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24">
                <path stroke="' . $arrowColor . '" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        ';
        $arrow_right_svg = '
            <svg class="ap-fade-arrow-svg" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24">
                <path stroke="' . $arrowColor . '" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        ';

        ob_start();
        ?>
        <div
            class="ap-fade-gallery"
            data-auto="<?php echo $auto; ?>"
            data-delay="<?php echo $delay; ?>"
            data-effect="<?php echo $effect; ?>"
            data-show-arrows="<?php echo $showArrows; ?>"
            data-show-dots="<?php echo $showDots; ?>"
            data-pause-on-hover="<?php echo $pauseOnHover; ?>"
            data-randomize="<?php echo $randomize; ?>"
            data-loop="<?php echo $loop; ?>"
            data-show-captions="<?php echo $showCaptions; ?>"
            data-arrow-color="<?php echo $arrowColor; ?>"
        >
            <div class="ap-fade-edge ap-fade-edge-left"></div>
            <div class="ap-fade-edge ap-fade-edge-right"></div>
            <?php if ($showArrows === 'true'): ?>
                <button class="ap-fade-arrow left" type="button"><?php echo $arrow_left_svg; ?></button>
                <button class="ap-fade-arrow right" type="button"><?php echo $arrow_right_svg; ?></button>
            <?php endif; ?>
            <?php foreach ( $attributes['images'] as $img ) : ?>
                <img src="<?php echo esc_url( $img['url'] ); ?>"
                     alt="<?php echo esc_attr( $img['alt'] ?? '' ); ?>"
                     <?php if ($showCaptions === 'true' && !empty($img['caption'])): ?>
                         data-caption="<?php echo esc_attr($img['caption']); ?>"
                     <?php endif; ?>
                >
            <?php endforeach; ?>
            <?php if ($showCaptions === 'true'): ?>
                <div class="ap-fade-caption"></div>
            <?php endif; ?>
            <?php if ($showDots === 'true'): ?>
                <div class="ap-fade-dots"></div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}