<?php

namespace OnlineOptimisation\EmailEncoderBundle\Integration;

class DiviTheme {

    public function boot(): void {
        add_filter( 'eeb/settings/fields', [ $this, 'deactivate_logic' ], 10 );
    }


    public function is_active(): bool {
        return defined( 'ET_BUILDER_VERSION' );
    }


    public function deactivate_logic( $fields ) {

        if ( ! $this->is_active() ) {
            return $fields;
        }

        if ( isset( $_GET['et_fb'] ) && $_GET['et_fb'] == '1' ) {
            if ( is_array( $fields ) ) {
                if ( isset( $fields['protect'] ) ) {
                    if ( isset( $fields['protect']['value'] ) ) {
                        $fields['protect']['value'] = 3;
                    }
                }
            }
        }

        return $fields;
    }

}
