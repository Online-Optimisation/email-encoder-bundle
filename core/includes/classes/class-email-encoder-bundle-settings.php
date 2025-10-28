<?php

/**
 * Class Email_Encoder_Settings
 *
 * This class contains all of our important settings
 * Here you can configure the whole plugin behavior.
 *
 * @since 2.0.0
 * @package EEB
 * @author Ironikus <info@ironikus.com>
 */
class Email_Encoder_Settings{

	/**
	 * Our globally used capability
	 *
	 * @var string
	 * @since 2.0.0
	 */
	private $admin_cap;

	/**
	 * The main page name
	 *
	 * @var string
	 * @since 2.0.0
	 */
	private $page_name;

	/**
	 * The page title
	 *
	 * @var string
	 * @since 2.0.0
	 */
	private $page_title;

	/**
	 * The final output buffer hook
	 *
	 * @var string
	 * @since 2.1.10
	 */
	private $final_outout_buffer_hook;

	/**
	 * The widget callback
	 *
	 * @var string
	 * @since 2.1.10
	 */
	private $widget_callback_hook;

	/**
	 * The settings key
	 *
	 * @var string
	 * @since 2.1.10
	 */
	private $settings_key;

	/**
	 * The versions key
	 *
	 * @var string
	 * @since 2.1.10
	 */
	private $version_key;

	/**
	 * The image secret key
	 *
	 * @var string
	 * @since 2.1.10
	 */
	private $image_secret_key;

	/**
	 * The @ character identifier
	 *
	 * @var string
	 * @since 2.1.10
	 */
	private $at_identifier;

	/**
	 * The previous version
	 *
	 * @var string
	 * @since 2.1.10
	 */
	private $previous_version;

	/**
	 * The hook priorities
	 *
	 * @var string
	 * @since 2.1.10
	 */
	private $hook_priorities;

	/**
	 * The safe HTML attributes (against XSS)
	 *
	 * @var string
	 * @since 2.1.10
	 */
	private $safe_attr_html;

	/**
	 * The email regex
	 *
	 * @var string
	 * @since 2.1.10
	 */
	private $email_regex;

	/**
	 * The soft (HTML) attribute regex
	 *
	 * @var string
	 * @since 2.1.10
	 */
	private $soft_attribute_regex;

	/**
	 * The available settings
	 *
	 * @var string
	 * @since 2.1.10
	 */
	private $settings;

	/**
	 * The version
	 *
	 * @var string
	 * @since 2.1.10
	 */
	private $version;

	/**
	 * The email image secret
	 *
	 * @var string
	 * @since 2.1.10
	 */
	private $email_image_secret;

	/**
	 * The template tags
	 *
	 * @var string
	 * @since 2.2.1
	 */
	private $template_tags;

