<?php

namespace Legacy\EmailEncoderBundle\Integration;

/**
 * Class Email_Encoder_Integration_Maintenance
 *
 * This class integrates support for the maintenance plugin:
 * https://wordpress.org/plugins/maintenance/
 *
 * @since 2.0.0
 * @package EEB
 * @author Ironikus <info@ironikus.com>
 */

class Maintenance {

    public function boot(): void {
        add_action( 'load_custom_style', [ $this, 'load_custom_styles' ], 100 );
        add_action( 'load_custom_scripts', [ $this, 'load_custom_scripts' ], 100 );
    }


    public function is_active(): bool {
        return class_exists( 'MTNC' );
    }


    public function load_custom_styles() {

        if ( ! $this->is_active() ) {
            return;
        }

        $protection_activated = (int) EEB()->settings->get_setting( 'protect', true );

        if ( $protection_activated === 2 || $protection_activated === 1 ) {

            echo '<link rel="stylesheet" id="eeb-css-frontend"  href="' . EEB_PLUGIN_URL . 'core/includes/assets/css/style.css' . '" type="text/css" media="all" />';

        }
    }


    public function load_custom_scripts() {

        if ( ! $this->is_active() ) {
            return;
        }

        $protection_activated = (int) EEB()->settings->get_setting( 'protect', true );
        $without_javascript = (string) EEB()->settings->get_setting( 'protect_using', true );

        if ( $protection_activated === 2 || $protection_activated === 1 ) {

            if ( $without_javascript !== 'without_javascript' ) {
                echo '<script type="text/javascript" src="' . EEB_PLUGIN_URL . 'core/includes/assets/js/custom.js' . '"></script>';
            }

        }
    }

}
