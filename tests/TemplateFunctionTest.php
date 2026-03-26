<?php

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;
use OnlineOptimisation\EmailEncoderBundle\Functions;

final class TemplateFunctionTest extends TestCase
{
    private Functions $functions;

    protected function setUp(): void
    {
        $this->functions = new Functions();
    }

    // =========================================================
    // eeb_mailto() template function
    // =========================================================

    public function test_eeb_mailto_produces_output(): void
    {
        $result = $this->functions->eeb_mailto( 'test@example.com', 'Click Here' );

        $this->assertNotEmpty( $result );
    }

    public function test_eeb_mailto_sanitizes_email(): void
    {
        $result = $this->functions->eeb_mailto( 'test<script>@example.com', 'Test' );

        $this->assertStringNotContainsString( '<script>', $result );
    }

    public function test_eeb_mailto_strips_html_from_display(): void
    {
        $result = $this->functions->eeb_mailto(
            'test@example.com',
            '<script>alert(1)</script>Click Here'
        );

        // wp_kses with empty allowed tags strips all HTML
        $this->assertStringNotContainsString( '<script>', $result );
    }

    public function test_eeb_mailto_with_escape_method(): void
    {
        $result = $this->functions->eeb_mailto(
            'test@example.com',
            'Contact',
            '',
            'escape'
        );

        $this->assertStringNotContainsString( 'test@example.com', $result );
        $this->assertStringContainsString( 'decodeURIComponent', $result );
        $this->assertStringNotContainsString( 'eval(', $result );
    }

    public function test_eeb_mailto_with_rot13_method(): void
    {
        $result = $this->functions->eeb_mailto(
            'test@example.com',
            'Contact',
            '',
            'rot13'
        );

        $this->assertStringNotContainsString( 'test@example.com', $result );
        $this->assertStringContainsString( 'var ml=', $result );
    }

    public function test_eeb_mailto_with_encode_method(): void
    {
        $result = $this->functions->eeb_mailto(
            'test@example.com',
            'Contact',
            '',
            'encode'
        );

        $this->assertStringContainsString( '<a', $result );
        $this->assertStringContainsString( 'mailto:', $result );
        $this->assertStringNotContainsString( 'test@example.com', $result );
    }

    public function test_eeb_mailto_xss_in_display(): void
    {
        $result = $this->functions->eeb_mailto(
            'test@example.com',
            "X';alert(1);//",
            '',
            'escape'
        );

        $this->assertStringNotContainsString( 'eval(', $result );
        $this->assertStringContainsString( 'innerHTML', $result );
    }

    public function test_eeb_mailto_default_display_is_email(): void
    {
        // When display is null, it should default to the email address
        $result = $this->functions->eeb_mailto( 'test@example.com', null, '', 'encode' );

        $this->assertStringContainsString( '<a', $result );
        $this->assertStringContainsString( 'mailto:', $result );
    }

    public function test_eeb_mailto_extra_attrs(): void
    {
        $result = $this->functions->eeb_mailto(
            'test@example.com',
            'Contact',
            'title="Email us"',
            'encode'
        );

        $this->assertStringContainsString( 'title=', $result );
    }

    public function test_eeb_mailto_strips_dangerous_extra_attrs(): void
    {
        $result = $this->functions->eeb_mailto(
            'test@example.com',
            'Contact',
            'onclick="alert(1)" title="Safe"',
            'encode'
        );

        $this->assertStringNotContainsString( 'onclick', $result );
        $this->assertStringContainsString( 'title=', $result );
    }
}
