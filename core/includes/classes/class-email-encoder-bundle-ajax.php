<?php

/**
 * Class Email_Encoder_Ajax
 *
 * Thats where we bring the plugin to life
 *
 * @since 2.0.0
 * @package EEB
 * @author Ironikus <info@ironikus.com>
 */

namespace OnlineOptimisation\EmailEncoderBundle;

class Email_Encoder_Ajax{

	/**
	 * The main page name for our admin page
	 *
	 * @var string
	 * @since 2.0.0
	 */
	private $page_name;

	/**
	 * The main page title for our admin page
	 *
	 * @var string
	 * @since 2.0.0
	 */
	private $page_title;

	/**
	 * Our Email_Encoder_Run constructor.
	 */
	function __construct(){
		$this->page_name    = EEB()->settings->get_page_name();
		$this->page_title   = EEB()->settings->get_page_title();

		// $this->add_hooks();
		add_action( 'init', [ $this, 'add_hooks' ] );
	}

	/**
	 * Define all of our necessary hooks
	 */
	public function add_hooks(){

		if(
			EEB()->helpers->is_page( $this->page_name )
			|| ( wp_doing_ajax() && isset( $_POST['action'] ) && $_POST['action'] === 'eeb_get_email_form_output' )
		){
			add_action( 'admin_enqueue_scripts',    array( $this, 'load_ajax_scripts_styles' ), EEB()->settings->get_hook_priorities( 'load_ajax_scripts_styles_admin' ) );
			add_action( 'wp_ajax_eeb_get_email_form_output', array( $this, 'eeb_ajax_email_encoder_response' ) );
		}

		$form_frontend = (bool) EEB()->settings->get_setting( 'encoder_form_frontend', true, 'encoder_form' );

		if( $form_frontend ){
			add_action( 'wp_enqueue_scripts', array( $this, 'load_ajax_scripts_styles' ), EEB()->settings->get_hook_priorities( 'load_ajax_scripts_styles' ) );
			add_action( 'wp_ajax_nopriv_eeb_get_email_form_output', array( $this, 'eeb_ajax_email_encoder_response' ) );
		}

	}

	/**
	 * ######################
	 * ###
	 * #### SCRIPTS & STYLES
	 * ###
	 * ######################
	 */

	 /**
	 * Register all necessary scripts and styles
	 *
	 * @since    2.0.0
	 */
	public function load_ajax_scripts_styles() {

		$js_version_form  = date( "ymd-Gis", filemtime( EEB_PLUGIN_DIR . 'core/includes/assets/js/encoder-form.js' ));
		wp_enqueue_script( 'eeb-js-ajax-ef', EEB_PLUGIN_URL . 'core/includes/assets/js/encoder-form.js', array('jquery'), $js_version_form, true );
		wp_localize_script( 'eeb-js-ajax-ef', 'eeb_ef', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'security' => wp_create_nonce( $this->page_name )
		));

	}

	/**
	 * ######################
	 * ###
	 * #### CORE LOGIC
	 * ###
	 * ######################
	 */

	public function eeb_ajax_email_encoder_response(){
		check_ajax_referer( $this->page_name, 'eebsec' );

		$email = html_entity_decode( sanitize_email( $_POST['eebEmail'] ) );
		$method = sanitize_text_field( $_POST['eebMethod'] );
		$display = html_entity_decode( $_POST['eebDisplay'] );
		$custom_class = (string) EEB()->settings->get_setting( 'class_name', true );
		$protection_text = __( EEB()->settings->get_setting( 'protection_text', true ), 'email-encoder-bundle' );

		if( empty( $display ) ) {
			$display = $email;
		} else {
			$display = wp_kses_post( $display );
		}

		$display = sanitize_text_field( $display );

		$class_name = ' class="' . esc_attr( $custom_class ) . '"';
		$mailto = '<a href="mailto:' . $email . '"'. $class_name . '>' . $display . '</a>';

		switch( $method ){
			case 'rot13':
				$mailto = EEB()->validate->encode_ascii( $mailto, $protection_text );
				break;
			case 'escape':
				$mailto = EEB()->validate->encode_escape( $mailto, $protection_text );
				break;
			case 'encode':
			default:
				$mailto = '<a href="mailto:' . antispambot( $email ) . '"'. $class_name . '>' . antispambot( $display ) . '</a>';
				break;
		}

		echo apply_filters( 'eeb/ajax/encoder_form_response', $mailto );
		exit;
	 }

}
