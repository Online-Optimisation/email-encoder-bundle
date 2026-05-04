<?php

namespace OnlineOptimisation\EmailEncoderBundle\Admin;

if ( ! defined( 'ABSPATH' ) ) exit;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class AdminEnqueue
{
    use PluginHelper;


    public function boot(): void
    {
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
    }


    public function enqueue(): void
    {
        if ( !$this->helper()->is_page( $this->getPageName() ) ) {
            return;
        }

        # JS
        $js_version = md5_file( $this->assetJsDir( 'custom-admin.js' ) );
        wp_enqueue_script(
            'eeb-admin-scripts',
            $this->assetJsUrl( 'custom-admin.js' ),
            [ 'jquery' ],
            $js_version,
            true
        );

        wp_localize_script( 'eeb-admin-scripts', 'eebAdmin', [
            'copyFallbackPrompt' => __( 'Copy this text:', 'email-encoder-bundle' ),
        ] );

        # CSS
        $css_version = md5_file( $this->assetCssDir( 'style-admin.css' ) );
        wp_enqueue_style(
            'eeb-css-backend',
            $this->assetCssUrl( 'style-admin.css' ),
            [],
            $css_version
        );
    }

}
