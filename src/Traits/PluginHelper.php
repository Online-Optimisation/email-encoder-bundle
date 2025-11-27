<?php

namespace OnlineOptimisation\EmailEncoderBundle\Traits;

use Legacy\EmailEncoderBundle\Email_Encoder;
use Legacy\EmailEncoderBundle\Email_Encoder_Helpers;
use Legacy\EmailEncoderBundle\Email_Encoder_Settings;
use Legacy\EmailEncoderBundle\Email_Encoder_Validate;

use OnlineOptimisation\EmailEncoderBundle\Admin\Admin;
use OnlineOptimisation\EmailEncoderBundle\Front\Front;
use OnlineOptimisation\EmailEncoderBundle\Validate\Validate;

trait PluginHelper
{
    # MAJORS =================================================================

    public function plugin(): Email_Encoder
    {
        return Email_Encoder::instance();
    }

    // public function validate(): Email_Encoder_Validate
    public function validate(): Validate
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

    public function context(): Admin|Front
    {
        return $this->plugin()->context;
    }

    # SETTINGS ===============================================================

    public function getSetting( string $slug = '', bool $single = false, string $group = '' ): mixed
    {
        return $this->plugin()->settings->get_setting( $slug, $single, $group );
    }

    public function getSettingBool( string $slug = '', bool $single = false, string $group = '' ): bool {
        return filter_var( $this->getSetting( $slug, $single, $group ), FILTER_VALIDATE_BOOLEAN );
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
        return $this->validate()->form->is_query_parameter_excluded();
    }

    public function isPostExcluded(): bool
    {
        return $this->validate()->form->is_post_excluded();
    }


    public function filterContent( string $content, string $protect_using ): string
    {
        return $this->validate()->filters->filter_content( $content, $protect_using );
    }

    public function filterPage( string $content, string $protect_using ): string
    {
        return $this->validate()->filters->filter_page( $content, $protect_using );
    }

    public function filterPlainEmails( ...$args ): string
    {
        return $this->validate()->filters->filter_plain_emails( ...$args );
    }


    public function dynamicJsEmailEncoding( ...$args ): string
    {
        return $this->validate()->encoding->dynamic_js_email_encoding( ...$args );
    }

    public function tempEncodeAtSymbol( ...$args ): string
    {
        return $this->validate()->encoding->temp_encode_at_symbol( ...$args );
    }

    public function encodeAscii( ...$args ): string
    {
        return $this->validate()->encoding->encode_ascii( ...$args );
    }

    public function encodeEscape( ...$args ): string
    {
        return $this->validate()->encoding->encode_escape( ...$args );
    }

    public function encodeEmailCss( ...$args ): string
    {
        return $this->validate()->encoding->encode_email_css( ...$args );
    }

    public function createProtectedMailto( ...$args ): string
    {
        return $this->validate()->encoding->create_protected_mailto( ...$args );
    }

    public function createProtectedHrefAtt( ...$args ): string
    {
        return $this->validate()->encoding->create_protected_href_att( ...$args );
    }

    public function getEncodedEmailIcon( ...$args ): string
    {
        return $this->validate()->encoding->get_encoded_email_icon( ...$args );
    }

    public function generateEmailImageUrl( ...$args ): string
    {
        return $this->validate()->encoding->generate_email_image_url( ...$args );
    }


    public function getEncoderForm( ...$args ): string
    {
        return $this->validate()->form->get_encoder_form( ...$args );
    }


    # LOG ====================================================================

    public function log( mixed $data ): void
    {
        error_log( print_r( $data, true ) );
    }

    # USEFUL =================================================================

    private function assetJs( string $filename ): string {
        return EEB_PLUGIN_URL . 'assets/js/' . $filename;
    }

    private function assetCss( string $filename ): string {
        return EEB_PLUGIN_URL . 'assets/css/' . $filename;
    }

}