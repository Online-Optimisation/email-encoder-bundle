<?php

namespace OnlineOptimisation\EmailEncoderBundle\Front\Shortcodes;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class ProtectContentShortcode
{
    use PluginHelper;

    protected string $tag = 'eeb_protect_content';

    public function tag(): string
    {
        return $this->tag;
    }


    public function handle( array $atts = [], ?string $content = null ): string
    {

        $original_content = $content;
        $allowed_attr_html = $this->getSafeHtmlAttr();
        $show_encoded_check = (string) $this->getSetting( 'show_encoded_check', true );

        if ( ! isset( $atts['protection_text'] ) ) {
            $protection_text = __( $this->getSetting( 'protection_text', true ), 'email-protection-text-eeb-content' );
        } else {
            $protection_text = wp_kses_post( $atts['protection_text'] );
        }

        if ( isset( $atts['method'] ) ) {
            $method = sanitize_title( $atts['method'] );
        } else {
            $method = 'rot13';
        }

        $content = wp_kses( html_entity_decode( $content ), $allowed_attr_html );

        if ( isset( $atts['do_shortcode'] ) && $atts['do_shortcode'] === 'yes' ) {
            $content = do_shortcode( $content );
        }

        switch ( $method ) {
            case 'enc_ascii':
            case 'rot13':
                $content = $this->encodeAscii( $content, $protection_text );
                break;
            case 'enc_escape':
            case 'escape':
                $content = $this->encodeEscape( $content, $protection_text );
                break;
            case 'enc_html':
            case 'encode':
            default:
                $content = antispambot( $content );
                break;
        }

         // mark link as successfullly encoded (for admin users)
        if ( current_user_can( $this->getAdminCap( 'frontend-display-security-check' ) ) && $show_encoded_check ) {
            $content .= $this->getEncodedEmailIcon();
        }

        return apply_filters( 'eeb/frontend/shortcode/eeb_protect_content', $content, $atts, $original_content );
    }

}
