<?php

namespace Legacy\EmailEncoderBundle;

class Email_Encoder_Settings {

    public const PROTECT_FULL_PAGE    = 1;
    public const PROTECT_FILTERS_ONLY = 2;
    public const PROTECT_DISABLED     = 3;

	private string $admin_cap = 'manage_options';
	private string $page_name = 'email-encoder-bundle-option-page';
	private string $page_title;
	private string $final_output_buffer_hook = 'final_output';
	private string $widget_callback_hook = 'widget_output';
	private string $settings_key= 'WP_Email_Encoder_Bundle_options';
	private string $version_key= 'email-encoder-bundle-version';
	private string $image_secret_key = 'email-encoder-bundle-img-key';
	private string $at_identifier = '##eebAddIdent##';
	private ?string $previous_version = null;
	private array $hook_priorities = [ // deprecated!
		'buffer_final_output' => 1000,
		'setup_single_filter_hooks' => 100,
		'add_custom_template_tags' => 10,
		'load_frontend_header_styling' => 10,
		'filter_rss' => 100,
		'filter_page' => 100,
		'filter_content' => 100,
		'first_version_init' => 100,
		'version_update' => 100,
		'display_email_image' => 999,
		'callback_rss_remove_shortcodes' => 10,
		'load_ajax_scripts_styles' => 10,
		'load_ajax_scripts_styles_admin' => 10,
		'reload_settings_for_integrations' => 5,
		// 'eeb_dynamic_sidebar_params' => 100, //deprecated but kept for compatibility
	];
	private array $safe_attr_html;
	private string $email_regex = '([_A-Za-z0-9-]+(\\.[_A-Za-z0-9-]+)*@[A-Za-z0-9-]+(\\.[A-Za-z0-9-]+)*(\\.[A-Za-z]{2,}))';
	private array $soft_attribute_regex = [
		'woocommerce_variation_attribute_tag' => '/data-product_variations="([^"]*)"/i',
		'jetpack_carousel_image_attribute_tag' => '/data-image-meta="([^"]*)"/i',
		'html_placeholder_tag' => '/placeholder="([^"]*)"/i',
	];

	private array $settings = [];
	private string $version;
	private string $email_image_secret;
	private array $template_tags= [
		'eeb_filter' => 'template_tag_eeb_filter',
		'eeb_mailto' => 'template_tag_eeb_mailto'
	];

	private array $default_values = [
		'protect' 				   => self::PROTECT_FULL_PAGE,
		'filter_rss' 			   => 1,
		'powered_by' 			   => 1,
		'protect_using' 		   => 'with_javascript',
		'class_name' 			   => 'mail-link',
		'protection_text' 		   => '*protected email*',
		'image_color' 			   => '0,0,0',
		'image_background_color'   => '0,0,0',
		'image_text_opacity'	   => '0',
		'image_underline'	       => '0',
		'image_background_opacity' => '127',
		'image_font_size'	       => '4',
	];

	/**
	 * Email_Encoder_Settings constructor.
	 *
	 * We define all of our necessary settings in here.
	 * If you need to do plugin related changes, everything will
	 * be available in this file.
	 */
	function __construct() {
		$this->page_title = EEB_NAME;
		$this->safe_attr_html = require EEB_PLUGIN_DIR . '/config/SafeHtmlConfig.php';

		add_action( 'init', [ $this, 'load_settings' ] );
		add_action( 'init', [ $this, 'load_version' ] );
		add_action( 'init', [ $this, 'load_email_image_secret' ] );
	}

	/**
	 * ######################
	 * ###
	 * #### MAIN SETTINGS
	 * ###
	 * ######################
	 */

	 /**
	  * Load the settings for our admin settings page
	  *
	  * @return void
	  */
	public function load_settings() {

		$fields = require EEB_PLUGIN_DIR . '/config/SettingsConfig.php';
		$fields = apply_filters( 'eeb/settings/pre_filter_fields', $fields );

		$saved_values = get_option( $this->settings_key, [] );
		$values = array_replace_recursive( $this->default_values, $saved_values );


		if ( $values != $saved_values ) {
			update_option( $this->settings_key, $values );
			error_log( 'Updated option ' . $this->settings_key);
		}

		foreach ( $fields as $key => $field ) {
			if ( $field['type'] === 'multi-input' ) {
				foreach ( $field['inputs'] as $smi_key => $smi_data ) {

					if ( $field['input-type'] === 'radio' ) {
						if ( isset( $values[ $key ] ) && (string) $values[ $key ] === (string) $smi_key ) {
							$fields[ $key ]['value'] = $values[ $key ];
						}
					}
					else {
						if ( isset( $values[ $smi_key ] ) ) {
							$fields[ $key ]['inputs'][ $smi_key ]['value'] = $values[ $smi_key ];
						}
					}

				}
			}
			else {
				if ( isset( $values[ $key ] ) ) {
					$fields[ $key ]['value'] = $values[ $key ];
				}
			}
		}

		$this->settings = apply_filters( 'eeb/settings/fields', $fields );
	}

