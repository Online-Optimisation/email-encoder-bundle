<?php

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;
use OnlineOptimisation\EmailEncoderBundle\Validate\Encoding;

final class EncodingTest extends TestCase
{
    private Encoding $encoding;
    private string $testEmail = 'test@example.com';
    private string $testLink;

    protected function setUp(): void
    {
        $this->encoding = new Encoding();
        $this->testLink = '<a href="mailto:' . $this->testEmail . '" class="mail-link">Contact Us</a>';
    }

    // =========================================================
    // encode_escape() — the method that had the CVE vulnerability
    // =========================================================

    public function test_escape_does_not_use_eval(): void
    {
        $result = $this->encoding->encode_escape( $this->testLink, 'protected' );

        $this->assertStringNotContainsString( 'eval(', $result );
        $this->assertStringNotContainsString( 'eval (', $result );
    }

    public function test_escape_uses_innerhtml_with_decode(): void
    {
        $result = $this->encoding->encode_escape( $this->testLink, 'protected' );

        $this->assertStringContainsString( 'innerHTML', $result );
        $this->assertStringContainsString( 'decodeURIComponent', $result );
    }

    public function test_escape_hides_email_in_source(): void
    {
        $result = $this->encoding->encode_escape( $this->testLink, 'protected' );

        $this->assertStringNotContainsString( $this->testEmail, $result );
    }

    public function test_escape_produces_valid_structure(): void
    {
        $result = $this->encoding->encode_escape( $this->testLink, 'protected' );

        // Should have a span with an ID, a script block, and a noscript block
        $this->assertMatchesRegularExpression( '/<span id="eeb-\d+-\d+"><\/span>/', $result );
        $this->assertStringContainsString( '<script type="text/javascript">', $result );
        $this->assertStringContainsString( '</script>', $result );
        $this->assertStringContainsString( '<noscript>', $result );
    }

    public function test_escape_percent_encodes_content(): void
    {
        $result = $this->encoding->encode_escape( $this->testLink, 'protected' );

        // The content between decodeURIComponent(" and ") should be percent-encoded
        preg_match( '/decodeURIComponent\("([^"]+)"\)/', $result, $matches );
        $this->assertNotEmpty( $matches[1], 'Should contain percent-encoded content' );

        // Decode it and verify it contains the original link HTML
        $decoded = urldecode( $matches[1] );
        $this->assertStringContainsString( 'mailto:', $decoded );
        $this->assertStringContainsString( 'Contact Us', $decoded );
    }

    public function test_escape_noscript_contains_protection_text(): void
    {
        $result = $this->encoding->encode_escape( $this->testLink, 'Contact us for help' );

        preg_match( '/<noscript>(.*?)<\/noscript>/s', $result, $matches );
        $this->assertNotEmpty( $matches[1] );
        $this->assertEquals( 'Contact us for help', $matches[1] );
    }

    public function test_escape_escapes_html_in_protection_text(): void
    {
        $result = $this->encoding->encode_escape( $this->testLink, '<script>alert(1)</script>' );

        preg_match( '/<noscript>(.*?)<\/noscript>/s', $result, $matches );
        $this->assertNotEmpty( $matches[1] );
        $this->assertStringNotContainsString( '<script>', $matches[1] );
        $this->assertStringContainsString( '&lt;script&gt;', $matches[1] );
    }

    public function test_escape_generates_unique_element_ids(): void
    {
        $result1 = $this->encoding->encode_escape( $this->testLink, 'protected' );
        $result2 = $this->encoding->encode_escape( $this->testLink, 'protected' );

        preg_match( '/id="(eeb-[^"]+)"/', $result1, $matches1 );
        preg_match( '/id="(eeb-[^"]+)"/', $result2, $matches2 );

        $this->assertNotEmpty( $matches1[1] );
        $this->assertNotEmpty( $matches2[1] );
        $this->assertNotEquals( $matches1[1], $matches2[1] );
    }

    // =========================================================
    // encode_ascii() — ROT13 / ASCII method
    // =========================================================

    public function test_ascii_hides_email_in_source(): void
    {
        $result = $this->encoding->encode_ascii( $this->testLink, 'protected' );

        $this->assertStringNotContainsString( $this->testEmail, $result );
    }

    public function test_ascii_contains_expected_js_structure(): void
    {
        $result = $this->encoding->encode_ascii( $this->testLink, 'protected' );

        $this->assertStringContainsString( 'var ml=', $result );
        $this->assertStringContainsString( 'mi=', $result );
        $this->assertStringContainsString( 'decodeURIComponent', $result );
        $this->assertStringContainsString( 'innerHTML', $result );
    }

    public function test_ascii_escapes_protection_text_in_noscript(): void
    {
        $result = $this->encoding->encode_ascii( $this->testLink, '<script>alert(1)</script>' );

        preg_match( '/<noscript>(.*?)<\/noscript>/s', $result, $matches );
        $this->assertNotEmpty( $matches[1] );
        $this->assertStringNotContainsString( '<script>', $matches[1] );
        $this->assertStringContainsString( '&lt;script&gt;', $matches[1] );
    }

    public function test_ascii_produces_valid_structure(): void
    {
        $result = $this->encoding->encode_ascii( $this->testLink, 'protected' );

        $this->assertMatchesRegularExpression( '/<span id="eeb-\d+-\d+"><\/span>/', $result );
        $this->assertStringContainsString( '<script type="text/javascript">', $result );
        $this->assertStringContainsString( '(function() {', $result );
        $this->assertStringContainsString( '<noscript>', $result );
    }

