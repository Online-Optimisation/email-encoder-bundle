<?php

namespace OnlineOptimisation\EmailEncoderBundle\Integration;

class OxygenBuilder {

    public function boot(): void {
        add_filter( 'eeb/settings/fields', array( $this, 'deactivate_logic' ), 10 );
    }


    public function is_active(): bool {
        return defined( 'CT_VERSION' );
    }


    public function deactivate_logic( $fields ) {

        if ( ! $this->is_active() ) {
            return $fields;
        }

        if ( isset( $_GET['ct_builder'] ) && $_GET['ct_builder'] === 'true' ) {
            if ( is_array( $fields ) ) {
                if ( isset( $fields[ 'protect' ] ) ) {
                    if ( isset( $fields[ 'protect' ]['value'] ) ) {
                        $fields[ 'protect' ]['value'] = 3;
                    }
                }
            }
        }

        return $fields;
    }

}
