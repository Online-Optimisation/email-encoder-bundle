<?php

namespace Legacy\EmailEncoderBundle\Integration;

use OnlineOptimisation\EmailEncoderBundle\Integrations\IntegrationInterface;

class Wpml implements IntegrationInterface {

    public function boot(): void {
        add_filter( 'eeb/settings/fields', [ $this, 'deactivate_logic' ], 10 );
    }

    public function is_active(): bool {
        return defined( 'ICL_SITEPRESS_VERSION' );
    }

    /**
     * @param array< string, array< string, mixed > > $fields
     * @return array< string, array< string, mixed > >
     */
    public function deactivate_logic( $fields ) {

        if ( ! $this->is_active() ) {
            return $fields;
        }

        if ( is_user_logged_in() && isset( $_GET['wpml-app'] ) && ! empty( $_GET['wpml-app'] ) ) {
            if ( isset( $fields[ 'protect' ] ) ) {
                if ( isset( $fields[ 'protect' ]['value'] ) ) {
                    $fields[ 'protect' ]['value'] = 2;
                }
            }
        }

        return $fields;
    }

}
