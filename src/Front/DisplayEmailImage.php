<?php

namespace OnlineOptimisation\EmailEncoderBundle\Front;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class DisplayEmailImage
{
    use PluginHelper;


    public function boot(): void
    {
        add_action( 'wp', [ $this, 'display_email_image' ], 999 );
    }


    public function display_email_image(): void
    {
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
            wp_die( __( 'GD Library Not Enabled. Please enable it first.', 'email-encoder-bundle' ) );
        }

        if ( $this->validate()->encoding->generate_email_signature( $email, $secret ) !== $hash ) {
            wp_die( __( 'Your signture is invalid.', 'email-encoder-bundle' ) );
        }

        $image = $this->validate()->encoding->email_to_image( $email );

        if ( empty( $image ) ) {
            wp_die( __( 'Your email could not be converted.', 'email-encoder-bundle' ) );
        }

        header( 'Content-type: image/png' );
        echo $image;

        exit;
    }

}
