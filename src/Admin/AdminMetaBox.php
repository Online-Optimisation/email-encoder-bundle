<?php

namespace OnlineOptimisation\EmailEncoderBundle\Admin;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class AdminMetaBox
{
    use PluginHelper;


    public function add_meta_box(): void
    {
        if ( !$this->helper()->is_page( $this->getPageName() ) ) {
            return;
        }

        add_meta_box(
            'encode_form',
            __( $this->getPageTitle(), 'email-encoder-bundle' ),
            [ $this, 'render' ],
            null,
            'normal',
            'core',
            [ 'encode_form' ]
        );
    }


    public function render( string $post, array $meta_box )
    {
        $key = $meta_box['args'][0];

        if ( $key !== 'encode_form' ) {
            return;
        }

        $is_form_frontend = (bool) $this->getSetting( 'encoder_form_frontend', true, 'encoder_form' );
        $encoder_form = $this->getEncoderForm();

        include EEB_PLUGIN_DIR . 'templates/admin/meta-box-content.php';
    }
}
