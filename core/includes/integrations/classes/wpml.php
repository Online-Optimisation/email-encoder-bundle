<?php

namespace Legacy\EmailEncoderBundle\Integration;

class Wpml {

    public function boot(): void {
        add_filter( 'eeb/settings/fields', [ $this, 'deactivate_logic' ], 10 );
    }

    public function is_active() {
        return defined( 'ICL_SITEPRESS_VERSION' );
    }

    public function deactivate_logic( $fields ) {

        if ( ! $this->is_active() ) {
            return $fields;
        }

        if ( is_user_logged_in() && isset( $_GET['wpml-app'] ) && ! empty( $_GET['wpml-app'] ) ) {
            if ( is_array( $fields ) ) {
                if ( isset( $fields[ 'protect' ] ) ) {
                    if ( isset( $fields[ 'protect' ]['value'] ) ) {
                        $fields[ 'protect' ]['value'] = 2;
                    }
                }
            }
        }

        return $fields;
    }

}
