<?php

namespace OnlineOptimisation\EmailEncoderBundle\Front\Shortcodes;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class EmailEncoderFormShortcode {

    use PluginHelper;

    protected string $tag = 'eeb_form';

    public function tag(): string {
        return $this->tag;
    }


	public function handle( array $atts = [], ?string $content = null ): string {

		if (
			$this->helper()->is_page( $this->getPageName() )
			|| (bool) $this->getSetting( 'encoder_form_frontend', true, 'encoder_form' )
		 ) {
			return $this->validate()->get_encoder_form();
		}

		return '';
	}

}