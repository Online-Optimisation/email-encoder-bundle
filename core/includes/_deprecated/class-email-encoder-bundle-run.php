<?php
/**
 * Class Email_Encoder_Run
 *
 * Thats where we bring the plugin to life
 *
 * @since 2.0.0
 * @package EEB
 * @author Ironikus <info@ironikus.com>
 */

class Email_Encoder_Run {

	private string $page_name;
	private string $page_title;
	private string $final_outout_buffer_hook;
	private string $widget_callback_hook;

	/**
	 * Our Email_Encoder_Run constructor.
	 */
	function __construct() {
		$this->page_name    			= EEB()->settings->get_page_name();
		$this->page_title   			= EEB()->settings->get_page_title();
		$this->final_outout_buffer_hook = EEB()->settings->get_final_outout_buffer_hook();
		$this->widget_callback_hook 	= EEB()->settings->get_widget_callback_hook();

		add_action( 'init', [ $this, 'add_hooks' ] );
		add_action( 'init', [ $this, 'add_shortcodes' ] );
	}

	/**
	 * Define all of our necessary hooks
	 */
	public function add_hooks() {

		$filter_hook = (bool) EEB()->settings->get_setting( 'filter_hook', true, 'filter_body' );
		$hook_name = $filter_hook ? 'init' : 'wp';

		$actions = [
			[ 'wp', 'display_email_image' ],
			[ 'init', 'buffer_final_output' ],
			[ 'init', 'add_custom_template_tags' ],
			[ $hook_name, 'setup_single_filter_hooks' ],
			[ 'wp_enqueue_scripts', 'load_frontend_header_styling' ],
			[ 'init', 'reload_settings_for_integrations' ],
		];

		foreach ( $actions as [ $tag, $method ] ) {
			$priority = EEB()->settings->get_hook_priorities( $method );

			add_action( $tag, [ $this, $method ], $priority, 0 );
		}

		do_action( 'eeb_ready', [ $this, 'eeb_ready_callback_filter' ], $this );
	}


	public function add_shortcodes() {

		$shortcodes = [
			[ 'eeb_protect_emails', 'protect_content_shortcode' ],
			[ 'eeb_protect_content', 'shortcode_eeb_content' ],
			[ 'eeb_mailto', 'shortcode_eeb_email' ],
			[ 'eeb_form', 'shortcode_email_encoder_form' ],
			[ 'eeb_content', 'shortcode_eeb_content' ],
			[ 'eeb_email', 'shortcode_eeb_email' ],
		];

		foreach( $shortcodes as [ $code, $method ] ) {
			add_shortcode( $code, [ $this, $method ] );
		}

		// add_shortcode( 'eeb_protect_emails', [ $this, 'protect_content_shortcode' ] );
		// add_shortcode( 'eeb_protect_content', array( $this, 'shortcode_eeb_content' ) );
		// add_shortcode( 'eeb_mailto', array( $this, 'shortcode_eeb_email' ) );
		// add_shortcode( 'eeb_form', array( $this, 'shortcode_email_encoder_form' ) );

		//BAckwards compatibility
		// add_shortcode( 'eeb_content', array( $this, 'shortcode_eeb_content' ) );
		// add_shortcode( 'eeb_email', array( $this, 'shortcode_eeb_email' ) );
	}

	/**
	 * ######################
	 * ###
	 * #### CALLBACK FILTERS
	 * ###
	 * ######################
	 */

	 /**
	 * WP filter callback
	 * @param string $content
	 * @return string
	 */
	public function eeb_ready_callback_filter( $content ) {

		$apply_protection = true;

		if( EEB()->validate->is_query_parameter_excluded() ) {
			$apply_protection = false;
		}

		if( EEB()->validate->is_post_excluded() ) {
			$apply_protection = false;
		}

		$apply_protection = apply_filters( 'eeb/frontend/apply_protection', $apply_protection );

		if( ! $apply_protection ) {
			return $content;
		}

		$protect_using = (string) EEB()->settings->get_setting( 'protect_using', true );

		return EEB()->validate->filter_content( $content, $protect_using );
	}

	/**
	 * Reload the settings to reflect
	 * Third party and integration changes
	 *
	 * @since 2.1.6
	 * @return void
	 */
	public function reload_settings_for_integrations() {
		EEB()->settings->reload_settings();
	}

	/**
	 * ######################
	 * ###
	 * #### PAGE BUFFERING & WIDGET FILTER
	 * ###
	 * ######################
	 */

