<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://antoninpuleo.com
 * @since      1.0.0
 *
 * @package    Ap_Library
 * @subpackage Ap_Library/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<?php if ( current_user_can( 'manage_options' ) ) : ?>
    <form method="post">
        <?php wp_nonce_field( 'ap_library_run_action', 'ap_library_nonce' ); ?>
        <input type="submit" name="ap_library_run_code" class="button button-primary" value="Run My Code">
    </form>
<?php endif; ?>
