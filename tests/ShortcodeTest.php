<?php

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;
use OnlineOptimisation\EmailEncoderBundle\Front\Shortcodes\MailtoShortcode;

final class ShortcodeTest extends TestCase
{
    private MailtoShortcode $mailto;

    protected function setUp(): void
    {
        $this->mailto = new MailtoShortcode();
    }

    // =========================================================
    // [eeb_mailto] — basic functionality
    // =========================================================

    public function test_mailto_produces_valid_output(): void
    {
        $result = $this->mailto->handle( [
            'email'   => 'test@example.com',
            'display' => 'Click Here',
            'method'  => 'encode',
        ] );

        $this->assertStringContainsString( '<a', $result );
        $this->assertStringContainsString( 'mailto:', $result );
        // antispambot() entity-encodes some characters in the display text
        // so we check the tag structure rather than the exact display string
        $this->assertStringContainsString( '</a>', $result );
    }

    public function test_mailto_empty_email_returns_empty(): void
    {
        $result = $this->mailto->handle( [ 'email' => '' ] );

        $this->assertEmpty( $result );
    }

    public function test_mailto_sanitizes_invalid_email(): void
    {
        $result = $this->mailto->handle( [
            'email'   => 'test<script>@example.com',
            'display' => 'Test',
            'method'  => 'encode',
        ] );

        $this->assertStringNotContainsString( '<script>', $result );
    }

    // =========================================================
    // [eeb_mailto] — all encoding methods via shortcode
    // =========================================================

    public function test_mailto_escape_method(): void
    {
        $result = $this->mailto->handle( [
            'email'   => 'escape@example.com',
            'display' => 'Escape Link',
            'method'  => 'escape',
        ] );

        $this->assertStringNotContainsString( 'escape@example.com', $result );
        $this->assertStringContainsString( 'decodeURIComponent', $result );
        $this->assertStringNotContainsString( 'eval(', $result );
    }

    public function test_mailto_rot13_method(): void
    {
        $result = $this->mailto->handle( [
            'email'   => 'rot13@example.com',
            'display' => 'ROT13 Link',
            'method'  => 'rot13',
        ] );

        $this->assertStringNotContainsString( 'rot13@example.com', $result );
        $this->assertStringContainsString( 'var ml=', $result );
    }

    public function test_mailto_with_javascript_method(): void
    {
        $result = $this->mailto->handle( [
            'email'   => 'withjs@example.com',
            'display' => 'JS Link',
            'method'  => 'with_javascript',
        ] );

        $this->assertStringNotContainsString( 'withjs@example.com', $result );
        // Should use either escape or ascii method (both use innerHTML)
        $this->assertStringContainsString( 'innerHTML', $result );
        $this->assertStringNotContainsString( 'eval(', $result );
    }

    public function test_mailto_without_javascript_method(): void
    {
        $result = $this->mailto->handle( [
            'email'   => 'nojs@example.com',
            'display' => 'nojs@example.com',
            'method'  => 'without_javascript',
        ] );

        // Use the email as display text so we can verify it gets obfuscated
        // (not just stripped by wp_strip_all_tags on the <a> wrapper)
        $this->assertStringNotContainsString( 'nojs@example.com', $result );
        // CSS method uses no JavaScript
        $this->assertStringNotContainsString( '<script', $result );
        // Should use CSS classes for direction-based obfuscation
        $this->assertStringContainsString( 'eeb-sd', $result );
    }

    public function test_mailto_char_encode_method(): void
    {
        $result = $this->mailto->handle( [
            'email'   => 'char@example.com',
            'display' => 'Char Link',
            'method'  => 'char_encode',
        ] );

        // char_encode uses antispambot via filter_plain_emails
        // Should produce output, and the email should be entity-encoded
        $this->assertNotEmpty( $result );
        // The raw email should not appear since antispambot converts characters
        $this->assertStringNotContainsString( 'char@example.com', $result );
    }

    public function test_mailto_strong_method(): void
    {
        $result = $this->mailto->handle( [
            'email'   => 'strong@example.com',
            'display' => 'Strong Link',
            'method'  => 'strong_method',
        ] );

        // Strong method replaces the email address with protection text
        // but keeps the surrounding HTML and display text
        $this->assertNotEmpty( $result );
        $this->assertStringNotContainsString( 'strong@example.com', $result );
        // The display text should still be present — only the email is replaced
        $this->assertStringContainsString( 'Strong Link', $result );
    }

    public function test_mailto_encode_method_uses_antispambot(): void
    {
        $result = $this->mailto->handle( [
            'email'   => 'encode@example.com',
            'display' => 'Encode Link',
            'method'  => 'encode',
        ] );

        $this->assertStringContainsString( '<a', $result );
        $this->assertStringContainsString( 'mailto:', $result );
        // antispambot converts some characters to entities — the literal email
        // should not appear fully intact
        $this->assertStringNotContainsString( 'encode@example.com', $result );
        // But the display text is also run through antispambot
        // so "Encode Link" characters may be partially entity-encoded
    }

    // =========================================================
    // [eeb_mailto] — XSS prevention (CVE-2026-2840)
    // =========================================================