	/**
	 * Email_Encoder_Settings constructor.
	 *
	 * We define all of our necessary settings in here.
	 * If you need to do plugin related changes, everything will
	 * be available in this file.
	 */
	function __construct(){
		$this->admin_cap            	= 'manage_options';
		$this->page_name            	= 'email-encoder-bundle-option-page';
		$this->page_title           	= EEB_NAME;
		$this->final_outout_buffer_hook = 'final_output';
		$this->widget_callback_hook 	= 'widget_output';
		$this->template_tags 			= array( 'eeb_filter' => 'template_tag_eeb_filter', 'eeb_mailto' => 'template_tag_eeb_mailto' );
		$this->settings_key        		= 'WP_Email_Encoder_Bundle_options';
		$this->version_key        		= 'email-encoder-bundle-version';
		$this->image_secret_key     	= 'email-encoder-bundle-img-key';
		$this->at_identifier     		= '##eebAddIdent##';
		$this->previous_version        	= null;
		$this->hook_priorities        	= array(
			'buffer_final_output' => 1000,
			'setup_single_filter_hooks' => 100,
			'add_custom_template_tags' => 10,
			'load_frontend_header_styling' => 10,
			'eeb_dynamic_sidebar_params' => 100, //deprecated but kept for compatibility
			'filter_rss' => 100,
			'filter_page' => 100,
			'filter_content' => 100,
			'first_version_init' => 100,
			'version_update' => 100,
			'display_email_image' => 10,
			'callback_rss_remove_shortcodes' => 10,
			'load_ajax_scripts_styles' => 10,
			'load_ajax_scripts_styles_admin' => 10,
		);
		$this->safe_attr_html = array(
			'p' => array(
				'id' => array(),
				'class' => array(),
				'style' => array(),
				'align' => array(),
				'lang' => array(),
				'dir' => array(),
			),
			'div' => array(
				'id' => array(),
				'class' => array(),
				'style' => array(),
				'align' => array(),
				'lang' => array(),
				'dir' => array(),
			),
			'span' => array(
				'id' => array(),
				'class' => array(),
				'style' => array(),
				'lang' => array(),
				'dir' => array(),
			),
			'br' => array(),
			'strong' => array(),
			'center' => array(),
			'ul' => array(
				'id' => array(),
				'class' => array(),
				'style' => array(),
				'lang' => array(),
				'dir' => array(),
			),
			'ol' => array(
				'id' => array(),
				'class' => array(),
				'style' => array(),
				'lang' => array(),
				'dir' => array(),
			),
			'li' => array(
				'id' => array(),
				'class' => array(),
				'style' => array(),
				'lang' => array(),
				'dir' => array(),
			),
			'a' => array(
				'id' => array(),
				'class' => array(),
				'style' => array(),
				'href' => array(),
				'title' => array(),
				'rel' => array(),
				'target' => array(),
				'lang' => array(),
				'dir' => array(),
			),
			'img' => array(
				'id' => array(),
				'class' => array(),
				'style' => array(),
				'src' => array(),
				'alt' => array(),
				'width' => array(),
				'height' => array(),
				'align' => array(),
				'title' => array(),
			),
		);

		//Regex
		$this->email_regex 			= '([_A-Za-z0-9-]+(\\.[_A-Za-z0-9-]+)*@[A-Za-z0-9-]+(\\.[A-Za-z0-9-]+)*(\\.[A-Za-z]{2,}))';
		$this->soft_attribute_regex = array(
			'woocommerce_variation_attribute_tag' => '/data-product_variations="([^"]*)"/i',
			'jetpack_carousel_image_attribute_tag' => '/data-image-meta="([^"]*)"/i',
			'html_placeholder_tag' => '/placeholder="([^"]*)"/i',
		);

		//Load data
		// $this->settings        			= $this->load_settings();
        add_action( 'admin_init', [ $this, 'load_settings' ] );

		$this->version        			= $this->load_version();
		$this->email_image_secret       = $this->load_email_image_secret();
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
	  * @return array - An array with all available settings and filled values
	  */
	public function load_settings(){
		$fields = array(

			'protect' => array(
				'fieldset'    => array( 'slug' => 'main', 'label' => 'Label' ),
				'id'          => 'protect',
				'type'        => 'multi-input',
				'input-type'  => 'radio',
				'title'       => __( 'Protect emails', 'email-encoder-bundle' ),
				'inputs' 	  => array(
					1 => array(
						'label' => __( 'Full-page scan', 'email-encoder-bundle' ),
						'description' => __('This will check the whole page against any mails and secures them.', 'email-encoder-bundle' )
					),
					2 => array(
						'label' => __( 'Wordpress filters', 'email-encoder-bundle' ),
						'description' => __('Secure only mails that occur within WordPress filters. (Not recommended)', 'email-encoder-bundle' ),
						'advanced' 	  => true,
					),
					3 => array(
						'label' => __( 'Don\'t do anything.', 'email-encoder-bundle' ),
						'description' => __('This turns off the protection for emails. (Not recommended)', 'email-encoder-bundle')
					),
				 ),
				'required'    => false
			),

			'protect_using' => array(
				'fieldset'    => array( 'slug' => 'main', 'label' => 'Label' ),
				'id'          => 'protect_using',
				'type'        => 'multi-input',
				'input-type'  => 'radio',
				'title'       => __( 'Protect emails using', 'email-encoder-bundle' ),
				'inputs' 	  => array(
					'with_javascript' => array(
						'label' => __( 'automatically the best method (including javascript)', 'email-encoder-bundle' )
					),
					'without_javascript' => array(
						'label' => __( 'automatically the best method (excluding javascript)', 'email-encoder-bundle' ),
					),
					'strong_method' => array(
						'label' => __( 'a strong method that replaces all emails with a "*protection text*".', 'email-encoder-bundle' ),
						'description' => __('You can configure the protection text within the advanced settings.', 'email-encoder-bundle')
					),
					'char_encode' => array(
						'label' => __( 'simple HTML character encoding.', 'email-encoder-bundle' ),
						'description' => __('Offers good (but not the best) protection, which saves you in most scenarios.', 'email-encoder-bundle')
					),
				 ),
				'required'    => false
			),

			'filter_body' => array(
				'fieldset'    => array( 'slug' => 'main', 'label' => 'Label' ),
				'id'          => 'filter_body',
				'type'        => 'multi-input',
				'input-type'  => 'checkbox',
				'advanced' 	  => true,
				'title'       => __( 'Protect...', 'email-encoder-bundle' ),
				'label'       => __( 'Customize what this plugin protects.', 'email-encoder-bundle' ),
				'inputs' 	  => array(
					'filter_rss' => array(
						'advanced' 	  => true,
						'label' => __( 'RSS feed', 'email-encoder-bundle' ),
						'description' => __( 'Activating this option results in protecting the rss feed based on the given protection method.', 'email-encoder-bundle' )
					),
					'ajax_requests' => array(
						'advanced' 	  => true,
						'label' => __( 'Ajax requests', 'email-encoder-bundle' ),
						'description' => __( 'By default, ajax requests can send clear emails in some situations. Activating this settings will apply encoding to ajax-relate requests.', 'email-encoder-bundle' )
					),
					'admin_requests' => array(
						'advanced' 	  => true,
						'label' => __( 'Admin requests', 'email-encoder-bundle' ),
						'description' => __( 'By default, we only protect frontend requests (Everything people see on your website). Activating this setting will also protect the backend of your website (The admin area).', 'email-encoder-bundle' )
					),
					'remove_shortcodes_rss' => array(
						'advanced' 	  => true,
						'label' => __( 'Remove all shortcodes from the RSS feeds', 'email-encoder-bundle' ),
						'description' => __( 'Activating this option results in protecting the rss feed based on the given protection method.', 'email-encoder-bundle' )
					),
					'input_strong_protection' => array(
						'advanced' 	  => true,
						'label' => __( 'input form email fields using strong protection.', 'email-encoder-bundle' ),
						'description' => __( 'Warning: this option could conflict with certain form plugins. Test it first. (Requires javascript)', 'email-encoder-bundle' )
					),
					'encode_mailtos' => array(
						'advanced' 	  => true,
						'label' => __( 'plain emails by converting them to mailto links', 'email-encoder-bundle' ),
						'description' => __( 'Plain emails will be automatically converted to mailto links where possible.', 'email-encoder-bundle' )
					),
					'convert_plain_to_image' => array(
						'advanced' 	  => true,
						'label' => __( 'plain emails by converting them to png images', 'email-encoder-bundle' ),
						'description' => __( 'Plain emails will be automatically converted to png images where possible.', 'email-encoder-bundle' )
					),
					'protect_shortcode_tags' => array(
						'advanced' 	  => true,
						'label' => __( 'shortcode content', 'email-encoder-bundle' ),
						'description' => __( 'Protect every shortcode content separately. (This may slows down your site)', 'email-encoder-bundle' )
					),
					'filter_hook' => array(
						'advanced' 	  => true,
						'label' => __( 'emails from "init" hook', 'email-encoder-bundle' ),
						'description' => __( 'Check this option if you want to register the email filters on the "init" hook instead of the "wp" hook.', 'email-encoder-bundle' )
					),
					'deactivate_rtl' => array(
						'advanced' 	  => true,
						'label' => __( 'mailto links without CSS direction', 'email-encoder-bundle' ),
						'description' => __( 'Check this option if your site does not support CSS directions.', 'email-encoder-bundle' )
					),
					'no_script_tags' => array(
						'advanced' 	  => true,
						'label' => __( 'no script tags', 'email-encoder-bundle' ),
						'description' => __( 'Check this option if you face issues with encoded script tags. This will deactivate protection for script tags.', 'email-encoder-bundle' )
					),
					'no_attribute_validation' => array(
						'advanced' 	  => true,
						'label' => __( 'html attributes without soft encoding.', 'email-encoder-bundle' ),
						'description' => __( 'Do not soft-filter all html attributes. This might optimizes the performance, but can break the site if other plugins use your email in attribute tags.', 'email-encoder-bundle' )
					),
				 ),
				'required'    => false,
			),

			'image_settings' => array(
				'fieldset'    => array( 'slug' => 'main', 'label' => 'Label' ),
				'id'          => 'image_settings',
				'type'        => 'multi-input',
				'input-type'  => 'text',
				'advanced' 	  => true,
				'title'       => __( 'Image settings', 'email-encoder-bundle' ),
				'label'       => __( 'Customize the settings for dynamically created images.', 'email-encoder-bundle' ),
				'inputs' 	  => array(
					'image_color' => array(
						'advanced' 	  => true,
						'label' => __( 'Image Colors', 'email-encoder-bundle' ),
						'description' => __( 'Please include RGB colors, comme saparated. E.g.: 0,0,255', 'email-encoder-bundle' )
					),
					'image_background_color' => array(
						'advanced' 	  => true,
						'label' => __( 'Image Background Colors', 'email-encoder-bundle' ),
						'description' => __( 'Please include RGB colors, comme saparated. E.g.: 0,0,255', 'email-encoder-bundle' )
					),
					'image_text_opacity' => array(
						'advanced' 	  => true,
						'label' => __( 'Text Opacity', 'email-encoder-bundle' ),
						'description' => __( 'Change the text opacity for the created images. 0 = not transparent - 127 = completely transprent', 'email-encoder-bundle' )
					),
					'image_background_opacity' => array(
						'advanced' 	  => true,
						'label' => __( 'Background Opacity', 'email-encoder-bundle' ),
						'description' => __( 'Change the background opacity for the created images. 0 = not transparent - 127 = completely transprent', 'email-encoder-bundle' )
					),
					'image_font_size' => array(
						'advanced' 	  => true,
						'label' => __( 'Font Size', 'email-encoder-bundle' ),
						'description' => __( 'Change the font size of the image text. Default: 4 - You can choose from 1 - 5', 'email-encoder-bundle' )
					),
					'image_underline' => array(
						'advanced' 	  => true,
						'label' => __( 'Text Underline', 'email-encoder-bundle' ),
						'description' => __( 'Adds a line beneath the text to highlight it as a link. empty or 0 deactivates the border. 1 = 1px', 'email-encoder-bundle' )
					),
				 ),
				'required'    => false,
			),

			'skip_posts' => array(
				'fieldset'    => array( 'slug' => 'main', 'label' => 'Label' ),
				'id'          => 'skip_posts',
				'type'        => 'text',
				'advanced' 	  => true,
				'title'       => __('Exclude post id\'s from protection', 'email-encoder-bundle'),
				'placeholder' => '',
				'required'    => false,
				'description' => __('By comma separating post id\'s ( e.g. 123,4535,643), you are able to exclude these posts from the logic protection.', 'email-encoder-bundle')
			),

			'skip_query_parameters' => array(
				'fieldset'    => array( 'slug' => 'main', 'label' => 'Label' ),
				'id'          => 'skip_query_parameters',
				'type'        => 'text',
				'advanced' 	  => true,
				'title'       => __('Exclude URL parameters from protection', 'email-encoder-bundle'),
				'placeholder' => '',
				'required'    => false,
				'description' => __('By comma separating URL (Query) parameters ( e.g. param1,param2), you are able to exclude URLs with these parameters from the protection. URL or Query parameters are found at the end of your URL (e.g. domain.com?param1=test)', 'email-encoder-bundle')
			),

			'protection_text' => array(
				'fieldset'    => array( 'slug' => 'main', 'label' => 'Label' ),
				'id'          => 'protection_text',
				'type'        => 'text',
				'advanced' 	  => true,
				'title'       => __('Set protection text *', 'email-encoder-bundle'),
				'placeholder' => '',
				'required'    => false,
				'description' => __('This text will be shown for protected email addresses and within noscript tags.', 'email-encoder-bundle')
			),

			'class_name' => array(
				'fieldset'    => array( 'slug' => 'main', 'label' => 'Label' ),
				'id'          => 'class_name',
				'type'        => 'text',
				'advanced' 	  => true,
				'title'       => __('Additional classes', 'email-encoder-bundle'),
				'label'       => __('Add extra classes to mailto links.', 'email-encoder-bundle'),
				'placeholder' => '',
				'required'    => false,
				'description' => __('Leave blank for none', 'email-encoder-bundle')
			),

			'custom_href_attr' => array(
				'fieldset'    => array( 'slug' => 'main', 'label' => 'Label' ),
				'id'          => 'custom_href_attr',
				'type'        => 'text',
				'advanced' 	  => true,
				'title'       => __('Protect custom href attributes', 'email-encoder-bundle'),
				'label'       => __('Protect href atrributes such as tel:, ftp:, file:, etc.', 'email-encoder-bundle'),
				'placeholder' => '',
				'required'    => false,
				'description' => __('Add the href attributes you want to protect as a comme-separated list. E.g. tel,file,ftp', 'email-encoder-bundle')
			),

			'footer_scripts' => array(
				'fieldset'    => array( 'slug' => 'main', 'label' => 'Label' ),
				'id'          => 'footer_scripts',
				'type'        => 'checkbox',
				'advanced' 	  => true,
				'title'       => __('Load scripts in footer', 'email-encoder-bundle'),
				'label'       => __('Check this button if you want to load all frontend scripts within the footer.', 'email-encoder-bundle'),
				'placeholder' => '',
				'required'    => false,
				'description' => __('This forces every script to be enqueued within the footer.', 'email-encoder-bundle')
			),

			'show_encoded_check' => array(
				'fieldset'    => array( 'slug' => 'main', 'label' => 'Label' ),
				'id'          => 'show_encoded_check',
				'type'        => 'checkbox',
				'title'       => __('Security Check', 'email-encoder-bundle'),
				'label'       => __('Mark emails on the site as successfully encoded', 'email-encoder-bundle') . '<i class="dashicons-before dashicons-lock" style="color:green;"></i>',
				'placeholder' => '',
				'required'    => false,
				'description' => __('Only visible for admin users. If your emails look broken, simply deactivate this feature. This also loads the dashicons style.', 'email-encoder-bundle')
			),

			'own_admin_menu' => array(
				'fieldset'    => array( 'slug' => 'main', 'label' => 'Label' ),
				'id'          => 'own_admin_menu',
				'type'        => 'checkbox',
				'advanced' 	  => true,
				'title'       => __('Admin Menu', 'email-encoder-bundle'),
				'label'       => __('Show this page in the main menu item', 'email-encoder-bundle'),
				'placeholder' => '',
				'required'    => false,
				'description' => __('Otherwise it will be shown in "Settings"-menu.', 'email-encoder-bundle')
			),

			'encoder_form' => array(
				'fieldset'    => array( 'slug' => 'main', 'label' => 'Label' ),
				'id'          => 'encoder_form',
				'type'        => 'multi-input',
				'input-type'  => 'checkbox',
				'advanced' 	  => true,
				'title'       => __( 'Encoder form settings', 'email-encoder-bundle' ),
				'inputs' 	  => array(
					'encoder_form_frontend' => array(
						'label' => __( 'Encoder form frontend', 'email-encoder-bundle' ),
						'description' => __( 'Activate this to use the [eeb_form] shortcode or the PHP template function eeb_form() within the frontend.', 'email-encoder-bundle' )
					),
					'powered_by' => array(
						'label' => __( 'Show a "powered by" link on bottom of the encoder form', 'email-encoder-bundle' ),
					),
				 ),
				'required'    => false
			),

			'advanced_settings' => array(
				'fieldset'    => array( 'slug' => 'main', 'label' => 'Label' ),
				'id'          => 'advanced_settings',
				'type'        => 'checkbox',
				'title'       => __('Advanced Settings', 'email-encoder-bundle'),
				'label'       => __('Show advanced settings for more configuration possibilities.', 'email-encoder-bundle'),
				'placeholder' => '',
				'required'    => false,
				'description' => __('Activate the advanced settings in case you want to customize the default logic or you want to troubleshoot the plugin.', 'email-encoder-bundle')
			),

		);

		$fields = apply_filters( 'eeb/settings/pre_filter_fields', $fields );

		$default_values = array(
			'protect' 				=> 1,
			'filter_rss' 			=> 1,
			'powered_by' 			=> 1,
			'protect_using' 		=> 'with_javascript',
			'class_name' 			=> 'mail-link',
			'protection_text' 		=> '*protected email*',
			'image_color' 			=> '0,0,0',
			'image_background_color'=> '0,0,0',
			'image_text_opacity'	=> '0',
			'image_underline'	=> '0',
			'image_background_opacity'	=> '127',
			'image_font_size'	=> '4',
		);
		$values = get_option( $this->settings_key );

		if ( empty( $values ) && ! is_array( $values ) ){
			update_option( $this->settings_key, $default_values );
			$values = $default_values;
		}

		//Bakwards compatibility
		if ( ! isset( $values['protect_using'] ) ){
			$values['protect_using'] = 'with_javascript';
			$values['encoder_form_frontend'] = 1;
		}

		//In case the mailto functiinality was deactivated, we will set it do "Do nothing" as well.
		if ( ! isset( $values['protect'] ) ){
			$values['protect'] = 1;
		}
		///Backwards compatibility

		//Value corrections
		if ( ! isset( $values['image_color'] ) ){
			$values['image_color'] = $default_values['image_color'];
		}
		$image_color = explode( ',', $values['image_color'] );
		if ( count( $image_color ) != 3 ){
			$values['image_color'] = $default_values['image_color'];
		}
		foreach( explode( ',', $values['image_color'] ) as $image_color_key => $image_color_single ){
			if ( ! is_numeric( trim( $image_color_single ) ) ){
				$values['image_color'] = $default_values['image_color'];
			}
		}

		if ( ! isset( $values['image_background_color'] ) ){
			$values['image_background_color'] = $default_values['image_background_color'];
		}
		$image_background_color = explode( ',', $values['image_background_color'] );
		if ( count( $image_background_color ) != 3 ){
			$values['image_background_color'] = $default_values['image_background_color'];
		}
		foreach( explode( ',', $values['image_background_color'] ) as $image_background_color_key => $image_background_color_single ){
			if ( ! is_numeric( trim( $image_background_color_single ) ) ){
				$values['image_background_color'] = $default_values['image_background_color'];
			}
		}

		if ( ! isset( $values['image_text_opacity'] ) || ! is_numeric( $values['image_text_opacity'] ) ){
			$values['image_text_opacity'] = $default_values['image_text_opacity'];
		}
		if ( ! isset( $values['image_background_opacity'] ) || ! is_numeric( $values['image_background_opacity'] ) ){
			$values['image_background_opacity'] = $default_values['image_background_opacity'];
		}
		if ( ! isset( $values['image_font_size'] ) || ! is_numeric( $values['image_font_size'] ) ){
			$values['image_font_size'] = $default_values['image_font_size'];
		}
		///Value corrections

		foreach( $fields as $key => $field ){
			if ( $field['type'] === 'multi-input' ){
				foreach( $field['inputs'] as $smi_key => $smi_data ){

					if ( $field['input-type'] === 'radio' ){
						if ( isset( $values[ $key ] ) && (string) $values[ $key ] === (string) $smi_key ){
							$fields[ $key ]['value'] = $values[ $key ];
						}
					} else {
						if ( isset( $values[ $smi_key ] ) ){
							$fields[ $key ]['inputs'][ $smi_key ]['value'] = $values[ $smi_key ];
						}
					}

				}
			} else {
				if ( isset( $values[ $key ] ) ){
					$fields[ $key ]['value'] = $values[ $key ];
				}
			}
		}

		$this->settings = apply_filters( 'eeb/settings/fields', $fields );
        return $this->settings;
	}

	/**
	 * ######################
	 * ###
	 * #### VERSIONING
	 * ###
	 * ######################
	 */

	 public function load_version(){

		$current_version = get_option( $this->get_version_key() );

		if ( empty( $current_version ) ){
			$current_version = EEB_VERSION;
			update_option( $this->get_version_key(), $current_version );

			add_action( 'init', array( $this, 'first_version_init' ), $this->get_hook_priorities( 'first_version_init' ) );
		} else {
			if ( $current_version !== EEB_VERSION ){
				$this->previous_version = $current_version;
				$current_version = EEB_VERSION;
				update_option( $this->get_version_key(), $current_version );

				add_action( 'init', array( $this, 'version_update' ), $this->get_hook_priorities( 'version_update' ) );
			}
		}

		return $current_version;
	 }

	 public function load_email_image_secret(){

		if ( ! (bool) $this->get_setting( 'convert_plain_to_image', true, 'filter_body' ) ){
			return false;
		}

		$image_descret = get_option( $this->get_image_secret_key() );

		if ( ! empty( $image_descret ) ){
			return $image_descret;
		}

		$key = '';

		for ($i = 0; $i < 265; $i++) {
			$key .= chr(mt_rand(33, 126));
		}

		update_option( $this->get_image_secret_key(), $key );

		return $key;
	 }

	 /**
	  * Fires an action after our settings key was initially set
	  * the very first time.
	  *
	  * @return void
	  */
	 public function first_version_init(){
		 do_action( 'eeb/settings/first_version_init', EEB_VERSION );
	 }

	 /**
	  * Fires after the version of the plugin is initially updated
	  *
	  * @return void
	  */
	 public function version_update(){
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
	public function get_admin_cap( $target = 'main' ){
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
	public function get_page_name(){
		/*
		 * Filter the page name based on your needs
		 */
		return apply_filters( 'eeb/settings/page_name', $this->page_name );
	}

	/**
	 * Return the page title for our admin page
	 *
	 * @return string - the page title
	 */
	public function get_page_title(){
		/*
		 * Filter the page title based on your needs.
		 */
		return apply_filters( 'eeb/settings/page_title', $this->page_title );
	}

	/**
	 * Return the settings_key
	 *
	 * @return string - the settings key
	 */
	public function get_settings_key(){
		return $this->settings_key;
	}

	/**
	 * Return the version_key
	 *
	 * @return string - the version_key
	 */
	public function get_version_key(){
		return $this->version_key;
	}

	/**
	 * Return the image_secret_key
	 *
	 * @return string - the image_secret_key
	 */
	public function get_image_secret_key(){
		return $this->image_secret_key;
	}

	/**
	 * Return the email_image_secret
	 *
	 * @return string - the email_image_secret
	 */
	public function get_email_image_secret(){
		return $this->email_image_secret;
	}

	/**
	 * Return the version
	 *
	 * @return string - the version
	 */
	public function get_version(){
		return apply_filters( 'eeb/settings/get_version', $this->version );
	}

	/**
	 * Return the default template tags
	 *
	 * @return array - the template tags
	 */
	public function get_template_tags(){
		return apply_filters( 'eeb/settings/get_template_tags', $this->template_tags );
	}

	/**
	 * Return the widget callback hook name
	 *
	 * @return string - the final widget callback hook name
	 */
	public function get_widget_callback_hook(){
		return apply_filters( 'eeb/settings/widget_callback_hook', $this->widget_callback_hook );
	}

	/**
	 * Return the final output buffer hook name
	 *
	 * @return string - the final output buffer hook name
	 */
	public function get_final_outout_buffer_hook(){
		return apply_filters( 'eeb/settings/final_outout_buffer_hook', $this->final_outout_buffer_hook );
	}

	/**
	 * Return the @ symbol identifier
	 *
	 * @return string - the @ symbol identifier
	 */
	public function get_at_identifier(){
		return apply_filters( 'eeb/settings/at_identifier', $this->at_identifier );
	}

	/**
     * @link http://www.mkyong.com/regular-expressions/how-to-validate-email-address-with-regular-expression/
     * @param boolean $include
     * @return string
     */
    public function get_email_regex( $include = false ){

        if ($include === true) {
            $return = $this->email_regex;
        } else {
			$return = '/' . $this->email_regex . '/i';
		}

		return apply_filters( 'eeb/settings/get_email_regex', $return, $include );
	}

	/**
	 * Get Woocommerce variation attribute regex
	 *
     * @param boolean $include
     * @return string
     */
    public function get_soft_attribute_regex( $single = null ){

		$return = $this->soft_attribute_regex;

		if ( $single !== null ){
			if ( isset( $this->soft_attribute_regex[ $single ] ) ){
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
    public function get_hook_priorities( $single = false ){

		$return = $this->hook_priorities;
		$default = false;

		if ( $single ){
			if ( isset( $this->hook_priorities[ $single ] ) ){
				$return = $this->hook_priorities[ $single ];
			} else {
				$return = 10;
				$default = true;
			}
		}

		return apply_filters( 'eeb/settings/get_hook_priorities', $return, $default, $single );
    }

	/**
	  * Get a collection of safe HTML attributes
	  *
	  * @return array
	  */
	  public function get_safe_html_attr(){
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
	 public function get_admin_page_url(){

		$url = admin_url( "options-general.php?page=" . $this->get_page_name() );

		 return apply_filters( 'eeb/settings/get_admin_page_url', $url );
	 }

	 /**
	  * Helper function to reload the settings
	  *
	  * @return array - An array of all available settings
	  */
	 public function reload_settings(){

		$this->settings = $this->load_settings();

		 return $this->settings;
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
	public function get_setting( $slug = '', $single = false, $group = '' ){
		$return = $this->settings;

		if ( empty( $slug ) ){
			return $return;
		}

		if ( isset( $this->settings[ $slug ] ) || ( ! empty( $group ) && isset( $this->settings[ $group ] ) ) ){
			if ( $single ){
				$return = false; // Default false

				//Set default to the main valie if available given with radio buttons)
				if ( isset( $this->settings[ $slug ]['value'] ) ){
					$return = $this->settings[ $slug ]['value'];
				}

				if (
					! empty( $group )
					&& isset( $this->settings[ $group ]['type'] )
					&& $this->settings[ $group ]['type'] === 'multi-input'
					)
				{
					if ( isset( $this->settings[ $group ]['inputs'][ $slug ] ) && isset( $this->settings[ $group ]['inputs'][ $slug ]['value'] ) ){
						$return = $this->settings[ $group ]['inputs'][ $slug ]['value'];
					}
				}

			} else {

				if ( ! empty( $group ) && isset( $this->settings[ $group ] ) ){
					$return = $this->settings[ $group ];
				} else {
					$return = $this->settings[ $slug ];
				}

			}

		}

		return $return;
	}

}