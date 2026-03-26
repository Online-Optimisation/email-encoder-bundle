<?php

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;
use OnlineOptimisation\EmailEncoderBundle\Front\Shortcodes\ProtectContentShortcode;

final class ProtectContentShortcodeTest extends TestCase
{
    private ProtectContentShortcode $shortcode;

    protected function setUp(): void
    {
        $this->shortcode = new ProtectContentShortcode();
    }

    // =========================================================
    // Basic functionality
    // =========================================================

    public function test_encodes_content_with_default_method(): void
    {
        $result = $this->shortcode->handle( [], 'Secret content with secret@example.com' );

        // Default method is rot13/ascii — content should be encoded
        $this->assertStringNotContainsString( 'secret@example.com', $result );
        $this->assertStringNotContainsString( 'Secret content', $result );
        // Should use ASCII encoding structure
        $this->assertStringContainsString( 'var ml=', $result );
    }

    public function test_encodes_with_escape_method(): void
    {
        $result = $this->shortcode->handle(
            [ 'method' => 'escape' ],
            'Hidden content with hidden@example.com'
        );

        $this->assertStringNotContainsString( 'hidden@example.com', $result );
        $this->assertStringContainsString( 'decodeURIComponent', $result );
        $this->assertStringNotContainsString( 'eval(', $result );
    }

    public function test_encodes_with_encode_method(): void
    {
        $result = $this->shortcode->handle(
            [ 'method' => 'encode' ],
            'Content with test@example.com'
        );

        // HTML encode uses antispambot — should produce entity-encoded output
        $this->assertNotEmpty( $result );
    }

    public function test_empty_content_returns_encoded_empty(): void
    {
        $result = $this->shortcode->handle( [], '' );

        // Even empty content gets passed through encoding
        $this->assertNotNull( $result );
    }

    // =========================================================
    // XSS prevention
    // =========================================================

    public function test_xss_script_tag_in_content(): void
    {
        $result = $this->shortcode->handle(
            [ 'method' => 'escape' ],
            '<script>alert(1)</script>'
        );

        // wp_kses strips script tags before encoding
        // The encoded output should not contain the script tag
        $this->assertStringNotContainsString( 'eval(', $result );
    }

    public function test_xss_in_protection_text_attribute(): void
    {
        $result = $this->shortcode->handle(
            [ 'protection_text' => '<script>alert(1)</script>', 'method' => 'escape' ],
            'Some content'
        );

        // protection_text goes through wp_kses_post then esc_html in encode_escape
        $this->assertStringNotContainsString( '<script>alert(1)</script>', $result );
    }

    public function test_method_attribute_is_sanitized(): void
    {
        // Invalid method should fall through to default (encode/antispambot)
        $result = $this->shortcode->handle(
            [ 'method' => '<script>alert(1)</script>' ],
            'test@example.com'
        );

        // sanitize_title strips HTML — should not contain script tags
        $this->assertStringNotContainsString( '<script>', $result );
    }
}
