<?php

namespace OnlineOptimisation\EmailEncoderBundle\Traits;

use Legacy\EmailEncoderBundle\Email_Encoder;
use Legacy\EmailEncoderBundle\Email_Encoder_Helpers;
use Legacy\EmailEncoderBundle\Email_Encoder_Settings;
use Legacy\EmailEncoderBundle\Email_Encoder_Validate;

trait PluginHelper
{
    # MAJORS =================================================================

    public function plugin(): Email_Encoder
    {
        return Email_Encoder::instance();
    }

    public function validate(): Email_Encoder_Validate
    {
        return $this->plugin()->validate;
    }

    public function helper(): Email_Encoder_Helpers
    {
        return $this->plugin()->helpers;
    }

    public function settings(): Email_Encoder_Settings
    {
        return $this->plugin()->settings;
    }

    # SETTINGS ===============================================================

    public function getSetting( string $slug = '', bool $single = false, string $group = '' ): mixed
    {
        return $this->plugin()->settings->get_setting( $slug, $single, $group );
    }

    public function getPageName(): string
    {
        return $this->plugin()->settings->get_page_name();
    }

    public function getPageTitle(): string
    {
        return $this->plugin()->settings->get_page_title();
    }

    public function getSettingsKey(): string
    {
        return $this->plugin()->settings->get_settings_key();
    }

    public function getFinalOutputBufferHook(): string
    {
        return $this->plugin()->settings->get_final_output_buffer_hook();
    }

    public function getWidgetCallbackHook(): string
    {
        return $this->plugin()->settings->get_widget_callback_hook();
    }

    public function getTemplateTags(): array
    {
        return $this->plugin()->settings->get_template_tags();
    }

    public function getSafeHtmlAttr(): array
    {
        return $this->plugin()->settings->get_safe_html_attr();
    }

    public function getAdminCap( string $target = 'main' ): string
    {
        return $this->plugin()->settings->get_admin_cap( $target );
    }

    public function getHookPriorities( string $method ): string
    {
        return $this->plugin()->settings->get_hook_priorities( $method );
    }

    public function reloadSettings(): ?array
    {
        return $this->plugin()->settings->reload_settings();
    }

    # VALIDATE ===============================================================

    public function isQueryParameterExcluded(): bool
    {
        return $this->plugin()->validate->is_query_parameter_excluded();
    }

    public function isPostExcluded(): bool
    {
        return $this->plugin()->validate->is_post_excluded();
    }

    public function filterContent( string $content, string $protect_using ): string
    {
        return $this->plugin()->validate->filter_content( $content, $protect_using );
    }

    public function filterPage( string $content, string $protect_using ): string
    {
        return $this->plugin()->validate->filter_page( $content, $protect_using );
    }

    # LOG ====================================================================

    public function log( mixed $data ): void
    {
        error_log( print_r( $data, true ) );
    }

}