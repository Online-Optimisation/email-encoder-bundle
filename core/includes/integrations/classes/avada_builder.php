<?php

namespace Legacy\EmailEncoderBundle\Integration;

use OnlineOptimisation\EmailEncoderBundle\Integrations\IntegrationInterface;

class AvadaBuilder implements IntegrationInterface {

    public function boot(): void {
        add_filter( 'eeb/settings/fields', [ $this, 'deactivate_logic' ], 10 );
    }

    public function is_active(): bool {
        return defined( 'FUSION_BUILDER_VERSION' );
    }


    /**
     * @param array< string, array< string, mixed > > $fields
     * @return array< string, array< string, mixed > >
     */
    public function deactivate_logic( $fields ) {

        if ( ! $this->is_active() ) {
            return $fields;
        }

        $condition = isset( $_GET['fb-edit'] )
            && isset( $fields[ 'protect' ]['value'] )
        ;

        if ( $condition ) {
            $fields[ 'protect' ]['value'] = 3; //3 equals "Do Nothing"
        }

        return $fields;
    }

}