	/**
	 * ######################
	 * ###
	 * #### VERSIONING
	 * ###
	 * ######################
	 */

	public function load_version() {

		$current_version = get_option( $this->get_version_key() );

		if ( empty( $current_version ) ) {
			$current_version = EEB_VERSION;
			update_option( $this->get_version_key(), $current_version );

			add_action( 'init', array( $this, 'first_version_init' ), $this->get_hook_priorities( 'first_version_init' ) );
		}
		else {
			if ( $current_version !== EEB_VERSION ) {
				$this->previous_version = $current_version;
				$current_version = EEB_VERSION;
				update_option( $this->get_version_key(), $current_version );

				add_action( 'init', array( $this, 'version_update' ), $this->get_hook_priorities( 'version_update' ) );
			}
		}

		$this->version = $current_version;
		return $current_version;
	}


	public function load_email_image_secret() {

		if ( ! (bool) $this->get_setting( 'convert_plain_to_image', true, 'filter_body' ) ) {
			return false;
		}

		$image_descret = get_option( $this->get_image_secret_key() );

		if ( ! empty( $image_descret ) ) {
			$this->email_image_secret = $image_descret;
			return $image_descret;
		}

		$key = '';

		for ( $i = 0; $i < 265; $i++ ) {
			$key .= chr( mt_rand( 33, 126 ) );
		}

		update_option( $this->get_image_secret_key(), $key );

		$this->email_image_secret = $key;
		// return $key;
	}

	/**
	 * Fires an action after our settings key was initially set
	 * the very first time.
	 *
	 * @return void
	 */
	public function first_version_init() {
		do_action( 'eeb/settings/first_version_init', EEB_VERSION );
	}

	/**
	 * Fires after the version of the plugin is initially updated
	 *
	 * @return void
	 */
	public function version_update() {
		do_action( 'eeb/settings/version_update', EEB_VERSION, $this->previous_version );
	}

	/**
	 * ######################
	 * ###
	 * #### CALLABLE FUNCTIONS
	 * ###
	 * ######################
	 */

	/**
	 * Our admin cap handler function
	 *
	 * This function handles the admin capability throughout
	 * the whole plugin.
	 *
	 * $target - With the target function you can make a more precised filtering
	 * by changing it for specific actions.
	 *
	 * @param string $target - A identifier where the call comes from
	 * @return mixed
	 */
	public function get_admin_cap( $target = 'main' ) {
		/**
		 * Customize the globally used capability for this plugin
		 *
		 * This filter is called every time the capability is needed.
		 */
		return apply_filters( 'eeb/settings/capability', $this->admin_cap, $target );
	}

	/**
	 * Return the page name for our admin page
	 *
	 * @return string - the page name
	 */
	public function get_page_name() {
		return apply_filters( 'eeb/settings/page_name', $this->page_name );
	}

	/**
	 * Return the page title for our admin page
	 *
	 * @return string - the page title
	 */
	public function get_page_title() {
		return apply_filters( 'eeb/settings/page_title', $this->page_title );
	}

	/**
	 * Return the settings_key
	 *
	 * @return string - the settings key
	 */
	public function get_settings_key() {
		return $this->settings_key;
	}

	/**
	 * Return the version_key
	 *
	 * @return string - the version_key
	 */
	public function get_version_key() {
		return $this->version_key;
	}

	/**
	 * Return the image_secret_key
	 *
	 * @return string - the image_secret_key
	 */
	public function get_image_secret_key() {
		return $this->image_secret_key;
	}

	/**
	 * Return the email_image_secret
	 *
	 * @return string - the email_image_secret
	 */
	public function get_email_image_secret() {
		return $this->email_image_secret;
	}

	/**
	 * Return the version
	 *
	 * @return string - the version
	 */
	public function get_version() {
		return apply_filters( 'eeb/settings/get_version', $this->version );
	}

	/**
	 * Return the default template tags
	 *
	 * @return array - the template tags
	 */
	public function get_template_tags() {
		return apply_filters( 'eeb/settings/get_template_tags', $this->template_tags );
	}

	/**
	 * Return the widget callback hook name
	 *
	 * @return string - the final widget callback hook name
	 */
	public function get_widget_callback_hook() {
		return apply_filters( 'eeb/settings/widget_callback_hook', $this->widget_callback_hook );
	}

	/**
	 * Return the final output buffer hook name
	 *
	 * @return string - the final output buffer hook name
	 */
	public function get_final_output_buffer_hook() {
		return apply_filters( 'eeb/settings/final_output_buffer_hook', $this->final_output_buffer_hook );
	}

