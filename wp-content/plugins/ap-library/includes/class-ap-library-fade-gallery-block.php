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
        $auto = !empty($attributes['auto']) ? 'true' : 'false';
        $arrow_left_svg = '
            <svg class="ap-fade-arrow-svg" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24">
                <path stroke="#AEACA6" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        ';
        $arrow_right_svg = '
            <svg class="ap-fade-arrow-svg" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24">
                <path stroke="#AEACA6" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        ';
        ob_start();
        ?>
        <div class="ap-fade-gallery" data-auto="<?php echo esc_attr($auto); ?>">
            <div class="ap-fade-edge ap-fade-edge-left"></div>
            <div class="ap-fade-edge ap-fade-edge-right"></div>
            <button class="ap-fade-arrow left" type="button"><?php echo $arrow_left_svg; ?></button>
            <button class="ap-fade-arrow right" type="button"><?php echo $arrow_right_svg; ?></button>
            <?php foreach ( $attributes['images'] as $img ) : ?>
                <img src="<?php echo esc_url( $img['url'] ); ?>" alt="<?php echo esc_attr( $img['alt'] ?? '' ); ?>">
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}