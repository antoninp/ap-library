<?php

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
    private $actions = [];
    /**
     * The last admin notice message.
     *
     * @since    1.0.0
     * @access   public
     * @var      array    $last_notice    The last admin notice message.
     */
    public $last_notice = null;

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

        $this->load_dependencies();

        $this->register_default_actions();
	}

    /**
     * Load the required dependencies for this class.
     *
     * Include the files for the action classes.
     *
     * @since    1.0.0
     * @access   private
     */
    public function load_dependencies() {
        // Load action classes
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/actions/ActionInterface.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/actions/CreateAllUploadPosts.php';
    }

    /**
     * Register the default actions.
     *
     * @since    1.0.0
     * @access   private
     */
    private function register_default_actions() {
        // Store raw labels and translate at render time to avoid early JIT loading before 'init'
        $this->register_action('create_all_upload_posts', 'Create Missing Upload Posts', [new CreateAllUploadPosts(), 'execute']);
    }

    /**
     * Register a new action.
     *
     * @since    1.0.0
     * @param    string    $key        The unique key for the action.
     * @param    string    $label      The label for the action button.
     * @param    callable  $callback   The callback to execute the action.
     */
    public function register_action( $key, $label, $callback ) {
        $this->actions[ $key ] = array(
            'label'    => $label,
            'callback' => $callback,
        );
    }

    /**
     * Render the action buttons.
     *
     * @since    1.0.0
     */
    public function render_buttons() {
        foreach ( $this->actions as $key => $action ) {
            $label = esc_html__( $action['label'], 'ap-library' );
            ?>
            <form method="post" style="display:inline;">
                <?php wp_nonce_field( 'ap_library_action_' . $key, 'ap_library_nonce_' . $key ); ?>
                <input type="submit" name="ap_library_run_<?php echo esc_attr( $key ); ?>" class="button button-primary" value="<?php echo esc_attr( $label ); ?>">
            </form>
            <?php
        }
    }

    /**
     * Handle the action button submissions.
     *
     * @since    1.0.0
     */
    public function handle_actions() {
        foreach ( $this->actions as $key => $action ) {
            if (
                isset( $_POST[ 'ap_library_run_' . $key ] ) &&
                check_admin_referer( 'ap_library_action_' . $key, 'ap_library_nonce_' . $key )
            ) {
                if ( is_callable( $action['callback'] ) ) {
                    $result = call_user_func( $action['callback'] );
                    if ( is_wp_error( $result ) ) {
                        $this->last_notice = [
                            'type' => 'error',
                            'message' => sprintf(
                                esc_html__('%s failed: %s', 'ap-library'),
                                esc_html( __( $action['label'], 'ap-library' ) ),
                                esc_html( $result->get_error_message() )
                            ),
                        ];
                    } else {
                        $this->last_notice = [
                            'type' => 'success',
                            'message' => sprintf(
                                esc_html__( '%s executed successfully!', 'ap-library' ),
                                esc_html( __( $action['label'], 'ap-library' ) )
                            ),
                        ];
                    }
                }
            }
        }
    }

    /**
     * Get the last admin notice message.
     *
     * @since    1.0.0
     * @return   array|null    The last admin notice message or null if none.
     */
    public function get_last_notice() {
        return $this->last_notice;
    }
}