	/**
	 * Return the @ symbol identifier
	 *
	 * @return string - the @ symbol identifier
	 */
	public function get_at_identifier() {
		return apply_filters( 'eeb/settings/at_identifier', $this->at_identifier );
	}

	/**
	 * @link http://www.mkyong.com/regular-expressions/how-to-validate-email-address-with-regular-expression/
	 * @param boolean $include
	 * @return string
	 */
	public function get_email_regex( $include = false ) {

		if ( $include === true ) {
			$return = $this->email_regex;
		} else {
			$return = '/' . $this->email_regex . '/i';
		}

		return apply_filters( 'eeb/settings/get_email_regex', $return, $include );
	}

	/**
	 * Get Woocommerce variation attribute regex
	 *
	 * @param boolean $single
	 * @return string
	 */
	public function get_soft_attribute_regex( $single = null ) {

		$return = $this->soft_attribute_regex;

		if ( $single !== null ) {
			if ( isset( $this->soft_attribute_regex[ $single ] ) ) {
				$return = $this->soft_attribute_regex[ $single ];
			} else {
				$return = false;
			}
		}

		return apply_filters( 'eeb/settings/get_soft_attribute_regex', $return, $single );
	}

	/**
	 * Get hook priorities
	 *
	 * @param boolean $single - wether you want to return only a single hook priority or not
	 * @return mixed - An array or string of hook priority(-ies)
	 */
	public function get_hook_priorities( $single = false )  {

		$is_single = $single && isset( $this->hook_priorities[ $single ] );

		$return = $is_single
			? $this->hook_priorities[ $single ]
			: ( $single ? 10 : $this->hook_priorities )
		;
		$default = $is_single ? true : false;

		// if ( $single ) {
		// 	if ( isset( $this->hook_priorities[ $single ] ) ) {
		// 		$return = $this->hook_priorities[ $single ];
		// 		$default = false;
		// 	} else {
		// 		$return = 10;
		// 		$default = true;
		// 	}
		// }

		return apply_filters( 'eeb/settings/get_hook_priorities', $return, $default, $single );
	}

	/**
	  * Get a collection of safe HTML attributes
	  *
	  * @return array
	  */
	  public function get_safe_html_attr() {
		return apply_filters( 'eeb/settings/get_safe_html_attr', $this->safe_attr_html );
	}

	/**
	 * ######################
	 * ###
	 * #### Settings helper
	 * ###
	 * ######################
	 */

	/**
	 * Get the admin page url
	 *
	 * @return string - The admin page url
	 */
	public function get_admin_page_url() {

		$url = admin_url( "options-general.php?page=" . $this->get_page_name() );

		return apply_filters( 'eeb/settings/get_admin_page_url', $url );
	}

	/**
	 * Helper function to reload the settings
	 *
	 * @return void
	 */
	public function reload_settings() {
		$this->load_settings();
	}

	/**
	 * Return the default strings that are available
	 * for this plugin.
	 *
	 * @param $slug - the identifier for your specified setting
	 * @param $single - wether you only want to return the value or the whole settings element
	 * @param $group - in case you call a multi-input that contains multiple values (e.g. checkbox), you can set a sub-slug to grab the sub value
	 * @return mixed - the default string
	 */
	public function get_setting( $slug = '', $single = false, $group = '' ) {

        // temporary fix to resolve calls before class is properly booted
        if ( $this->settings === [] ) {
            error_log( 'EmailEncoderBundle: Method get_settings() is accessed too early!' );
            $this->load_settings();
        }
        // end of fix


		$return = $this->settings;

		if ( empty( $slug ) ) {
			return $return;
		}

		if ( isset( $this->settings[ $slug ] ) || ( ! empty( $group ) && isset( $this->settings[ $group ] ) ) ) {
			if ( $single ) {
				$return = false; // Default false

				//Set default to the main valie if available given with radio buttons)
				if ( isset( $this->settings[ $slug ]['value'] ) ) {
					$return = $this->settings[ $slug ]['value'];
				}

				if (
					! empty( $group )
					&& isset( $this->settings[ $group ]['type'] )
					&& $this->settings[ $group ]['type'] === 'multi-input'
					)
				{
					if ( isset( $this->settings[ $group ]['inputs'][ $slug ] ) && isset( $this->settings[ $group ]['inputs'][ $slug ]['value'] ) ) {
						$return = $this->settings[ $group ]['inputs'][ $slug ]['value'];
					}
				}

			} else {

				if ( ! empty( $group ) && isset( $this->settings[ $group ] ) ) {
					$return = $this->settings[ $group ];
				} else {
					$return = $this->settings[ $slug ];
				}

			}

		}

		return $return;
	}

}