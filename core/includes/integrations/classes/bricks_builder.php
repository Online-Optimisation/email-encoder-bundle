<?php

namespace Legacy\EmailEncoderBundle\Integration;

use OnlineOptimisation\EmailEncoderBundle\Integrations\IntegrationInterface;

class BricksBuilder implements IntegrationInterface {

    public function boot(): void {
        add_filter( 'eeb/settings/fields', [ $this, 'deactivate_logic' ], 10 );
    }


    public function is_active(): bool {
        return function_exists( 'bricks_is_builder' );
    }


    /**
     * @param array< string, array< string, mixed > > $fields
     * @return array< string, array< string, mixed > >
     */
    public function deactivate_logic( $fields ) {

        if ( ! $this->is_active() ) {
            return $fields;
        }

        if ( function_exists( 'bricks_is_builder' ) && bricks_is_builder() ) {
            if ( isset( $fields[ 'protect' ] ) ) {
                if ( isset( $fields[ 'protect' ]['value'] ) ) {
                    $fields[ 'protect' ]['value'] = 3;
                }
            }
        }

        return $fields;
    }

}
