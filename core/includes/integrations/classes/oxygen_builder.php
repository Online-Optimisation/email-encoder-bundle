<?php

namespace Legacy\EmailEncoderBundle\Integration;

use OnlineOptimisation\EmailEncoderBundle\Integrations\IntegrationInterface;

class OxygenBuilder implements IntegrationInterface {

    public function boot(): void {
        add_filter( 'eeb/settings/fields', array( $this, 'deactivate_logic' ), 10 );
    }


    public function is_active(): bool {
        return defined( 'CT_VERSION' );
    }


    /**
     * @param array< string, array< string, mixed > > $fields
     * @return array< string, array< string, mixed > >
     */
    public function deactivate_logic( $fields ) {

        if ( ! $this->is_active() ) {
            return $fields;
        }

        if ( isset( $_GET['ct_builder'] ) && $_GET['ct_builder'] === 'true' ) {
            if ( isset( $fields[ 'protect' ] ) ) {
                if ( isset( $fields[ 'protect' ]['value'] ) ) {
                    $fields[ 'protect' ]['value'] = 3;
                }
            }
        }

        return $fields;
    }

}
