<?php

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;

/**
 * Regression test for the PHP warning:
 *   "DOMDocument::loadHTML(): Tag defs invalid in Entity"
 *
 * filter_soft_dom_attributes() parses the whole page with DOMDocument, whose
 * HTML4 parser chokes on inline SVG (<defs>, <linearGradient>) and HTML5 tags
 * that themes/builders emit (reported on HivePress and Elementor). The page
 * still renders fine, but libxml used to raise the parse errors as PHP warnings.
 * The old code hid them with `@`, yet Query Monitor deliberately surfaces even
 * @-silenced errors — so users still saw them.
 *
 * The fix routes libxml errors into the internal buffer
 * (libxml_use_internal_errors) so no PHP warning is raised at all.
 */
final class DomWarningTest extends TestCase
{
    private function filters()
    {
        return EEB()->validate->filters;
    }

    /** A page containing inline SVG, like Elementor / HivePress output. */
    private function page_with_svg(): string
    {
        return '<html><head></head><body>'
             . '<p>Contact us at person@example.com</p>'
             . '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10">'
             . '<defs><linearGradient id="g"><stop offset="0"/></linearGradient></defs>'
             . '<rect fill="url(#g)" width="10" height="10"/></svg>'
             . '</body></html>';
    }

    /**
     * Capture EVERY error raised while running $fn, including @-silenced ones.
     * This mirrors Query Monitor (which reports silenced errors) — a handler that
     * respected error_reporting() would miss the old @-suppressed warning and let
     * the bug pass. Returns the collected error messages.
     *
     * @return array<int, string>
     */
    private function capture_errors( callable $fn ): array
    {
        $errors = [];

        set_error_handler( static function ( $errno, $errstr ) use ( &$errors ): bool {
            $errors[] = $errstr;
            return true; // handled — don't also emit through PHP
        } );

        try {
            $fn();
        } finally {
            restore_error_handler();
        }

        return $errors;
    }

    public function test_svg_page_raises_no_domdocument_warning(): void
    {
        $result = '';

        $errors = $this->capture_errors( function () use ( &$result ) {
            $result = $this->filters()->filter_soft_dom_attributes( $this->page_with_svg(), 'char_encode' );
        } );

        $dom_warnings = array_values( array_filter( $errors, static function ( string $e ): bool {
            return strpos( $e, 'DOMDocument' ) !== false || stripos( $e, 'invalid in Entity' ) !== false;
        } ) );

        $this->assertSame(
            [],
            $dom_warnings,
            'filter_soft_dom_attributes() must not raise DOMDocument/libxml warnings on inline SVG'
        );

        // Sanity: the method still processed and returned the page (SVG intact).
        $this->assertStringContainsString( '<svg', $result );
    }
}
