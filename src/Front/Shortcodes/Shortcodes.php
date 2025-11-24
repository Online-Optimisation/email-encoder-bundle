<?php

namespace OnlineOptimisation\EmailEncoderBundle\Front\Shortcodes;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class Shortcodes
{
    use PluginHelper;


    public function boot(): void {

		add_action( 'init', [ $this, 'register' ] );
    }


    public function register(): void {

        $shortcodes = [
            new ProtectContentShortcode(),
            new MailtoShortcode(),
            new EmailEncoderFormShortcode(),
            new ProtectEmailsShortcode(),
            new ContentShortcode(), // DEPRECATED
            new EmailShortcode(), // DEPRECATED
        ];

		foreach( $shortcodes as $shortcode ) {
			add_shortcode( $shortcode->tag(), [ $shortcode, 'handle' ] );
		}
    }

}