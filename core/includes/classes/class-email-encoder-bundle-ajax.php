<?php

namespace OnlineOptimisation\EmailEncoderBundle;

class Email_Encoder_Ajax{

    public function boot(): void {
	    add_action( 'init', [ $this, 'register_hooks' ] );
    }

    public function register_hooks(): void
    {
        $EEB  = Email_Encoder::instance();
        $page = $EEB->settings->get_page_name();

        $is_target_admin_page = $EEB->helpers->is_page( $page )
            || ( wp_doing_ajax() && ( $_POST['action'] ?? '' ) === 'eeb_get_email_form_output')
        ;

        if ( $is_target_admin_page ) {
            add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
            add_action( 'wp_ajax_eeb_get_email_form_output', [ $this, 'handle' ] );
        }

        if ( (bool) $EEB->settings->get_setting( 'encoder_form_frontend', true, 'encoder_form' ) ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
            add_action( 'wp_ajax_nopriv_eeb_get_email_form_output', [ $this, 'handle' ] );
        }
    }


    public function enqueue_scripts(): void
    {
        $file = EEB_PLUGIN_DIR . 'core/includes/assets/js/encoder-form.js';
        $ver  = file_exists( $file ) ? filemtime( $file ) : false;

        wp_enqueue_script(
            'eeb-js-ajax-ef',
            EEB_PLUGIN_URL . 'core/includes/assets/js/encoder-form.js',
            [ 'jquery' ],
            $ver,
            true
        );

        wp_localize_script( 'eeb-js-ajax-ef', 'eeb_ef', [
            'ajaxurl'  => admin_url( 'admin-ajax.php' ),
            'security' => wp_create_nonce( 'eeb_form' )
        ] );
    }


    public function handle(): void
    {
        check_ajax_referer( 'eeb_form', 'eebsec' );

        $email     = sanitize_email( $_POST['eebEmail'] ?? '' );
        $method    = sanitize_text_field( $_POST['eebMethod'] ?? '' );
        $display   = wp_kses_post( $_POST['eebDisplay'] ?? '' );
        $display   = $display ?: $email;

        $EEB       = Email_Encoder::instance();
        $class     = esc_attr( $EEB->settings->get_setting( 'class_name', true ) );
        $protect   = __( $EEB->settings->get_setting( 'protection_text', true ), 'email-encoder-bundle' );
        $link      = '<a href="mailto:' . $email . '" class="' . $class . '">' . $display . '</a>';

        switch ( $method ) {
            case 'rot13':
                $link = $EEB->validate->encode_ascii($link, $protect);
                break;

            case 'escape':
                $link = $EEB->validate->encode_escape($link, $protect);
                break;

            default:
                $link = '<a href="mailto:' . antispambot($email) . '" class="' . $class . '">' . antispambot($display) . '</a>';
        }

        # @TODO: Proper way to do this
        // wp_send_json_success( apply_filters('eeb/ajax/encoder_form_response', $link) );

        # @TODO: Old way
        echo apply_filters('eeb/ajax/encoder_form_response', $link);
        exit;
    }

}
