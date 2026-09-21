<?php

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;

/**
 * Regression test for a mailto link's email dropping onto a second row in Elementor's
 * Icon List widget (Nassim, Asana 1218593609473108 — Astra + Elementor, JS method).
 *
 * Elementor renders <a href="mailto:x"><span class="…-icon"><svg/></span><span class="…-text">x</span></a>
 * and lays the anchor out as a flex row. strip_tags() of that display equals the email, so
 * create_protected_mailto() treated it as "just the email" and ran the WHOLE inner HTML through
 * get_protected_display(): the JS method re-injected icon + text inside one extra <span>, so they
 * stopped being flex children and the email wrapped below the icon; the CSS method stripped the
 * icon entirely. Only the email text may be scrambled — the wrapping elements must stay real DOM.
 */
final class MailtoWrappedDisplayTest extends TestCase
{
    private const ICON = '<span class="elementor-icon-list-icon"><svg aria-hidden="true" viewBox="0 0 512 512"><path d="M0 0h512v512H0z"/></svg></span>';

    private function filter( string $html, string $method ): string
    {
        return EEB()->validate->filters->filter_content( $html, $method );
    }

    private function iconListItem(): string
    {
        return '<a href="mailto:info@example.com">' . self::ICON . '<span class="elementor-icon-list-text">info@example.com</span></a>';
    }

    public function test_js_method_keeps_icon_and_text_wrappers_as_anchor_children(): void
    {
        $out = $this->filter( $this->iconListItem(), 'with_javascript' );

        $this->assertStringContainsString( self::ICON, $out, 'Icon markup must stay in the served HTML, not be moved into the injected script' );
        $this->assertMatchesRegularExpression( '#<span class="elementor-icon-list-text"><span id="eeb-#', $out, 'Only the email text is replaced, inside its own wrapper' );
        $this->assertStringNotContainsString( 'info@example.com', $out, 'The plain email must not be left in the markup' );
    }

    public function test_css_method_keeps_the_icon(): void
    {
        $out = $this->filter( $this->iconListItem(), 'without_javascript' );

        $this->assertStringContainsString( self::ICON, $out );
        $this->assertStringNotContainsString( 'info@example.com', $out );
    }

    public function test_email_inside_an_attribute_of_the_display_is_not_rewritten(): void
    {
        $html = '<a href="mailto:info@example.com"><span data-label="info@example.com">info@example.com</span></a>';

        $out = $this->filter( $html, 'with_javascript' );

        // The scrambler must not be injected into the attribute value (that would break the tag).
        $this->assertDoesNotMatchRegularExpression( '/data-label="[^"]*<span/', $out );
        $this->assertMatchesRegularExpression( '#<span data-label="[^"]*"><span id="eeb-#', $out );
    }
}
