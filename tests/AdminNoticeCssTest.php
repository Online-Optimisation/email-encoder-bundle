<?php

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;

/**
 * Regression tests for hidden admin notices becoming visible on the plugin
 * settings page.
 *
 * The admin stylesheet restyles WP admin notices that end up inside the
 * settings wrapper (.eeb-wrap > .notice) as flex cards. That rule used to set
 * display:flex !important on every notice, which overrode the mechanisms other
 * plugins use to keep a notice hidden until needed (the .hidden and
 * .hide-if-js classes, the [hidden] attribute, or an inline display:none).
 * Those notices then showed up on the settings page.
 *
 * The fix: exclude hidden notices from the selector and drop !important from
 * the display declaration so inline display:none still wins.
 */
final class AdminNoticeCssTest extends TestCase
{
    private const CSS_FILE = __DIR__ . '/../assets/css/style-admin.css';

    private const NOTICE_SELECTOR = '.eeb-wrap > .notice';

    /**
     * Returns the selector items and bodies of every rule that targets
     * .eeb-wrap > .notice.
     *
     * @return array<int, array{selectors: string[], body: string}>
     */
    private function notice_rules(): array
    {
        $css = file_get_contents( self::CSS_FILE );
        $this->assertIsString( $css, 'Admin stylesheet could not be read.' );

        $css = (string) preg_replace( '#/\*.*?\*/#s', '', $css );

        preg_match_all( '/([^{}]+)\{([^{}]*)\}/', $css, $matches, PREG_SET_ORDER );

        $rules = [];
        foreach ( $matches as $match ) {
            $items = array_map(
                static function ( string $item ): string {
                    return trim( (string) preg_replace( '/\s+/', ' ', $item ) );
                },
                explode( ',', $match[1] )
            );

            $matching = array_values(
                array_filter(
                    $items,
                    static function ( string $item ): bool {
                        return strpos( $item, self::NOTICE_SELECTOR ) !== false;
                    }
                )
            );

            if ( $matching !== [] ) {
                $rules[] = [
                    'selectors' => $matching,
                    'body'      => $match[2],
                ];
            }
        }

        return $rules;
    }

    public function test_notice_rule_exists(): void
    {
        $this->assertNotEmpty( $this->notice_rules(), 'Expected a rule targeting ' . self::NOTICE_SELECTOR . '.' );
    }

    public function test_notice_selectors_skip_hidden_notices(): void
    {
        foreach ( $this->notice_rules() as $rule ) {
            foreach ( $rule['selectors'] as $selector ) {
                $this->assertStringContainsString( ':not(.hidden)', $selector );
                $this->assertStringContainsString( ':not([hidden])', $selector );
                $this->assertStringContainsString( ':not(.hide-if-js)', $selector );
            }
        }
    }

    public function test_notice_display_is_not_important(): void
    {
        foreach ( $this->notice_rules() as $rule ) {
            $this->assertDoesNotMatchRegularExpression(
                '/display\s*:[^;]*!important/i',
                $rule['body'],
                'display on ' . implode( ', ', $rule['selectors'] ) . ' must not be !important.'
            );
        }
    }
}
