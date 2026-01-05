<?php

namespace OnlineOptimisation\EmailEncoderBundle\Front\Shortcodes;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class MailtoShortcode
{
    use PluginHelper;

    protected string $tag = 'eeb_mailto';

    public function tag(): string
    {
        return $this->tag;
    }


    public function handle( array $atts = [], ?string $content = null ): string
    {
        $allowed_attr_html = $this->getSafeHtmlAttr();
        $show_encoded_check = (bool) $this->getSetting( 'show_encoded_check', true );
        $protection_text = __( $this->getSetting( 'protection_text', true ), 'email-encoder-bundle' );

        if ( empty( $atts['email'] ) ) {
            return '';
        } else {
            $email = sanitize_email( $atts['email'] );
        }

        if ( empty( $atts['extra_attrs'] ) ) {
            $extra_attrs = '';
        } else {
            $extra_attrs = $atts['extra_attrs'];
        }

        if ( ! isset( $atts['method'] ) || empty( $atts['method'] ) ) {
            $protect_using = (string) $this->getSetting( 'protect_using', true );
            if ( ! empty( $protect_using ) ) {
                $method = $protect_using;
            } else {
                $method = 'rot13'; //keep as fallback
            }
        } else {
            $method = sanitize_title( $atts['method'] );
        }

        $custom_class = (string) $this->getSetting( 'class_name', true );

        if ( empty( $atts['display'] ) ) {
            $display = $email;
        } else {
            $display = wp_kses( html_entity_decode( $atts['display'] ), $allowed_attr_html );
            $display = str_replace( '\\', '', $display ); //Additionally sanitize unicode
        }

        if ( empty( $atts['noscript'] ) ) {
            $noscript = $protection_text;
        } else {
            $noscript = wp_kses( html_entity_decode( $atts['noscript'] ), $allowed_attr_html );
            $noscript = str_replace( '\\', '', $noscript ); //Additionally sanitize unicode
        }

        $class_name = ' ' . $this->helper()->sanitize_html_attributes( $extra_attrs );
        $class_name .= ' class="' . esc_attr( $custom_class ) . '"';
        $mailto = '<a href="mailto:' . $email . '"' . $class_name . '>' . $display . '</a>';

        switch ( $method ) {
            case 'enc_ascii':
            case 'rot13':
                $mailto = $this->encodeAscii( $mailto, $noscript );
                break;
            case 'enc_escape':
            case 'escape':
                $mailto = $this->encodeEscape( $mailto, $noscript );
                break;
            case 'with_javascript':
                $mailto = $this->dynamicJsEmailEncoding( $mailto, $noscript );
                break;
            case 'without_javascript':
                $mailto = $this->encodeEmailCss( $mailto );
                break;
            case 'char_encode':
                $mailto = $this->filterPlainEmails( $mailto, null, 'char_encode' );
                break;
            case 'strong_method':
                $mailto = $this->filterPlainEmails( $mailto );
                break;
            case 'enc_html':
            case 'encode':
            default:
                $mailto = '<a href="mailto:' . antispambot( $email ) . '"' . $class_name . '>' . antispambot( $display ) . '</a>';
                break;
        }

        // mark link as successfullly encoded (for admin users)
        if ( current_user_can( $this->getAdminCap( 'frontend-display-security-check' ) ) && $show_encoded_check ) {
            $mailto .= $this->getEncodedEmailIcon();
        }

        return apply_filters( 'eeb/frontend/shortcode/eeb_mailto', $mailto );
    }

}
