<?php

namespace OnlineOptimisation\EmailEncoderBundle\Front;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;
use OnlineOptimisation\EmailEncoderBundle\Front\Shortcodes\Shortcodes;

class Front
{
    use PluginHelper;


    public function boot(): void {
        $this->log( __METHOD__ );

        ( new Shortcodes() )->boot();

		add_action( 'init', [ $this, 'register_hooks' ] );
    }


	public function register_hooks() {
        $this->log( __METHOD__ );

		$filter_hook = (bool) $this->getSetting( 'filter_hook', true, 'filter_body' );
		$hook_name = $filter_hook ? 'init' : 'wp';

		$actions = [
			[ 'wp',                 'display_email_image'              ],
			[ 'init',               'load_textdomain'                  ],
			[ 'init',               'buffer_final_output'              ],
			[ 'init',               'add_custom_template_tags'         ],
			[ $hook_name,           'setup_single_filter_hooks'        ],
			[ 'wp_enqueue_scripts', 'load_frontend_header_styling'     ],
			[ 'init',               'reload_settings_for_integrations' ],
		];

		foreach ( $actions as [ $tag, $method ] ) {
			$priority = $this->getHookPriorities( $method );

			add_action( $tag, [ $this, $method ], $priority, 0 );
		}

		do_action( 'eeb_ready', [ $this, 'eeb_ready_callback_filter' ], $this );
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

		if ( $this->isQueryParameterExcluded() ) {
			$apply_protection = false;
		}

		if ( $this->isPostExcluded() ) {
			$apply_protection = false;
		}

		$apply_protection = apply_filters( 'eeb/frontend/apply_protection', $apply_protection );

		if ( ! $apply_protection ) {
			return $content;
		}

		$protect_using = (string) $this->getSetting( 'protect_using', true );

		return $this->filterContent( $content, $protect_using );
	}

