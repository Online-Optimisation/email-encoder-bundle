<?php

namespace OnlineOptimisation\EmailEncoderBundle\Integration;

class HivePress {

    public function boot (): void {
        error_log( __METHOD__ );
        add_filter( 'eeb/settings/fields', [ $this, 'deactivate_logic' ], 10 );
    }


    public function is_active(): bool {
        return defined( 'HP_FILE' );
    }


    public function deactivate_logic( $fields ) {

        if ( ! $this->is_active() ) {
            return $fields;
        }

        $uri = isset( $_SERVER['REQUEST_URI'] )
            ? wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH )
            : ''
        ;

        $condition = preg_match( '#/account/listings/(\d+)/?$#', $uri )
            && is_array( $fields )
            && isset( $fields['protect']['value'] )
        ;

        if ( $condition ) {
            error_log( 'HivePress: protecting' );
            $fields[ 'protect' ]['value'] = 3;
        }

        return $fields;
    }

}
