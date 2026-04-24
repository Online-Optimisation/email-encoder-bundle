<?php

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;

/**
 * Regression tests for the Divi mailto display-clobber bug
 * (Asana 1213905935240604, WP.org thread /topic/divi-forms-2/).
 *
 * When a <a href="mailto:…"> anchor's inner HTML contained an email pattern,
 * create_protected_mailto() treated the entire inner HTML as "the display"
 * and handed it to get_protected_display(), which wiped it and left behind
 * only a scramble span + script. This destroyed Divi 5's Link-block inner
 * span (<span class="et_pb_link_inner">) and Divi 4 text modules wrapping
 * richer content inside the anchor.
 *
 * The fix: only scramble display when it IS the email itself; otherwise
 * leave the markup intact and rely on the final filterPlainEmails pass to
 * entity-encode any emails still embedded in it.
 */
final class Divi5ReproTest extends TestCase
{
    private function filters()
    {
        return EEB()->validate->filters;
    }

    // --- Divi 4 text module: mailto anchor wrapping a span that contains the email ---

    public function test_divi4_text_module_span_survives(): void
    {
        $input = '<a href="mailto:contact@example.com"><span class="big">Contact us today at contact@example.com for a FREE quote!</span></a>';

        $out = $this->filters()->filter_content( $input, 'with_javascript' );

        $this->assertStringContainsString( '<span class="big">', $out, 'the user-authored <span> must survive' );
        $this->assertStringContainsString( 'Contact us today at', $out, 'the copy before the email must survive' );
        $this->assertStringContainsString( 'for a FREE quote!', $out, 'the copy after the email must survive' );
        $this->assertStringNotContainsString( 'contact@example.com', $out, 'the embedded email must not leak in plaintext' );
    }

    public function test_divi4_text_module_block_children_survive(): void
    {
        $input = '<a href="mailto:contact@example.com"><h3>Get in touch</h3><p>Email contact@example.com for a quote.</p></a>';

        $out = $this->filters()->filter_content( $input, 'with_javascript' );

        $this->assertStringContainsString( '<h3>Get in touch</h3>', $out, 'the <h3> must survive' );
        $this->assertStringContainsString( 'Email ', $out, 'the paragraph copy must survive' );
        $this->assertStringContainsString( ' for a quote.', $out, 'the trailing copy must survive' );
        $this->assertStringNotContainsString( 'contact@example.com', $out, 'the embedded email must not leak in plaintext' );
    }

    // --- Divi 5 Link block: inner text span with email in link text ---

    public function test_divi5_link_block_inner_span_survives(): void
    {
        $input = '<a class="et_pb_link_0 et_pb_link et_pb_module" href="mailto:contact@example.com"><span class="et_pb_link_inner">Email us at contact@example.com today!</span></a>';

        $out = $this->filters()->filter_content( $input, 'with_javascript' );

        $this->assertStringContainsString( '<span class="et_pb_link_inner">', $out, 'Divi 5 inner span must survive so its CSS still targets something' );
        $this->assertStringContainsString( 'Email us at', $out, 'link text before the email must survive' );
        $this->assertStringContainsString( ' today!', $out, 'link text after the email must survive' );
        $this->assertStringNotContainsString( 'contact@example.com', $out, 'the embedded email must not leak in plaintext' );
    }

    // --- Backward compat: classic <a href="mailto:x">x</a> must still scramble ---

    public function test_classic_mailto_anchor_still_scrambles(): void
    {
        $input = '<p>Email: <a href="mailto:contact@example.com">contact@example.com</a></p>';

        $out = $this->filters()->filter_content( $input, 'with_javascript' );

        $this->assertStringContainsString( 'data-enc-email', $out, 'classic anchor must still produce a JS-decoded anchor' );
        $this->assertStringNotContainsString( 'mailto:contact@example.com', $out, 'raw mailto href must be replaced' );
        $this->assertStringNotContainsString( '>contact@example.com</a>', $out, 'plain email must no longer appear as display text' );
    }

    public function test_classic_mailto_anchor_without_javascript_still_encodes_href(): void
    {
        $input = '<p>Email: <a href="mailto:contact@example.com">contact@example.com</a></p>';

        $out = $this->filters()->filter_content( $input, 'without_javascript' );

        $this->assertStringNotContainsString( 'mailto:contact@example.com', $out, 'plain mailto href must be entity-encoded' );
        $this->assertStringNotContainsString( '>contact@example.com</a>', $out, 'plain email text must no longer appear' );
    }
}