	 /**
	  * Buffer the final output on the init hook
	  *
	  * @return void
	  */
	public function buffer_final_output() {

		if( defined( 'WP_CLI' ) || defined( 'DOING_CRON' ) ) {
			return;
		}

		if( wp_doing_ajax() ) {
			//Maybe allow filtering for ajax requests
			$filter_ajax_requests = (int) EEB()->settings->get_setting( 'ajax_requests', true, 'filter_body' );
			if( $filter_ajax_requests !== 1 ) {
				return;
			}

		}

		if( is_admin() ) {

			//Maybe allow filtering for admin requests
			$filter_admin_requests = (int) EEB()->settings->get_setting( 'admin_requests', true, 'filter_body' );
			if( $filter_admin_requests !== 1 ) {
				return;
			}

		}

		ob_start( array( $this, 'apply_content_filter' ) );
	}

	 /**
	 * Apply the callabla function for ob_start()
	 *
	 * @param string $content
	 * @return string - the filtered content
	 */
	public function apply_content_filter( $content ) {
		$filteredContent = apply_filters( $this->final_outout_buffer_hook, $content );

		// remove filters after applying to prevent multiple applies
		remove_all_filters( $this->final_outout_buffer_hook );

		return $filteredContent;
	}

	/**
	 * Filter for "dynamic_sidebar_params" hook
	 *
	 * @deprecated 2.1.4
	 * @global array $wp_registered_widgets
	 * @param  array $params
	 * @return array
	 */
	public function eeb_dynamic_sidebar_params( $params) {
		global $wp_registered_widgets;

		if ( is_admin() ) {
			return $params;
		}

		$widget_id = $params[0]['widget_id'];

		// prevent overwriting when already set by another version of the widget output class
		if ( isset( $wp_registered_widgets[ $widget_id ]['_wo_original_callback'] ) ) {
			return $params;
		}

		$wp_registered_widgets[ $widget_id ]['_wo_original_callback'] = $wp_registered_widgets[ $widget_id ]['callback'];
		$wp_registered_widgets[ $widget_id ]['callback'] = array( $this, 'call_widget_callback' );

		return $params;
	}

	/**
	 * The Widget Callback
	 *
	 * @deprecated 2.1.4
	 * @global array $wp_registered_widgets
	 */
	public function call_widget_callback() {
		global $wp_registered_widgets;

		$original_callback_params = func_get_args();
		$original_callback = null;

		$widget_id = $original_callback_params[0]['widget_id'];

		$original_callback = $wp_registered_widgets[ $widget_id ]['_wo_original_callback'];
		$wp_registered_widgets[ $widget_id ]['callback'] = $original_callback;

		$widget_id_base = ( isset( $wp_registered_widgets[ $widget_id ]['callback'][0]->id_base ) ) ? $wp_registered_widgets[ $widget_id ]['callback'][0]->id_base : 0;

		if ( is_callable( $original_callback ) ) {
			ob_start();
			call_user_func_array( $original_callback, $original_callback_params );
			$widget_output = ob_get_clean();

			echo apply_filters( $this->widget_callback_hook, $widget_output, $widget_id_base, $widget_id );

			// remove filters after applying to prevent multiple applies
			remove_all_filters( $this->widget_callback_hook );
		}
	}

	/**
	 * ######################
	 * ###
	 * #### SCRIPT ENQUEUEMENTS
	 * ###
	 * ######################
	 */

	public function load_frontend_header_styling() {

		$js_version  = date( "ymd-Gis", filemtime( EEB_PLUGIN_DIR . 'core/includes/assets/js/custom.js' ));
		$css_version = date( "ymd-Gis", filemtime( EEB_PLUGIN_DIR . 'core/includes/assets/css/style.css' ));
		$protect_using = (string) EEB()->settings->get_setting( 'protect_using', true );
		$footer_scripts = (bool) EEB()->settings->get_setting( 'footer_scripts', true );

		if( $protect_using === 'with_javascript' ) {
			wp_enqueue_script( 'eeb-js-frontend', EEB_PLUGIN_URL . 'core/includes/assets/js/custom.js', array( 'jquery' ), $js_version, $footer_scripts );
		}

		if(
			$protect_using === 'with_javascript'
			|| $protect_using === 'without_javascript'
		) {
			wp_register_style( 'eeb-css-frontend',    EEB_PLUGIN_URL . 'core/includes/assets/css/style.css', false,   $css_version );
			wp_enqueue_style ( 'eeb-css-frontend' );
		}

		if( (string) EEB()->settings->get_setting( 'show_encoded_check', true ) === '1' ) {
			wp_enqueue_style('dashicons');
		}

	}

