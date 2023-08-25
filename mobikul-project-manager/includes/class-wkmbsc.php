<?php
/**
 * Main Class
 *
 * @package WooCommerce EU Price Indication
 */

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

if ( ! class_exists( 'WKMBSC' ) ) {

	/**
	 * WKMBSC Main Class
	 */
	final class WKMBSC {

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * WKMBSC Constructor.
		 */
		public function __construct() {
			$this->wkmbsc_define_constants();
			$this->wkmbsc_init_hooks();
		}

		/**
		 * This is a singleton page, access the single instance just using this method.
		 *
		 * @return object
		 */
		public static function get_instance() {
			if ( ! static::$instance ) {
				static::$instance = new self();
			}

			return static::$instance;
		}

		/**
		 * Defining plugin's constant.
		 *
		 * @return void
		 */
		public function wkmbsc_define_constants() {
			defined( 'WKMBSC_PLUGIN_URL' ) || define( 'WKMBSC_PLUGIN_URL', plugin_dir_url( dirname( __FILE__ ) ) );
			defined( 'WKMBSC_VERSION' ) || define( 'WKMBSC_VERSION', '1.0.0' );
			defined( 'WKMBSC_SCRIPT_VERSION' ) || define( 'WKMBSC_SCRIPT_VERSION', '1.0.0' );
		}

		/**
		 * Hook into actions and filters.
		 *
		 * @since 1.0.0
		 */
		private function wkmbsc_init_hooks() {
			// add_action( 'init', array( $this, 'wkmbsc_load_plugin_textdomain' ), 0 );
			add_action( 'plugins_loaded', array( $this, 'wkmbsc_load_plugin' ) );
		}

		/**
		 * Load plugin text domain.
		 */
		// public function wkmbsc_load_plugin_textdomain() {
		// load_plugin_textdomain( 'wkmbsc', false, plugin_basename( dirname( WKMBSC_PLUGIN_FILE ) ) . '/languages' );
		// }

		/**
		 * Load eu price indication plugin.
		 *
		 * @return void
		 */
		public function wkmbsc_load_plugin() {
			// if ( $this->wkmbsc_dependency_satisfied() ) {
				Wkmbsc_File_Handler::get_instance();
			// } else {
				// add_action( 'admin_notices', array( $this, 'wkmbsc_show_wc_not_installed_notice' ) );
			// }
		}

		/**
		 * Check if WooCommerce are installed and activated.
		 *
		 * @return bool
		 */
		// public function wkmbsc_dependency_satisfied() {
		// if ( ! function_exists( 'WC' ) || ! defined( 'WC_VERSION' ) ) {
		// return false;
		// }

		// return true;
		// }

		/**
		 * Cloning is forbidden.
		 *
		 * @since 1.0.0
		 */
		public function __clone() {
			wp_die( __FUNCTION__ . esc_html__( 'Cloning is forbidden.', 'wkmbsc' ) );
		}

		/**
		 * Deserializing instances of this class is forbidden.
		 *
		 * @since 1.0.0
		 */
		public function __wakeup() {
			wp_die( __FUNCTION__ . esc_html__( 'Deserializing instances of this class is forbidden.', 'wkmbsc' ) );
		}

		/**
		 * Show wc not installed notice.
		 *
		 * @return void
		 */
		public function wkmbsc_show_wc_not_installed_notice() {
			?>
			<div class="error">
				<p>
					<?php echo sprintf( /* translators: %s wkmbsc links */ esc_html__( 'Buyer seller chat API. It requires the last version of %s to work!', 'wkmbsc' ), '<a href="' . esc_url( 'https://wordpress.org/plugins/woocommerce/' ) . '" target="_blank">' . esc_html__( 'WooCommerce', 'wkmbsc' ) . '</a>' ); ?>
				</p>
			</div>
			<?php
		}
	}
}
