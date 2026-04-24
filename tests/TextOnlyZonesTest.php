<?php

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;

/**
 * Regression tests for the Firefox <select><option> bug.
 *
 * <option>, <textarea>, and <title> can only contain text per HTML spec. When the plugin
 * emitted <span>/<script>/<noscript> wrappers inside them, Firefox stripped everything
 * except the <noscript> fallback (showing only "*protected email*" in dropdowns), while
 * Chrome/Edge rendered the mess inconsistently. The fix: pre-encode emails in those zones
 * as HTML entities and skip the main filter pass for them.
 */
final class TextOnlyZonesTest extends TestCase
{
    private function filters()
    {
        return EEB()->validate->filters;
    }

    // =========================================================
    // <option> — the ticket reporter's case
    // =========================================================

    public function test_option_content_has_no_script_wrapper(): void
    {
        $input = '<select><option>user@example.com</option></select>';

        $result = $this->filters()->filter_content( $input, 'with_javascript' );

        $this->assertStringNotContainsString( '<script', $result );
        $this->assertStringNotContainsString( '<span', $result );
        $this->assertStringNotContainsString( '<noscript', $result );
    }

    public function test_option_content_hides_literal_email(): void
    {
        $input = '<select><option>alice@example.com</option></select>';

        $result = $this->filters()->filter_content( $input, 'with_javascript' );

        // At minimum, the @ must be entity-encoded so naive scrapers cannot match it
        $this->assertStringNotContainsString( 'alice@example.com', $result );
    }

    public function test_option_without_javascript_mode_has_no_span_wrapper(): void
    {
        $input = '<select><option>user@example.com</option></select>';

        $result = $this->filters()->filter_content( $input, 'without_javascript' );

        $this->assertStringNotContainsString( '<span', $result );
    }

    // =========================================================
    // Regression guard — text outside text-only zones still gets JS encoding
    // =========================================================

    public function test_text_outside_option_still_uses_js_encoding(): void
    {
        $input = '<p>Contact: user@example.com</p>';

        $result = $this->filters()->filter_content( $input, 'with_javascript' );

        // The regular JS path should still fire — span + script + noscript wrapper
        $this->assertStringContainsString( '<script', $result );
        $this->assertStringContainsString( 'decodeURIComponent', $result );
    }

    public function test_mixed_content_encodes_each_zone_correctly(): void
    {
        $input = '<p>Contact: user@example.com</p>'
               . '<select><option>alice@example.com</option></select>';

        $result = $this->filters()->filter_content( $input, 'with_javascript' );

        // Single <script> tag — the one for the <p> email, not for the <option> email
        $this->assertEquals( 1, substr_count( $result, '<script' ) );
    }

    // =========================================================
    // <textarea> and <title> — same class of bug
    // =========================================================

    public function test_textarea_content_has_no_script_wrapper(): void
    {
        $input = '<textarea>Reach me at user@example.com</textarea>';

        $result = $this->filters()->filter_content( $input, 'with_javascript' );

        $this->assertStringNotContainsString( '<script', $result );
        $this->assertStringNotContainsString( '<span', $result );
    }

    public function test_title_content_has_no_script_wrapper(): void
    {
        $input = '<title>Contact user@example.com</title>';

        $result = $this->filters()->filter_content( $input, 'with_javascript' );

        $this->assertStringNotContainsString( '<script', $result );
    }

    // =========================================================
    // Edge cases
    // =========================================================

    public function test_option_with_attributes_still_handled(): void
    {
        // Attribute values (value="...") are handled by filter_soft_dom_attributes in filter_page,
        // not by filter_content. Scope this test to the inner text that our fix targets.
        $input = '<select><option selected>alice@example.com</option></select>';

        $result = $this->filters()->filter_content( $input, 'with_javascript' );

        $this->assertStringNotContainsString( '<script', $result );
        $this->assertStringNotContainsString( 'alice@example.com', $result );
        $this->assertStringContainsString( 'selected', $result );
    }

    public function test_multiple_options_all_handled(): void
    {
        $input = '<select>'
               . '<option>alice@example.com</option>'
               . '<option>bob@example.com</option>'
               . '<option>carol@example.com</option>'
               . '</select>';

        $result = $this->filters()->filter_content( $input, 'with_javascript' );

        $this->assertStringNotContainsString( '<script', $result );
        $this->assertStringNotContainsString( 'alice@example.com', $result );
        $this->assertStringNotContainsString( 'bob@example.com', $result );
        $this->assertStringNotContainsString( 'carol@example.com', $result );
    }

    public function test_option_without_email_is_unchanged(): void
    {
        $input = '<select><option>No email here</option></select>';

        $result = $this->filters()->filter_content( $input, 'with_javascript' );

        $this->assertStringContainsString( 'No email here', $result );
    }
}
