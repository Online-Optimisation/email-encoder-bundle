<?php

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;

/**
 * Regression tests for the image-mode mailto invisibility bug
 * (Asana 1211893451211575, grayscale, WP.org topic image-encoding-broken-in-version-2-2-4).
 *
 * Before the fix: when image mode was active (convert_plain_to_image=1) and a mailto
 * anchor's display was anything other than a bare email — e.g. <span>x</span> from
 * page builders, or "Contact us at x" — get_protected_display() passed the wrapped
 * markup directly to generate_email_image_url(). That function calls is_email(), which
 * rejects non-bare strings, so it returned false and we produced <img src="">. A broken
 * image renders as nothing on the frontend, making the link invisible.
 *
 * The fix: in image mode, strip tags from the display and extract the first email via
 * the plugin's email regex, then pass THAT to generate_email_image_url(). The result is
 * always a valid signed URL.
 *
 * Originally reported as WPForms-specific, but any page builder that wraps the email
 * (WPForms, Divi, Elementor, etc.) hits the same code path.
 */
final class ImageModeMailtoTest extends TestCase
{
    private function filters()
    {
        return EEB()->validate->filters;
    }

    private function setImageMode( bool $on ): void
    {
        $opts = get_option( 'WP_Email_Encoder_Bundle_options', [] );
        $opts['convert_plain_to_image'] = $on ? 1 : 0;
        update_option( 'WP_Email_Encoder_Bundle_options', $opts );
        // The plugin caches settings + image_image_secret on the `init` hook, but in the
        // PHPUnit bootstrap `init` never fires. Force both loads here so get_protected_display
        // sees the flipped setting and generate_email_image_url has a valid signing key.
        EEB()->settings->reload_settings();
        EEB()->settings->load_email_image_secret();
    }

    protected function setUp(): void
    {
        $this->setImageMode( true );
    }

    protected function tearDown(): void
    {
        $this->setImageMode( false );
    }

    // --- Baseline: bare mailto must still work ---

    public function test_bare_mailto_produces_valid_image_url(): void
    {
        $input = '<p><a href="mailto:bare@example.com">bare@example.com</a></p>';

        $out = $this->filters()->filter_content( $input, 'with_javascript' );

        $this->assertMatchesRegularExpression( '/<img src="[^"]*eeb_mail=[^"]+"/', $out );
        $this->assertStringNotContainsString( '<img src=""', $out );
    }

    // --- WPForms / page builder case: span-wrapped email ---

    public function test_span_wrapped_mailto_produces_valid_image_url(): void
    {
        $input = '<p><a href="mailto:wrapped@example.com"><span>wrapped@example.com</span></a></p>';

        $out = $this->filters()->filter_content( $input, 'with_javascript' );

        $this->assertMatchesRegularExpression( '/<img src="[^"]*eeb_mail=[^"]+"/', $out );
        $this->assertStringNotContainsString( '<img src=""', $out );
    }

    // --- Rich-text and rich-markup mailto: must never produce invisible <img src=""> ---
    //
    // These cases compose with the Divi mailto-display-clobber fix, which preserves
    // rich display markup rather than replacing it with an image. The original bug
    // (invisible <img src="">) is fixed by either branch alone — this test locks in
    // the "never invisible" invariant regardless of which branch handles the case.

    public function test_rich_text_mailto_is_not_invisible(): void
    {
        $input = '<p><a href="mailto:rich@example.com">Contact us at rich@example.com today</a></p>';

        $out = $this->filters()->filter_content( $input, 'with_javascript' );

        $this->assertStringNotContainsString( '<img src=""', $out, 'must not produce an empty <img>' );
        $this->assertStringNotContainsString( 'rich@example.com', $out, 'email must not leak in plaintext' );
    }

    public function test_divi5_link_block_mailto_is_not_invisible(): void
    {
        $input = '<a class="et_pb_link" href="mailto:divi@example.com"><span class="et_pb_link_inner">Email us at divi@example.com today</span></a>';

        $out = $this->filters()->filter_content( $input, 'with_javascript' );

        $this->assertStringNotContainsString( '<img src=""', $out, 'must not produce an empty <img>' );
        $this->assertStringNotContainsString( 'divi@example.com', $out, 'email must not leak in plaintext' );
    }

    // --- The image URL should encode the extracted email, not the raw markup ---

    public function test_extracted_email_encoded_into_image_url(): void
    {
        $input = '<p><a href="mailto:target@example.com"><span class="wrap">target@example.com</span></a></p>';

        $out = $this->filters()->filter_content( $input, 'with_javascript' );

        // eeb_mail param is base64 of the email. Verify it decodes to the extracted email.
        preg_match( '/eeb_mail=([^&"]+)/', $out, $match );
        $this->assertNotEmpty( $match[1] ?? null, 'eeb_mail param must be present' );
        $decoded = base64_decode( urldecode( $match[1] ) );
        $this->assertSame( 'target@example.com', $decoded );
    }

}
