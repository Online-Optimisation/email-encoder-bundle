<?php
if ( ! class_exists( 'Email_Encoder' ) ) :

	/**
	 * Main Email_Encoder Class.
	 *
	 * @since 2.0.0
	 * @package EEB
	 * @author Ironikus <info@ironikus.com>
	 */
	final class Email_Encoder {

		/**
		 * The real instance
		 *
		 * @var Email_Encoder
		 * @since 2.0.0
		 */
		private static $instance;

		/**
		 * EEB settings Object.
		 *
		 * @var object|Email_Encoder_Settings
		 * @since 2.0.0
		 */
		public $settings;

		/**
		 * EEB helpers Object.
		 *
		 * @var object|Email_Encoder_Helpers
		 * @since 2.0.0
		 */
		public $helpers;

		/**
		 * EEB validate Object.
		 *
		 * @var object|Email_Encoder_Validate
		 * @since 2.0.0
		 */
		public $validate;

		/**
		 * Throw error on object clone.
		 *
		 * Cloning instances of the class is forbidden.
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'email-encoder-bundle' ), '2.0.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'email-encoder-bundle' ), '2.0.0' );
		}

		/**
		 * Main Email_Encoder Instance.
		 *
		 * Insures that only one instance of Email_Encoder exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since 2.0.0
		 * @static
		 * @staticvar array $instance
		 * @return object|Email_Encoder The one true Email_Encoder
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Email_Encoder ) ) {
				self::$instance                 = new Email_Encoder;
				self::$instance->base_hooks();
				self::$instance->includes();
				self::$instance->helpers        = new Email_Encoder_Helpers();
				self::$instance->settings       = new Email_Encoder_Settings();
				self::$instance->validate       = new Email_Encoder_Validate();

				new Email_Encoder_Ajax();
				new EEB_Integrations_Loader();
				new Email_Encoder_Run();

				/**
				 * Fire a custom action to allow extensions to register
				 * after Email Encoder was successfully registered
				 */
				do_action( 'eeb_plugin_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Include required files.
		 *
		 * @access private
		 * @since 2.0.0
		 * @return void
		 */
		private function includes() {
			require_once EEB_PLUGIN_DIR . 'core/includes/classes/class-email-encoder-bundle-helpers.php';
			require_once EEB_PLUGIN_DIR . 'core/includes/classes/class-email-encoder-bundle-settings.php';
			require_once EEB_PLUGIN_DIR . 'core/includes/classes/class-email-encoder-bundle-validate.php';

			require_once EEB_PLUGIN_DIR . 'core/includes/classes/class-email-encoder-bundle-ajax.php';
			require_once EEB_PLUGIN_DIR . 'core/includes/functions/template-tags.php';

			require_once EEB_PLUGIN_DIR . 'core/includes/integrations/loader.php';

			if( is_admin() ){
				require_once EEB_PLUGIN_DIR . 'core/includes/classes/class-email-encoder-bundle-run-admin.php';
			} else {
				require_once EEB_PLUGIN_DIR . 'core/includes/classes/class-email-encoder-bundle-run.php';
			}

		}

		/**
		 * Add base hooks for the core functionality
		 *
		 * @access private
		 * @since 2.0.0
		 * @return void
		 */
		private function base_hooks() {
			add_action( 'init', array( self::$instance, 'load_textdomain' ) );
		}

		/**
		 * Loads the plugin language files.
		 *
		 * @access public
		 * @since 2.0.0
		 * @return void
		 */
		public function load_textdomain() {
			load_plugin_textdomain( EEB_TEXTDOMAIN, FALSE, dirname( plugin_basename( EEB_PLUGIN_FILE ) ) . '/languages/' );
		}

	}

endif; // End if class_exists check.