    // =========================================================
    // encode_email_css() — CSS direction method
    // =========================================================

    public function test_css_hides_email_in_source(): void
    {
        // Use plain email as input since encode_email_css strips HTML tags first
        $result = $this->encoding->encode_email_css( $this->testEmail );

        $this->assertStringNotContainsString( $this->testEmail, $result );
    }

    public function test_css_does_not_use_javascript(): void
    {
        $result = $this->encoding->encode_email_css( $this->testEmail );

        $this->assertStringNotContainsString( '<script', $result );
    }

    public function test_css_uses_expected_css_classes(): void
    {
        $result = $this->encoding->encode_email_css( $this->testEmail );

        $this->assertStringContainsString( 'eeb', $result );
        $this->assertStringContainsString( 'eeb-sd', $result );
        $this->assertStringContainsString( 'eeb-nodis', $result );
    }

    // =========================================================
    // XSS attack vectors — CVE-2026-2840
    // =========================================================

    public function test_xss_single_quote_escape_method_is_safe(): void
    {
        $malicious = "X';alert(document.domain);//";
        $link = '<a href="mailto:' . $this->testEmail . '">' . $malicious . '</a>';
        $result = $this->encoding->encode_escape( $link, 'protected' );

        // No eval means no JS string breakout possible
        $this->assertStringNotContainsString( 'eval(', $result );

        // Decode the percent-encoded content and verify the single quote
        // is present in the decoded HTML but not in a JS execution context
        preg_match( '/decodeURIComponent\("([^"]+)"\)/', $result, $matches );
        $this->assertNotEmpty( $matches[1] );
        $decoded = urldecode( $matches[1] );
        // The decoded content is assigned to innerHTML, so it's treated as HTML not JS
        $this->assertStringContainsString( "X'", $decoded );
    }

    public function test_xss_single_quote_ascii_method_is_safe(): void
    {
        $malicious = "X';alert(document.domain);//";
        $link = '<a href="mailto:' . $this->testEmail . '">' . $malicious . '</a>';
        $result = $this->encoding->encode_ascii( $link, 'protected' );

        // ASCII method uses innerHTML assignment, not eval
        $this->assertStringContainsString( 'innerHTML', $result );
        $this->assertStringNotContainsString( 'eval(', $result );

        // The ml/mi variables use double-quoted JS strings with proper escaping
        // Single quotes in the content should not break the JS
        preg_match( '/var ml="([^"]*)"/', $result, $ml_matches );
        $this->assertNotEmpty( $ml_matches[1], 'ml variable should exist' );
    }

    public function test_xss_protection_text_escape_method(): void
    {
        $result = $this->encoding->encode_escape( $this->testLink, '<script>alert("xss")</script>' );

        preg_match( '/<noscript>(.*?)<\/noscript>/s', $result, $matches );
        $this->assertStringNotContainsString( '<script>', $matches[1] );
    }

    public function test_xss_protection_text_ascii_method(): void
    {
        $result = $this->encoding->encode_ascii( $this->testLink, '<script>alert("xss")</script>' );

        preg_match( '/<noscript>(.*?)<\/noscript>/s', $result, $matches );
        $this->assertStringNotContainsString( '<script>', $matches[1] );
    }

    // =========================================================
    // Special characters — encoding should handle these safely
    // =========================================================

    public function test_escape_handles_apostrophe_in_content(): void
    {
        $link = '<a href="mailto:' . $this->testEmail . '">O\'Brien</a>';
        $result = $this->encoding->encode_escape( $link, 'protected' );

        preg_match( '/decodeURIComponent\("([^"]+)"\)/', $result, $matches );
        $decoded = urldecode( $matches[1] );
        $this->assertStringContainsString( "O'Brien", $decoded );
    }

    public function test_escape_handles_ampersand_in_content(): void
    {
        $link = '<a href="mailto:' . $this->testEmail . '">Smith &amp; Co</a>';
        $result = $this->encoding->encode_escape( $link, 'protected' );

        preg_match( '/decodeURIComponent\("([^"]+)"\)/', $result, $matches );
        $decoded = urldecode( $matches[1] );
        $this->assertStringContainsString( 'Smith &amp; Co', $decoded );
    }

    public function test_ascii_handles_apostrophe_in_content(): void
    {
        $link = '<a href="mailto:' . $this->testEmail . '">O\'Brien</a>';
        $result = $this->encoding->encode_ascii( $link, 'protected' );

        $this->assertStringContainsString( 'innerHTML', $result );
        $this->assertStringNotContainsString( $this->testEmail, $result );

        // Verify the apostrophe survives by checking it's in the ml character map
        preg_match( '/var ml="([^"]*)"/', $result, $ml );
        $this->assertNotEmpty( $ml[1] );
        // The URI-encoded apostrophe (%27) or literal ' should be in the character map
        $this->assertTrue(
            strpos( $ml[1], "'" ) !== false || strpos( $ml[1], '%27' ) !== false,
            'Apostrophe should be present in the character map'
        );
    }

    public function test_ascii_handles_quotes_in_content(): void
    {
        $link = '<a href="mailto:' . $this->testEmail . '">Say &quot;Hello&quot;</a>';
        $result = $this->encoding->encode_ascii( $link, 'protected' );

        $this->assertStringContainsString( 'innerHTML', $result );
        $this->assertStringNotContainsString( $this->testEmail, $result );

        // Verify the encoded quote entity survives in the character map
        preg_match( '/var ml="([^"]*)"/', $result, $ml );
        $this->assertNotEmpty( $ml[1] );
    }
}
