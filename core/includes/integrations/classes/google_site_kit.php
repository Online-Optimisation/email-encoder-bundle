<?php

namespace Legacy\EmailEncoderBundle\Integration;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class GoogleSiteKit {

    use PluginHelper;

    public function boot(): void {
        add_filter( 'googlesitekit_admin_data', [ $this, 'soft_encode_admin_data' ], 100, 1 );
    }


    public function soft_encode_admin_data( $admin_data ) {

        $soft_encode = apply_filters( 'eeb/integrations/google_site_kit/soft_encode', true );

        if ( isset( $admin_data['userData'] ) && isset( $admin_data['userData']['email'] ) ) {

            $admin_data['userData']['email'] = $soft_encode
                ? antispambot( $admin_data['userData']['email'] )
                : $this->validate()->temp_encode_at_symbol( $admin_data['userData']['email'] )
            ;

        }

        return $admin_data;
    }

}
