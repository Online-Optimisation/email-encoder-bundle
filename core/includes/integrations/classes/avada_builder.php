<?php

namespace OnlineOptimisation\EmailEncoderBundle\Integration;

class AvadaBuilder {

    public function boot() {
        add_filter( 'eeb/settings/fields', [ $this, 'deactivate_logic' ], 10 );
    }

    public function is_active() {
        return defined( 'FUSION_BUILDER_VERSION' );
    }

    public function deactivate_logic( $fields ) {

        if ( ! $this->is_active() ) {
            return $fields;
        }

        $condition = isset( $_GET['fb-edit'] )
            && is_array( $fields )
            && isset( $fields[ 'protect' ]['value'] )
        ;

        if ( $condition ) {
            $fields[ 'protect' ]['value'] = 3; //3 equals "Do Nothing"
        }

        return $fields;
    }

}
