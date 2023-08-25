<?php
/**
 * Main Class
 *
 * @package Marketplace rma api
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

if ( ! class_exists( 'WKMPAPI' ) ) {

	/**
	 * Marketplace rma api Main Class
	 */
	final class WKMPAPI {

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Marketplace Constructor.
		 */
		public function __construct() {
			$this->wkmpapi_define_constants();
			$this->wkmpapi_init_hooks();
		}

		/**
		 * Ensures only one instance of this class is loaded or can be loaded.
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
		public function wkmpapi_define_constants() {
			defined( 'WKMP_RMA_API_PLUGIN_URL' ) || define( 'WKMP_RMA_API_PLUGIN_URL', plugin_dir_url( dirname( __FILE__ ) ) );
			defined( 'WKMP_RMA_API_VERSION' ) || define( 'WKMP_RMA_API_VERSION', '1.0.1' );
			defined( 'WKMP_RMA_API_SCRIPT_VERSION' ) || define( 'WKMP_RMA_API_SCRIPT_VERSION', '1.0.1' );

			// Change this DB version value if updating any table structure like alter, drop, insert tables or columns.
			defined( 'WKMP_RMA_API_DB_VERSION' ) || define( 'WKMP_RMA_API_DB_VERSION', '1.0.8' );
		}

		/**
		 * Hook into actions and filters.
		 *
		 * @since 1.0.0
		 */
		private function wkmpapi_init_hooks() {
			add_action( 'init', array( $this, 'wkmpapi_load_plugin_textdomain' ), 0 );
			add_action( 'plugins_loaded', array( $this, 'wkmpapi_load_plugin' ) );
		}


		/**
		 * Load plugin text domain.
		 */
		public function wkmpapi_load_plugin_textdomain() {
			load_plugin_textdomain( 'wkmpapi', false, plugin_basename( dirname( WKMP_RMA_API_PLUGIN_FILE ) ) . '/languages' );
		}

		/**
		 * Load gbuy plugin.
		 *
		 * @return void
		 */
		public function wkmpapi_load_plugin() {
			if ( $this->wkmpapi_dependency_satisfied() ) {
				WKMPAPI_Main_Handler::get_instance();
			} else {
				add_action( 'admin_notices', array( $this, 'wkmpapi_show_mp_not_installed_notice' ) );
			}
		}

		/**
		 * Check if marketplace and WooCommerce are installed and activated.
		 *
		 * @return bool
		 */
		public function wkmpapi_dependency_satisfied() {
			if ( ! function_exists( 'WC' ) || ! defined( 'WC_VERSION' ) ) {
				return false;
			}

			if ( ! class_exists( 'Marketplace' ) || ! defined( 'MARKETPLACE_VERSION' ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Cloning is forbidden.
		 *
		 * @since 1.0.0
		 */
		public function __clone() {
			wp_die( __FUNCTION__ . esc_html__( 'Cloning is forbidden.', 'wkmp-group-buy' ) );
		}

		/**
		 * Deserializing instances of this class is forbidden.
		 *
		 * @since 1.0.0
		 */
		public function __wakeup() {
			wp_die( __FUNCTION__ . esc_html__( 'Deserializing instances of this class is forbidden.', 'wkmp-group-buy' ) );
		}

		/**
		 * Show wc not installed notice.
		 *
		 * @return void
		 */
		public function wkmpapi_show_mp_not_installed_notice() {
			?>
			<div class="error">
				<p><?php echo wp_sprintf( /* translators: %s marketplace links */ esc_html__( 'Marketplace RMA Api is enabled but not effective. It requires the last version of %s to work!', 'wkmp-group-buy' ), '<a href="https://codecanyon.net/item/wordpress-woocommerce-marketplace-plugin/19214408" target="_blank">' . esc_html__( 'Marketplace for WooCommerce', 'wkmp-group-buy' ) . '</a>' ); ?></p>
			</div>
			<?php
		}
	}
}