    public function test_xss_single_quote_escape_method(): void
    {
        $result = $this->mailto->handle( [
            'email'   => 'xss@example.com',
            'method'  => 'escape',
            'display' => "X';alert(document.domain);//",
        ] );

        $this->assertStringNotContainsString( 'eval(', $result );
        $this->assertStringContainsString( 'innerHTML', $result );

        // Verify the decoded content contains the quote as HTML content (safe)
        // not as JavaScript code (unsafe)
        preg_match( '/decodeURIComponent\("([^"]+)"\)/', $result, $matches );
        $this->assertNotEmpty( $matches[1], 'Should have percent-encoded content' );
        $decoded = urldecode( $matches[1] );
        // The single quote should be in the HTML content (assigned to innerHTML)
        // not in a JS string context
        $this->assertStringContainsString( "X'", $decoded );
    }

    public function test_xss_single_quote_rot13_method(): void
    {
        $result = $this->mailto->handle( [
            'email'   => 'xss@example.com',
            'method'  => 'rot13',
            'display' => "X';alert(document.domain);//",
        ] );

        $this->assertStringContainsString( 'innerHTML', $result );
        $this->assertStringNotContainsString( 'eval(', $result );
    }

    public function test_xss_single_quote_with_javascript_method(): void
    {
        $result = $this->mailto->handle( [
            'email'   => 'xss@example.com',
            'method'  => 'with_javascript',
            'display' => "X';alert(1);//",
        ] );

        $this->assertStringNotContainsString( 'eval(', $result );
        $this->assertStringContainsString( 'innerHTML', $result );
    }

    public function test_xss_img_tag_injection(): void
    {
        $result = $this->mailto->handle( [
            'email'   => 'xss@example.com',
            'display' => '<img src=x onerror=alert(1)>',
            'method'  => 'encode',
        ] );

        // wp_kses strips onerror attribute
        $this->assertStringNotContainsString( 'onerror', $result );
    }

    public function test_xss_script_tag_in_display(): void
    {
        $result = $this->mailto->handle( [
            'email'   => 'xss@example.com',
            'display' => '<script>alert(1)</script>',
            'method'  => 'encode',
        ] );

        $this->assertStringNotContainsString( '<script>', $result );
    }

    public function test_xss_in_noscript_attribute(): void
    {
        $result = $this->mailto->handle( [
            'email'    => 'xss@example.com',
            'display'  => 'Test',
            'method'   => 'escape',
            'noscript' => '<script>alert(1)</script>',
        ] );

        // The noscript content goes through wp_kses then esc_html in encode_escape
        $this->assertStringNotContainsString( '<script>alert(1)</script>', $result );
    }

    // =========================================================
    // [eeb_mailto] — special characters in display text
    // =========================================================

    public function test_special_chars_apostrophe(): void
    {
        $result = $this->mailto->handle( [
            'email'   => 'test@example.com',
            'display' => "O'Brien",
            'method'  => 'escape',
        ] );

        // Should produce output without breaking
        $this->assertStringContainsString( 'decodeURIComponent', $result );

        // Decode and verify the apostrophe is preserved in the HTML
        preg_match( '/decodeURIComponent\("([^"]+)"\)/', $result, $matches );
        $decoded = urldecode( $matches[1] );
        $this->assertStringContainsString( "O'Brien", $decoded );
    }

    public function test_special_chars_ampersand(): void
    {
        $result = $this->mailto->handle( [
            'email'   => 'test@example.com',
            'display' => 'Smith &amp; Co',
            'method'  => 'escape',
        ] );

        preg_match( '/decodeURIComponent\("([^"]+)"\)/', $result, $matches );
        $decoded = urldecode( $matches[1] );
        // Verify the ampersand entity survives in the decoded HTML
        $this->assertStringContainsString( 'Smith &amp; Co', $decoded );
    }

    public function test_special_chars_quotes(): void
    {
        $result = $this->mailto->handle( [
            'email'   => 'test@example.com',
            'display' => 'Say &quot;Hello&quot;',
            'method'  => 'escape',
        ] );

        preg_match( '/decodeURIComponent\("([^"]+)"\)/', $result, $matches );
        $this->assertNotEmpty( $matches[1], 'Should have percent-encoded content' );
        $decoded = urldecode( $matches[1] );
        // Verify the display text survives in the decoded HTML
        $this->assertStringContainsString( 'Hello', $decoded );
        $this->assertStringContainsString( 'Say', $decoded );
        // The email should be hidden in the raw output (before decoding)
        $this->assertStringNotContainsString( 'test@example.com', $result );
    }

    // =========================================================
    // [eeb_mailto] — extra_attrs attribute
    // =========================================================

    public function test_extra_attrs_title(): void
    {
        $result = $this->mailto->handle( [
            'email'      => 'test@example.com',
            'display'    => 'Test',
            'method'     => 'encode',
            'extra_attrs' => 'title="Custom Tooltip"',
        ] );

        $this->assertStringContainsString( 'title=', $result );
        $this->assertStringContainsString( 'Custom Tooltip', $result );
    }

    public function test_extra_attrs_strips_dangerous_attributes(): void
    {
        $result = $this->mailto->handle( [
            'email'      => 'test@example.com',
            'display'    => 'Test',
            'method'     => 'encode',
            'extra_attrs' => 'onclick="alert(1)" title="Safe"',
        ] );

        // onclick should be stripped by sanitize_html_attributes allowlist
        $this->assertStringNotContainsString( 'onclick', $result );
        // title should be kept
        $this->assertStringContainsString( 'title=', $result );
    }
}
