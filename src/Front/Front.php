<?php

namespace OnlineOptimisation\EmailEncoderBundle\Front;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;
use OnlineOptimisation\EmailEncoderBundle\Front\Shortcodes\Shortcodes;

class Front
{
    use PluginHelper;


    public function boot(): void
    {
        ( new DisplayEmailImage() )->boot();
        ( new FrontBuffering() )->boot();
        ( new FrontCore() )->boot();
        ( new FrontEnqueue() )->boot();
        ( new FrontTemplateTags() )->boot();
        ( new Shortcodes() )->boot();

        add_action( 'init', [ $this, 'register_hooks' ], 2000 );
    }


    public function register_hooks()
    {
        add_action( 'init', 'load_textdomain' );

        do_action( 'eeb_ready', [ $this, 'eeb_ready_callback_filter' ], $this );
    }



    public function eeb_ready_callback_filter( $content )
    {
        $apply_protection = true;

        if ( $this->isQueryParameterExcluded() ) {
            $apply_protection = false;
        }

        if ( $this->isPostExcluded() ) {
            $apply_protection = false;
        }

        $apply_protection = apply_filters( 'eeb/frontend/apply_protection', $apply_protection );

        if ( ! $apply_protection ) {
            return $content;
        }

        $protect_using = (string) $this->getSetting( 'protect_using', true );

        return $this->filterContent( $content, $protect_using );
    }



    public function load_textdomain()
    {
        load_plugin_textdomain(
            EEB_TEXTDOMAIN,
            false,
            dirname( plugin_basename( EEB_PLUGIN_FILE ) ) . '/languages/'
        );
    }

}
