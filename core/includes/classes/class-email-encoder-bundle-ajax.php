<?php

namespace Legacy\EmailEncoderBundle;

if ( ! defined( 'ABSPATH' ) ) exit;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class Email_Encoder_Ajax{

    use PluginHelper;

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

        // The [eeb_form] shortcode and eeb_form() template tag are always
        // available on the frontend; the public AJAX endpoint always
        // listens. The form JS is enqueued lazily by EncoderForm itself
        // when the form actually renders — covers shortcodes in widgets,
        // FSE blocks, theme files, and template-tag calls without
        // scanning post_content site-wide.
        add_action( 'wp_ajax_nopriv_eeb_get_email_form_output', [ $this, 'handle' ] );
    }


    public function enqueue_scripts(): void
    {
        $file = EEB_PLUGIN_DIR . 'core/includes/assets/js/encoder-form.js';
        $ver  = file_exists( $file ) ? filemtime( $file ) : false;

        wp_enqueue_script(
            'eeb-js-ajax-ef',
            EEB_PLUGIN_URL . 'core/includes/assets/js/encoder-form.js',
            [ 'jquery' ],
            (string) $ver,
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

        $class     = esc_attr( $this->getSetting( 'class_name', true ) );
        $protect   = (string) $this->getSetting( 'protection_text', true );
        $link      = '<a href="mailto:' . $email . '" class="' . $class . '">' . $display . '</a>';

        switch ( $method ) {
            case 'rot13':
                $link = $this->encodeAscii($link, $protect);
                break;

            case 'escape':
                $link = $this->encodeEscape($link, $protect);
                break;

            default:
                $link = '<a href="mailto:' . antispambot($email) . '" class="' . $class . '">' . antispambot($display) . '</a>';
        }

        # @TODO: Proper way to do this
        // wp_send_json_success( apply_filters('eeb/ajax/encoder_form_response', $link) );

        # @TODO: Old way
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is encoded email HTML built with antispambot/rot13/escape encoding; escaping would break the protection.
        echo apply_filters('eeb/ajax/encoder_form_response', $link);
        exit;
    }

}
