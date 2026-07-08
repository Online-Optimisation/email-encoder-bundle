<?php

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;

/**
 * Regression test for the "space below the footer filled with garbage" bug
 * (reported against Helpie FAQ, but it hits any large page with a big inline
 * <script> JSON blob).
 *
 * filter_soft_dom_attributes masks the @ inside every <script> so the encoder
 * skips emails that live in inline data blobs. It did this with a single lazy
 * `preg_match_all( '/<script\b[^>]*>(.*?)<\/script>/is' )` over the whole page.
 * On a large page that regex silently returns false with
 * PREG_BACKTRACK_LIMIT_ERROR — a single continuous inline JSON blob is enough to
 * trip it, even at the default pcre.backtrack_limit. When it failed the guard
 * masked nothing, the full-page encoder replaced the plain email inside the blob
 * with `<span>…<script>…</script>` markup, and that injected raw </script>
 * prematurely closed the host script — dumping its JSON as visible page text.
 *
 * The fix: when the regex errors, fall back to a linear, backtrack-free scan so
 * the guard keeps masking. These tests force the regex to fail (via a low
 * pcre.backtrack_limit + an oversized script) and assert the blob survives.
 */
final class ScriptGuardBacktrackTest extends TestCase
{
    private string $backtrack_limit;

    protected function setUp(): void
    {
        // Reproduce an affected host: a low backtrack limit makes the lazy guard
        // regex bail on the oversized script below.
        $this->backtrack_limit = (string) ini_get( 'pcre.backtrack_limit' );
        ini_set( 'pcre.backtrack_limit', '100000' );
    }

    protected function tearDown(): void
    {
        ini_set( 'pcre.backtrack_limit', $this->backtrack_limit );
    }

    private function filters()
    {
        return EEB()->validate->filters;
    }

    /**
     * A full page whose inline <script> body is large enough that the lazy guard
     * regex trips the backtrack limit, with a plain email buried inside the blob.
     */
    private function page_with_oversized_script( string $email ): string
    {
        $padding = str_repeat( 'a1b2c3d4 ', 20000 ); // ~180 KB of script body

        return '<html><head></head><body><p>Welcome</p>'
             . '<script>window.DATA = "' . $padding . ' contact ' . $email . '";</script>'
             . '</body></html>';
    }

    public function test_guard_regex_actually_bails_on_this_input(): void
    {
        // Documents the premise: on this page the old single-regex approach errors
        // out. If this ever stops being true the other assertions are meaningless.
        $html = $this->page_with_oversized_script( 'user@example.com' );

        @preg_match_all( '/<script\b[^>]*>(.*?)<\/script>/is', $html, $matches );

        $this->assertSame( PREG_BACKTRACK_LIMIT_ERROR, preg_last_error() );
    }

    public function test_email_in_oversized_script_survives_uncorrupted(): void
    {
        $email  = 'harvest-me@example.com';
        $result = $this->filters()->filter_page( $this->page_with_oversized_script( $email ), 'with_javascript' );

        // The blob must be left intact: the email round-trips back to plain text and
        // is NOT replaced by encoder markup (which is what broke the host <script>).
        $this->assertStringContainsString( $email, $result );
        $this->assertStringNotContainsString( 'id="eeb-', $result );
        $this->assertSame( 1, substr_count( $result, '</script>' ), 'No inner </script> should be injected into the blob' );
    }

    public function test_normal_email_outside_scripts_still_encoded(): void
    {
        // Guardrail: the fix only makes the in-<script> guard reliable; plain emails
        // in ordinary markup must still be encoded as before.
        $html   = '<html><head></head><body><p>Contact: person@example.com</p></body></html>';
        $result = $this->filters()->filter_page( $html, 'with_javascript' );

        $this->assertStringContainsString( '<script', $result );
        $this->assertStringNotContainsString( 'person@example.com', $result );
    }
}
