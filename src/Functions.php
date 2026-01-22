<?php

namespace OnlineOptimisation\EmailEncoderBundle;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class Functions
{
    use PluginHelper;

    public function boot(): void
    {
        $this->log(__METHOD__);
    }


    public function eeb_form(): string
    {
        return EEB()->validate->form->get_encoder_form();
    }


    public function eeb_mailto( string $email, ?string $display = null, string $extra_attrs = '', string $method = null ): string
    {
        $custom_class = (string) EEB()->settings->get_setting( 'class_name', true );

        if( empty( $display ) ) {
			$display = $email;
        } else {
            $display = html_entity_decode($display);
		}

        $class_name = ' ' . EEB()->helpers->sanitize_html_attributes( $extra_attrs );
		$class_name .= ' class="' . esc_attr( $custom_class ) . '"';
		$mailto = '<a href="mailto:' . $email . '"'. $class_name . '>' . $display . '</a>';

		if( empty( $method ) ){
			$protect_using = (string) EEB()->settings->get_setting( 'protect_using', true );
			if( ! empty( $protect_using ) ){
				$method = $protect_using;
			}
		}

		switch( $method ){
			case 'enc_ascii':
			case 'rot13':
				$mailto = EEB()->validate->encoding->encode_ascii( $mailto, $display );
				break;
			case 'enc_escape':
			case 'escape':
				$mailto = EEB()->validate->encoding->encode_escape( $mailto, $display );
				break;
			case 'with_javascript':
				$mailto = EEB()->validate->encoding->dynamic_js_email_encoding( $mailto, $display );
				break;
			case 'without_javascript':
				$mailto = EEB()->validate->encoding->encode_email_css( $mailto );
				break;
			case 'char_encode':
				$mailto = EEB()->validate->filters->filter_plain_emails( $mailto, null, 'char_encode' );
				break;
			case 'strong_method':
				$mailto = EEB()->validate->filters->filter_plain_emails( $mailto );
				break;
			case 'enc_html':
			case 'encode':
			default:
				$mailto = '<a href="mailto:' . antispambot( $email ) . '"'. $class_name . '>' . antispambot( $display ) . '</a>';
				break;
		}

		return apply_filters( 'eeb/frontend/template_func/eeb_mailto', $mailto );
    }


    /**
     * Template function for encoding content
     *
     * @global Eeb_Site $Eeb_Site
     * @param string $content
     * @param string $method Optional, default null
     * @return string
     */
    public function eeb_protect_content( string $content, ?string $method = null, ?string $protection_text = null ): string
    {
        if( empty( $protection_text ) ){
			$protection_text = __( EEB()->settings->get_setting( 'protection_text', true ), 'email-encoder-bundle' );
		} else {
			$protection_text = wp_kses_post( $protection_text  );
		}

		if( ! empty( $method ) ){
			$method = sanitize_title( $method );
		} else {
			$method = 'rot13';
		}

        switch( $method ){
			case 'enc_ascii':
			case 'rot13':
				$content = EEB()->validate->encoding->encode_ascii( $content, $protection_text );
				break;
			case 'enc_escape':
			case 'escape':
				$content = EEB()->validate->encoding->encode_escape( $content, $protection_text );
				break;
			case 'enc_html':
			case 'encode':
			default:
				$content = antispambot( $content );
				break;
		}

		return apply_filters( 'eeb/frontend/template_func/eeb_protect_content', $content );
    }

    /**
     * Template function for encoding emails in the given content
     *
     * @global Eeb_Site $Eeb_Site
     * @param string $content
     * @param mixed $method
     * @param boolean $enc_mailtos  Optional, default true (deprectaed)
     * @param boolean $enc_plain_emails Optional, default true (deprectaed)
     * @param boolean $enc_input_fields Optional, default true (deprectaed)
     * @return string
     */
    public function eeb_protect_emails( string $content, $method = null, bool $enc_mailtos = true, bool $enc_plain_emails = true, bool $enc_input_fields = true ): string
    {

        //backwards compatibility for enc tags
        if( $method === null || is_bool( $method ) ){
            $protect_using = (string) EEB()->settings->get_setting( 'protect_using', true );
        } else {
            $protect_using = sanitize_title( $method );
        }

		$content =  EEB()->validate->filters->filter_content( $content, $protect_using );
		return apply_filters( 'eeb/frontend/template_func/eeb_protect_emails', $content, $protect_using );
    }
}
