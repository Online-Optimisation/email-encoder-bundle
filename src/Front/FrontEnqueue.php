<?php

namespace OnlineOptimisation\EmailEncoderBundle\Front;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class FrontEnqueue
{
    use PluginHelper;


    public function boot(): void
    {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ] );
    }


    public function enqueue(): void
    {
        $protect_using = (string) $this->getSetting( 'protect_using', true );
        $footer_scripts = (bool) $this->getSetting( 'footer_scripts', true );


        # JS
        if ( $protect_using === 'with_javascript' ) {

            $js_version = md5_file( $this->assetJsDir( 'custom.js' ) );
            wp_enqueue_script(
                'eeb-js-frontend',
                $this->assetJsUrl( 'custom.js' ),
                [ 'jquery' ],
                $js_version,
                $footer_scripts
            );
        }

        # CSS
        if ( in_array( $protect_using, [ 'with_javascript', 'without_javascript' ] ) ) {

            $css_version = md5_file( $this->assetCssDir( 'style.css' ) );
            wp_enqueue_style(
                'eeb-css-frontend',
                $this->assetCssUrl( 'style.css' ),
                false,
                $css_version
            );
        }

        if ( (string) $this->getSetting( 'show_encoded_check', true ) === '1' ) {
            wp_enqueue_style( 'dashicons' );
        }
    }

}