	/**
	 * Reload the settings to reflect
	 * Third party and integration changes
	 *
	 * @since 2.1.6
	 * @return void
	 */
	public function reload_settings_for_integrations() {
		$this->reloadSettings();
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

		if ( defined( 'WP_CLI' ) || defined( 'DOING_CRON' ) ) {
			return;
		}

		if ( wp_doing_ajax() ) {
			//Maybe allow filtering for ajax requests
			$filter_ajax_requests = (int) $this->getSetting( 'ajax_requests', true, 'filter_body' );
			if ( $filter_ajax_requests !== 1 ) {
				return;
			}

		}

		if ( is_admin() ) {

			//Maybe allow filtering for admin requests
			$filter_admin_requests = (int) $this->getSetting( 'admin_requests', true, 'filter_body' );
			if ( $filter_admin_requests !== 1 ) {
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
		$filteredContent = apply_filters( $this->getFinalOutputBufferHook(), $content );

		// remove filters after applying to prevent multiple applies
		remove_all_filters( $this->getFinalOutputBufferHook() );

		return $filteredContent;
	}

	// /**
	//  * Filter for "dynamic_sidebar_params" hook
	//  *
	//  * @deprecated 2.1.4
	//  * @global array $wp_registered_widgets
	//  * @param  array $params
	//  * @return array
	//  */
	// public function eeb_dynamic_sidebar_params( $params) {
	// 	global $wp_registered_widgets;

	// 	if ( is_admin() ) {
	// 		return $params;
	// 	}

	// 	$widget_id = $params[0]['widget_id'];

	// 	// prevent overwriting when already set by another version of the widget output class
	// 	if ( isset( $wp_registered_widgets[ $widget_id ]['_wo_original_callback'] ) ) {
	// 		return $params;
	// 	}

	// 	$wp_registered_widgets[ $widget_id ]['_wo_original_callback'] = $wp_registered_widgets[ $widget_id ]['callback'];
	// 	$wp_registered_widgets[ $widget_id ]['callback'] = array( $this, 'call_widget_callback' );

	// 	return $params;
	// }

	// /**
	//  * The Widget Callback
	//  *
	//  * @deprecated 2.1.4
	//  * @global array $wp_registered_widgets
	//  */
	// public function call_widget_callback() {
	// 	global $wp_registered_widgets;

	// 	$original_callback_params = func_get_args();
	// 	$original_callback = null;

	// 	$widget_id = $original_callback_params[0]['widget_id'];

	// 	$original_callback = $wp_registered_widgets[ $widget_id ]['_wo_original_callback'];
	// 	$wp_registered_widgets[ $widget_id ]['callback'] = $original_callback;

	// 	$widget_id_base = ( isset( $wp_registered_widgets[ $widget_id ]['callback'][0]->id_base ) ) ? $wp_registered_widgets[ $widget_id ]['callback'][0]->id_base : 0;

	// 	if ( is_callable( $original_callback ) ) {
	// 		ob_start();
	// 		call_user_func_array( $original_callback, $original_callback_params );
	// 		$widget_output = ob_get_clean();

	// 		echo apply_filters( $this->widget_callback_hook, $widget_output, $widget_id_base, $widget_id );

	// 		// remove filters after applying to prevent multiple applies
	// 		remove_all_filters( $this->widget_callback_hook );
	// 	}
	// }

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
		$protect_using = (string) $this->getSetting( 'protect_using', true );
		$footer_scripts = (bool) $this->getSetting( 'footer_scripts', true );

		if ( $protect_using === 'with_javascript' ) {
			wp_enqueue_script( 'eeb-js-frontend', EEB_PLUGIN_URL . 'core/includes/assets/js/custom.js', array( 'jquery' ), $js_version, $footer_scripts );
		}

		if (
			$protect_using === 'with_javascript'
			|| $protect_using === 'without_javascript'
		) {
			wp_register_style( 'eeb-css-frontend',    EEB_PLUGIN_URL . 'core/includes/assets/css/style.css', false,   $css_version );
			wp_enqueue_style ( 'eeb-css-frontend' );
		}

		if ( (string) $this->getSetting( 'show_encoded_check', true ) === '1' ) {
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

		if ( $this->isQueryParameterExcluded() ) {
			return;
		}

		if ( $this->isPostExcluded() ) {
			return;
		}

		$protection_method = (int) $this->getSetting( 'protect', true );
		$filter_rss = (int) $this->getSetting( 'filter_rss', true, 'filter_body' );
		$remove_shortcodes_rss = (int) $this->getSetting( 'remove_shortcodes_rss', true, 'filter_body' );
		$protect_shortcode_tags = (bool) $this->getSetting( 'protect_shortcode_tags', true, 'filter_body' );
		$protect_shortcode_tags_valid = false;

		if ( is_feed() ) {

			if ( $filter_rss === 1 ) {
				add_filter( $this->getFinalOutputBufferHook(), array( $this, 'filter_rss' ), $this->getHookPriorities( 'filter_rss' ) );
			}

			if ( $remove_shortcodes_rss ) {
				add_filter( $this->getFinalOutputBufferHook(), array( $this, 'callback_rss_remove_shortcodes' ), $this->getHookPriorities( 'callback_rss_remove_shortcodes' ) );
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
			   add_filter( $hook, array( $this, 'filter_content' ), $this->getHookPriorities( 'filter_content' ) );
			}
		} elseif ( $protection_method === 1 ) {
			$protect_shortcode_tags_valid = true;

			add_filter( $this->getFinalOutputBufferHook(), array( $this, 'filter_page' ), $this->getHookPriorities( 'filter_page' ) );
		}

		if ( $protect_shortcode_tags_valid && $protect_shortcode_tags ) {
            add_filter( 'do_shortcode_tag', [ $this, 'filter_content' ], $this->getHookPriorities( 'do_shortcode_tag' ) );
		}

	}

	/**
	 * Filter the page itself
	 *
	 * @param string $content
	 * @return string
	 */
	public function filter_page( $content ) {
		$protect_using = (string) $this->getSetting( 'protect_using', true );

		return $this->filterPage( $content, $protect_using );
	}

	/**
	 * Filter the whole content
	 *
	 * @param string $content
	 * @return string
	 */
	public function filter_content( $content ) {
		$protect_using = (string) $this->getSetting( 'protect_using', true );
		return $this->filterContent( $content, $protect_using );
	}

	/**
	 * Filter the rss content
	 *
	 * @param string $content
	 * @return string
	 */
	public function filter_rss( $content ) {
		$protection_type = (string) $this->getSetting( 'protect_using', true );
		return $this->validate()->filter_rss( $content, $protection_type );
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
	 * #### EMAIL IMAGE
	 * ###
	 * ######################
	 */

	public function display_email_image() {

		if ( ! isset( $_GET['eeb_mail'] ) ) {
			return;
		}

		$email = sanitize_email( base64_decode( $_GET['eeb_mail'] ) );

		if ( ! is_email( $email ) || ! isset( $_GET['eeb_hash'] ) ) {
			return;
		}

		$hash = (string) $_GET['eeb_hash'];
		$secret = $this->settings()->get_email_image_secret();

		if ( ! function_exists( 'imagefontwidth' ) ) {
			wp_die( __('GD Library Not Enabled. Please enable it first.', 'email-encoder-bundle') );
		}

		if ( $this->validate()->generate_email_signature( $email, $secret ) !== $hash ) {
			wp_die( __('Your signture is invalid.', 'email-encoder-bundle') );
		}

		$image = $this->validate()->email_to_image( $email );

		if ( empty( $image ) ) {
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
        error_log( __METHOD__ );
		$template_tags = $this->getTemplateTags();

		foreach( $template_tags as $hook => $callback ) {

			//Make sure we only call our own custom template tags
			if ( is_callable( array( $this, $callback ) ) ) {
				apply_filters( $hook, array( $this, $callback ), 10 );
			}

		}
	}

	/**
	 * Filter for the eeb_filter template tag
	 *
	 * This function is called dynamically by add_custom_template_tags
	 * using the $this->getTemplateTags() callback.
	 *
	 * @param string $content - the default content
	 * @return string - the filtered content
	 */
	public function template_tag_eeb_filter( $content ) {
        error_log( __METHOD__ );
		$protect_using = (string) $this->getSetting( 'protect_using', true );
		return $this->validate()->filter_content( $content, $protect_using );
	}

	/**
	 * Filter for the eeb_filter template tag
	 *
	 * This function is called dynamically by add_custom_template_tags
	 * using the $this->getTemplateTags() callback.
	 *
	 * @param string $content - the default content
	 * @return string - the filtered content
	 */
	public function template_tag_eeb_mailto( $email, $display = null, $atts = array() ) {
        error_log( __METHOD__ );
		if ( is_array( $display ) ) {
			// backwards compatibility (old params: $display, $attrs = array())
			$atts   = $display;
			$display = $email;
		} else {
			$atts['href'] = 'mailto:'.$email;
		}

		return $this->validate()->create_protected_mailto( $display, $atts );
	}


    public function load_textdomain() {
        load_plugin_textdomain( EEB_TEXTDOMAIN, FALSE, dirname( plugin_basename( EEB_PLUGIN_FILE ) ) . '/languages/' );
    }
}