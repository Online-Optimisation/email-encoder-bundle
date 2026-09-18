<?php

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;
use OnlineOptimisation\EmailEncoderBundle\Validate\Encoding;

/**
 * Regression test for icon-only tel: links losing their icon once "Protect custom
 * href attributes" includes tel. WP.org "Protecting tel breaks image" (neumicha,
 * Asana 1218153387684832).
 *
 * Block/builder social icons render as <a href="tel:x"><img>/<svg></a>. The custom-href
 * encoder ran the anchor's inner HTML through get_protected_display() unconditionally:
 * the CSS method strips tags (icon vanishes) and image mode replaces it with a broken
 * <img src="">. Rich display content must be kept intact, as create_protected_mailto()
 * already does; a plain-text display (the phone number itself) is still scrambled.
 */
final class CustomHrefIconDisplayTest extends TestCase
{
    private Encoding $encoding;

    protected function setUp(): void
    {
        $this->encoding = new Encoding();
    }

    private function setImageMode( bool $on ): void
    {
        $opts = get_option( 'WP_Email_Encoder_Bundle_options', [] );
        $opts['convert_plain_to_image'] = $on ? 1 : 0;
        update_option( 'WP_Email_Encoder_Bundle_options', $opts );
        EEB()->settings->reload_settings();
        EEB()->settings->load_email_image_secret();
    }

    protected function tearDown(): void
    {
        $this->setImageMode( false );
    }

    public function test_img_icon_survives_css_method(): void
    {
        $icon = '<img src="/icons/phone.svg" alt="phone">';

        $result = $this->encoding->create_protected_href_att( $icon, [ 'href' => 'tel:+4912345678' ], 'without_javascript' );

        $this->assertStringContainsString( $icon, $result, 'Icon markup must be kept intact inside the protected tel link' );
        $this->assertStringNotContainsString( 'tel:+49', $result, 'The href must still be entity-encoded' );
    }

    public function test_svg_icon_survives_image_mode(): void
    {
        $this->setImageMode( true );
        $icon = '<svg viewBox="0 0 24 24"><path d="M0 0h24v24H0z"/></svg>';

        $result = $this->encoding->create_protected_href_att( $icon, [ 'href' => 'tel:+4912345678' ], 'with_javascript' );

        $this->assertStringContainsString( $icon, $result );
        $this->assertStringNotContainsString( '<img src=""', $result, 'Image mode must not replace the icon with a broken image' );
    }

    public function test_plain_text_display_is_still_scrambled(): void
    {
        $result = $this->encoding->create_protected_href_att( '+49 123 45678', [ 'href' => 'tel:+4912345678' ], 'without_javascript' );

        $this->assertStringNotContainsString( '+49 123 45678', $result, 'A plain phone number display must still be obfuscated' );
        $this->assertStringContainsString( 'class="eeb eeb-rtl"', $result );
    }
}
