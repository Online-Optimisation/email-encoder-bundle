<?php

namespace Legacy\EmailEncoderBundle\Integration;

class BricksBuilder {

    public function boot(): void {
        add_filter( 'eeb/settings/fields', [ $this, 'deactivate_logic' ], 10 );
    }


    public function is_active(): bool {
        return function_exists( 'bricks_is_builder' );
    }


    public function deactivate_logic( $fields ) {

        if ( ! $this->is_active() ) {
            return $fields;
        }

        if ( function_exists( 'bricks_is_builder' ) && bricks_is_builder() ) {
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
