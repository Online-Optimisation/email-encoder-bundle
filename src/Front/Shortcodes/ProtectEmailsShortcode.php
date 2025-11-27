<?php

namespace OnlineOptimisation\EmailEncoderBundle\Front\Shortcodes;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class ProtectEmailsShortcode {

    use PluginHelper;

    protected string $tag = 'eeb_protect_emails';

    public function tag(): string {
        return $this->tag;
    }


	public function handle( array $atts = [], ?string $content = null ): string {

		$protect = (int) $this->getSetting( 'protect', true );
		$allowed_attr_html = $this->getSafeHtmlAttr();
		$protect_using = (string) $this->getSetting( 'protect_using', true );
		$protection_activated = ( $protect === 1 || $protect === 2 ) ? true : false;

		if ( ! $protection_activated ) {
			return $content;
		}

		if ( isset( $atts['protect_using'] ) ) {
			$protect_using = sanitize_title( $atts['protect_using'] );
		}

		//Filter content first
		$content = wp_kses( html_entity_decode( $content ), $allowed_attr_html );

		$content = $this->filterContent( $content, $protect_using );

		return $content;
	}

}