	/**
	 * ######################
	 * ###
	 * #### CORE LOGIC
	 * ###
	 * ######################
	 */

	 /**
	  * Register all single filters to protect your content
	  *
	  * @return void
	  */
	public function setup_single_filter_hooks() {

		if( EEB()->validate->is_query_parameter_excluded() ) {
			return;
		}

		if( EEB()->validate->is_post_excluded() ) {
			return;
		}

		$protection_method = (int) EEB()->settings->get_setting( 'protect', true );
		$filter_rss = (int) EEB()->settings->get_setting( 'filter_rss', true, 'filter_body' );
		$remove_shortcodes_rss = (int) EEB()->settings->get_setting( 'remove_shortcodes_rss', true, 'filter_body' );
		$protect_shortcode_tags = (bool) EEB()->settings->get_setting( 'protect_shortcode_tags', true, 'filter_body' );
		$protect_shortcode_tags_valid = false;

		if ( is_feed() ) {

			if( $filter_rss === 1 ) {
				add_filter( $this->final_outout_buffer_hook, array( $this, 'filter_rss' ), EEB()->settings->get_hook_priorities( 'filter_rss' ) );
			}

			if ( $remove_shortcodes_rss ) {
				add_filter( $this->final_outout_buffer_hook, array( $this, 'callback_rss_remove_shortcodes' ), EEB()->settings->get_hook_priorities( 'callback_rss_remove_shortcodes' ) );
			}

		}

		if ( $protection_method === 2 ) {
			$protect_shortcode_tags_valid = true;

			$filter_hooks = array(
				'the_title',
				'the_content',
				'the_excerpt',
				'get_the_excerpt',

				//Comment related
				'comment_text',
				'comment_excerpt',
				'comment_url',
				'get_comment_author_url',
				'get_comment_author_url_link',

				//Widgets
				'widget_title',
				'widget_text',
				'widget_content',
				'widget_output',
			);

			$filter_hooks = apply_filters( 'eeb/frontend/wordpress_filters', $filter_hooks );

			foreach ( $filter_hooks as $hook ) {
			   add_filter( $hook, array( $this, 'filter_content' ), EEB()->settings->get_hook_priorities( 'filter_content' ) );
			}
		} elseif ( $protection_method === 1 ) {
			$protect_shortcode_tags_valid = true;

			add_filter( $this->final_outout_buffer_hook, array( $this, 'filter_page' ), EEB()->settings->get_hook_priorities( 'filter_page' ) );
		}

		if ( $protect_shortcode_tags_valid ) {
			if ( $protect_shortcode_tags ) {
				add_filter( 'do_shortcode_tag', array( $this, 'filter_content' ), EEB()->settings->get_hook_priorities( 'do_shortcode_tag' ) );
			}
		}

	}

	/**
	 * Filter the page itself
	 *
	 * @param string $content
	 * @return string
	 */
	public function filter_page( $content ) {
		$protect_using = (string) EEB()->settings->get_setting( 'protect_using', true );

		return EEB()->validate->filter_page( $content, $protect_using );
	}

	/**
	 * Filter the whole content
	 *
	 * @param string $content
	 * @return string
	 */
	public function filter_content( $content ) {
		$protect_using = (string) EEB()->settings->get_setting( 'protect_using', true );
		return EEB()->validate->filter_content( $content, $protect_using );
	}

	/**
	 * Filter the rss content
	 *
	 * @param string $content
	 * @return string
	 */
	public function filter_rss( $content ) {
		$protection_type = (string) EEB()->settings->get_setting( 'protect_using', true );
		return EEB()->validate->filter_rss( $content, $protection_type );
	}

	/**
	 * RSS Callback Remove shortcodes
	 * @param string $content
	 * @return string
	 */
	public function callback_rss_remove_shortcodes( $content ) {
		// strip shortcodes like [eeb_content], [eeb_form]
		$content = strip_shortcodes($content);

		return $content;
	}

	/**
	 * ######################
	 * ###
	 * #### SHORTCODES
	 * ###
	 * ######################
	 */

