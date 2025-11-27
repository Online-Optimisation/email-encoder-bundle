<?php

namespace OnlineOptimisation\EmailEncoderBundle\Admin;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class AdminEnqueue
{
    use PluginHelper;


    public function boot(): void {

        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
    }


	public function enqueue(): void {

		if ( !$this->helper()->is_page( $this->getPageName() ) ) {
            return;
        }

        # JS
        $js_version = md5_file( $this->assetJs( 'custom-admin.js' ) );
        wp_enqueue_script( 'eeb-admin-scripts',
            $this->assetJs( 'custom-admin.js' ),
            [ 'jquery' ],
            $js_version,
            true
        );

        # CSS
        $css_version = md5_file( $this->assetCss( 'style-admin.css' ) );
        wp_enqueue_style( 'eeb-css-backend',
            $this->assetCss( 'style-admin.css' ),
            false,
            $css_version
        );
	}

}
