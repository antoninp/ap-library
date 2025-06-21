<?php


/**
 * The admin-specific actions of the plugin.
 *
 * @link       https://antoninpuleo.com
 * @since      1.0.0
 *
 * @package    Ap_Library
 * @subpackage Ap_Library/admin
 */

/**
 * The admin-specific actions of the plugin.
 *
 * Defines the plugin name, version and actions
 *
 * @package    Ap_Library
 * @subpackage Ap_Library/admin
 * @author     Antonin Puleo <a@antoninpuleo.com>
 */
class Ap_Library_Admin_Actions {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;


    /**
	 * The actions of this admin menu.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $actions    The actions of this admin menu.
	 */
    private $actions;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
        $this->actions = array();

	}
    public function register_action( $key, $label, $callback ) {
        $this->actions[ $key ] = array(
            'label'    => $label,
            'callback' => $callback,
        );
    }

    public function render_buttons() {
        foreach ( $this->actions as $key => $action ) {
            ?>
            <form method="post" style="display:inline;">
                <?php wp_nonce_field( 'ap_library_action_' . $key, 'ap_library_nonce_' . $key ); ?>
                <input type="submit" name="ap_library_run_<?php echo esc_attr( $key ); ?>" class="button button-primary" value="<?php echo esc_attr( $action['label'] ); ?>">
            </form>
            <?php
        }
    }

    public function handle_actions() {
        foreach ( $this->actions as $key => $action ) {
            if (
                isset( $_POST[ 'ap_library_run_' . $key ] ) &&
                check_admin_referer( 'ap_library_action_' . $key, 'ap_library_nonce_' . $key )
            ) {
                try {
                    $result = call_user_func( $action['callback'] );
                    if ( is_wp_error( $result ) ) {
                        throw new Exception( $result->get_error_message() );
                    }
                    add_action( 'admin_notices', function() use ( $action ) {
                        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $action['label'] ) . ' executed successfully!</p></div>';
                    } );
                } catch ( Exception $e ) {
                    add_action( 'admin_notices', function() use ( $e, $action ) {
                        echo '<div class="notice notice-error is-dismissible"><p>Error running ' . esc_html( $action['label'] ) . ': ' . esc_html( $e->getMessage() ) . '</p></div>';
                    } );
                }
            }
        }
    }
}