	 /**
	 * Handle content filter shortcode
	 * @param array   $atts
	 * @param string  $content
	 */
	public function protect_content_shortcode( $atts, $content = null ) {
		$protect = (int) EEB()->settings->get_setting( 'protect', true );
		$allowed_attr_html = EEB()->settings->get_safe_html_attr();
		$protect_using = (string) EEB()->settings->get_setting( 'protect_using', true );
		$protection_activated = ( $protect === 1 || $protect === 2 ) ? true : false;

		if ( ! $protection_activated ) {
			return $content;
		}

		if( isset( $atts['protect_using'] ) ) {
			$protect_using = sanitize_title( $atts['protect_using'] );
		}

		//Filter content first
		$content = wp_kses( html_entity_decode( $content ), $allowed_attr_html );

		$content = EEB()->validate->filter_content( $content, $protect_using );

		return $content;
	}

	 /**
	 * Return the email encoder form
	 * @param array   $atts
	 * @param string  $content
	 */
	public function shortcode_email_encoder_form( $atts = array(), $content = null ) {

		if(
			EEB()->helpers->is_page( $this->page_name )
			|| (bool) EEB()->settings->get_setting( 'encoder_form_frontend', true, 'encoder_form' )
		 ) {
			return EEB()->validate->get_encoder_form();
		}

		return '';
	}

	 /**
	 * Return the encoded content
	 * @param array   $atts
	 * @param string  $content
	 */
	public function shortcode_eeb_content( $atts = array(), $content = null ) {

		$original_content = $content;
		$allowed_attr_html = EEB()->settings->get_safe_html_attr();
		$show_encoded_check = (string) EEB()->settings->get_setting( 'show_encoded_check', true );

		if( ! isset( $atts['protection_text'] ) ) {
			$protection_text = __( EEB()->settings->get_setting( 'protection_text', true ), 'email-protection-text-eeb-content' );
		} else {
			$protection_text = wp_kses_post( $atts['protection_text'] );
		}

		if( isset( $atts['method'] ) ) {
			$method = sanitize_title( $atts['method'] );
		} else {
			$method = 'rot13';
		}

		$content = wp_kses( html_entity_decode( $content ), $allowed_attr_html );

		if( isset( $atts['do_shortcode'] ) && $atts['do_shortcode'] === 'yes' ) {
			$content = do_shortcode( $content );
		}

		switch( $method ) {
			case 'enc_ascii':
			case 'rot13':
				$content = EEB()->validate->encode_ascii( $content, $protection_text );
				break;
			case 'enc_escape':
			case 'escape':
				$content = EEB()->validate->encode_escape( $content, $protection_text );
				break;
			case 'enc_html':
			case 'encode':
			default:
				$content = antispambot( $content );
				break;
		}

		 // mark link as successfullly encoded (for admin users)
		 if ( current_user_can( EEB()->settings->get_admin_cap( 'frontend-display-security-check' ) ) && $show_encoded_check ) {
			$content .= EEB()->validate->get_encoded_email_icon();
		}

		return apply_filters( 'eeb/frontend/shortcode/eeb_protect_content', $content, $atts, $original_content );
	}

