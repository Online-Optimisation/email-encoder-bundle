<?php

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;
use OnlineOptimisation\EmailEncoderBundle\Validate\Encoding;

/**
 * Regression tests for the "random characters in email" bug
 * (Asana 1212167486607990, mastflow, WP.org topic
 * /topic/random-characters-added-to-mail-addresses/).
 *
 * encode_email_css() interleaves the real email (CSS-reversed) with dummy <span>s
 * containing a Unix timestamp to confuse scrapers. The dummies rely on .eeb-nodis
 * being styled display:none. If the plugin's stylesheet fails to load — page
 * builders deferring CSS, caching layers stripping style handles, other plugins
 * dropping the enqueue — those timestamps render as visible "random characters"
 * interleaved into the email. Same for the CSS-based RTL reversal: without CSS
 * the email displays backwards.
 *
 * The fix: inline display:none on each dummy span, and inline the
 * unicode-bidi/word-break on the wrapper, so the output stays readable even
 * with zero external stylesheets.
 */
final class CssIndependenceTest extends TestCase
{
    private Encoding $encoding;

    protected function setUp(): void
    {
        $this->encoding = new Encoding();
    }

    public function test_dummy_spans_have_inline_display_none(): void
    {
        $out = $this->encoding->encode_email_css( 'user@example.com' );

        // Every eeb-nodis span must carry style="display:none" alongside the class.
        // Count class occurrences vs inline-style occurrences — they should match.
        $class_count = substr_count( $out, 'class="eeb-nodis"' );
        $style_count = substr_count( $out, 'class="eeb-nodis" style="display:none"' );

        $this->assertGreaterThan( 0, $class_count, 'encode_email_css must emit at least one eeb-nodis span' );
        $this->assertSame( $class_count, $style_count, 'every eeb-nodis span must have inline display:none' );
    }

    public function test_wrapper_has_inline_bidi_style(): void
    {
        $out = $this->encoding->encode_email_css( 'user@example.com' );

        // The outer wrapper carries either the RTL override or the word-break rule,
        // depending on the deactivate_rtl setting — both must be inline, not purely CSS-dependent.
        $has_rtl_inline = strpos( $out, 'style="unicode-bidi:bidi-override;direction:rtl"' ) !== false;
        $has_nrtl_inline = strpos( $out, 'style="word-break:break-all"' ) !== false;

        $this->assertTrue(
            $has_rtl_inline || $has_nrtl_inline,
            'outer wrapper must inline either the RTL override or word-break style'
        );
    }
}
