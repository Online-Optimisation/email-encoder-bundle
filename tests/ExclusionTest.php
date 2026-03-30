<?php

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;
use OnlineOptimisation\EmailEncoderBundle\Validate\EncoderForm;

final class ExclusionTest extends TestCase
{
    private EncoderForm $form;

    protected function setUp(): void
    {
        $this->form = new EncoderForm();
    }

    // =========================================================
    // is_post_excluded() — post exclusion by ID
    // =========================================================

    public function test_post_not_excluded_by_default(): void
    {
        // With no skip_posts setting configured, no post should be excluded
        $result = $this->form->is_post_excluded( 123 );

        $this->assertFalse( $result );
    }

    public function test_post_excluded_returns_bool(): void
    {
        $result = $this->form->is_post_excluded( 999 );

        $this->assertIsBool( $result );
    }

    public function test_post_excluded_with_null_id(): void
    {
        // Null post ID should not cause an error
        $result = $this->form->is_post_excluded( null );

        $this->assertIsBool( $result );
    }

    // =========================================================
    // is_query_parameter_excluded() — URL parameter exclusion
    // =========================================================

    public function test_query_param_not_excluded_with_empty_params(): void
    {
        // Empty parameters array should not trigger exclusion
        $result = $this->form->is_query_parameter_excluded( [] );

        $this->assertFalse( $result );
    }

    public function test_query_param_not_excluded_by_default(): void
    {
        // With no skip_query_parameters setting, nothing should be excluded
        $result = $this->form->is_query_parameter_excluded( [ 'page' => '1' ] );

        $this->assertFalse( $result );
    }

    public function test_query_param_returns_bool(): void
    {
        $result = $this->form->is_query_parameter_excluded( [ 'test' => 'value' ] );

        $this->assertIsBool( $result );
    }
}