	 /**
	 * Return the encoded email
	 * @param array   $atts
	 * @param string  $content
	 */
	public function shortcode_eeb_email( $atts = array(), $content = null ) {

		$allowed_attr_html = EEB()->settings->get_safe_html_attr();
		$show_encoded_check = (bool) EEB()->settings->get_setting( 'show_encoded_check', true );
		$protection_text = __( EEB()->settings->get_setting( 'protection_text', true ), 'email-encoder-bundle' );

		if( empty( $atts['email'] ) ) {
			return '';
		} else {
			$email = sanitize_email( $atts['email'] );
		}

		if( empty( $atts['extra_attrs'] ) ) {
			$extra_attrs = '';
		} else {
			$extra_attrs = $atts['extra_attrs'];
		}

		if( ! isset( $atts['method'] ) || empty( $atts['method'] ) ) {
			$protect_using = (string) EEB()->settings->get_setting( 'protect_using', true );
			if( ! empty( $protect_using ) ) {
				$method = $protect_using;
			} else {
				$method = 'rot13'; //keep as fallback
			}
		} else {
			$method = sanitize_title( $atts['method'] );
		}

		$custom_class = (string) EEB()->settings->get_setting( 'class_name', true );

		if( empty( $atts['display'] ) ) {
			$display = $email;
		} else {
			$display = wp_kses( html_entity_decode( $atts['display'] ), $allowed_attr_html );
			$display = str_replace( '\\', '', $display ); //Additionally sanitize unicode
		}

		if( empty( $atts['noscript'] ) ) {
			$noscript = $protection_text;
		} else {
			$noscript = wp_kses( html_entity_decode( $atts['noscript'] ), $allowed_attr_html );
			$noscript = str_replace( '\\', '', $noscript ); //Additionally sanitize unicode
		}

		$class_name = ' ' . EEB()->helpers->sanitize_html_attributes( $extra_attrs );
		$class_name .= ' class="' . esc_attr( $custom_class ) . '"';
		$mailto = '<a href="mailto:' . $email . '"'. $class_name . '>' . $display . '</a>';

		switch( $method ) {
			case 'enc_ascii':
			case 'rot13':
				$mailto = EEB()->validate->encode_ascii( $mailto, $noscript );
				break;
			case 'enc_escape':
			case 'escape':
				$mailto = EEB()->validate->encode_escape( $mailto, $noscript );
				break;
			case 'with_javascript':
				$mailto = EEB()->validate->dynamic_js_email_encoding( $mailto, $noscript );
				break;
			case 'without_javascript':
				$mailto = EEB()->validate->encode_email_css( $mailto );
				break;
			case 'char_encode':
				$mailto = EEB()->validate->filter_plain_emails( $mailto, null, 'char_encode' );
				break;
			case 'strong_method':
				$mailto = EEB()->validate->filter_plain_emails( $mailto );
				break;
			case 'enc_html':
			case 'encode':
			default:
				$mailto = '<a href="mailto:' . antispambot( $email ) . '"'. $class_name . '>' . antispambot( $display ) . '</a>';
				break;
		}

		// mark link as successfullly encoded (for admin users)
		if ( current_user_can( EEB()->settings->get_admin_cap( 'frontend-display-security-check' ) ) && $show_encoded_check ) {
			$mailto .= EEB()->validate->get_encoded_email_icon();
		}

		return apply_filters( 'eeb/frontend/shortcode/eeb_mailto', $mailto );
	}

	/**
	 * ######################
	 * ###
	 * #### EMAIL IMAGE
	 * ###
	 * ######################
	 */

	 public function display_email_image() {

		if( ! isset( $_GET['eeb_mail'] ) ) {
			return;
		}

		$email = sanitize_email( base64_decode( $_GET['eeb_mail'] ) );

		if( ! is_email( $email ) || ! isset( $_GET['eeb_hash'] ) ) {
			return;
		}

		$hash = (string) $_GET['eeb_hash'];
		$secret = EEB()->settings->get_email_image_secret();

		if( ! function_exists( 'imagefontwidth' ) ) {
			wp_die( __('GD Library Not Enabled. Please enable it first.', 'email-encoder-bundle') );
		}

		if( EEB()->validate->generate_email_signature( $email, $secret ) !== $hash ) {
			wp_die( __('Your signture is invalid.', 'email-encoder-bundle') );
		}

		$image = EEB()->validate->email_to_image( $email );

		if( empty( $image ) ) {
			wp_die( __('Your email could not be converted.', 'email-encoder-bundle') );
		}

		header('Content-type: image/png');
		echo $image;
		die();

	 }

	/**
	 * ######################
	 * ###
	 * #### TEMPLATE TAGS
	 * ###
	 * ######################
	 */

	 public function add_custom_template_tags() {
		$template_tags = EEB()->settings->get_template_tags();

		foreach( $template_tags as $hook => $callback ) {

			//Make sure we only call our own custom template tags
			if( is_callable( array( $this, $callback ) ) ) {
				apply_filters( $hook, array( $this, $callback ), 10 );
			}

		}
	 }

	 /**
	  * Filter for the eeb_filter template tag
	  *
	  * This function is called dynamically by add_custom_template_tags
	  * using the EEB()->settings->get_template_tags() callback.
	  *
	  * @param string $content - the default content
	  * @return string - the filtered content
	  */
	 public function template_tag_eeb_filter( $content ) {
		$protect_using = (string) EEB()->settings->get_setting( 'protect_using', true );
		return EEB()->validate->filter_content( $content, $protect_using );
	 }

	 /**
	  * Filter for the eeb_filter template tag
	  *
	  * This function is called dynamically by add_custom_template_tags
	  * using the EEB()->settings->get_template_tags() callback.
	  *
	  * @param string $content - the default content
	  * @return string - the filtered content
	  */
	 public function template_tag_eeb_mailto( $email, $display = null, $atts = array() ) {
		if ( is_array( $display ) ) {
			// backwards compatibility (old params: $display, $attrs = array())
			$atts   = $display;
			$display = $email;
		} else {
			$atts['href'] = 'mailto:'.$email;
		}

		return EEB()->validate->create_protected_mailto( $display, $atts );
	 }

}
