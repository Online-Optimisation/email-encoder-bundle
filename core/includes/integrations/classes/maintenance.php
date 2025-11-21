<?php

namespace Legacy\EmailEncoderBundle\Integration;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

/**
 * Class Maintenance
 *
 * This class integrates support for the maintenance plugin:
 * https://wordpress.org/plugins/maintenance/
 *
 * @since 2.0.0
 * @package EEB
 * @author OnlineOptimisation <info@onlineoptimisation.com.au>
 */

class Maintenance {

    use PluginHelper;

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

        $protection_activated = (int) $this->getSetting( 'protect', true );

        if ( $protection_activated === 2 || $protection_activated === 1 ) {

            echo '<link rel="stylesheet" id="eeb-css-frontend"  href="' . EEB_PLUGIN_URL . 'core/includes/assets/css/style.css' . '" type="text/css" media="all" />';

        }
    }


    public function load_custom_scripts() {

        if ( ! $this->is_active() ) {
            return;
        }

        $protection_activated = (int) $this->getSetting( 'protect', true );
        $without_javascript = (string) $this->getSetting( 'protect_using', true );

        if ( $protection_activated === 2 || $protection_activated === 1 ) {

            if ( $without_javascript !== 'without_javascript' ) {
                echo '<script type="text/javascript" src="' . EEB_PLUGIN_URL . 'core/includes/assets/js/custom.js' . '"></script>';
            }

        }
    }

}
