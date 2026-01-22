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

    /**
     * @return Admin|Front
     */
    public function context() //: Admin|Front
    {
        return $this->plugin()->context;
    }

    # SETTINGS ===============================================================

    /**
     * @return mixed
     */
    public function getSetting( string $slug = '', bool $single = false, string $group = '' )
    {
        $value = $this->plugin()->settings->get_setting( $slug, $single, $group );
        if ( is_string( $value ) ) {
            $value = sanitize_text_field( $value );
        }

        return $value;
    }

    public function getSettingBool( string $slug = '', bool $single = false, string $group = '' ): bool
    {
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

    /**
     * @return array< string, string >
     */
    public function getTemplateTags(): array
    {
        return $this->plugin()->settings->get_template_tags();
    }

    /**
     * @return array< string, array< string, array< string > > >
     */
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

    public function reloadSettings(): void
    {
        $this->plugin()->settings->reload_settings();
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

    /**
     * @param mixed $args
     */
    public function filterPlainEmails( ...$args ): string
    {
        return $this->validate()->filters->filter_plain_emails( ...$args );
    }


    /**
     * @param mixed $args
     */
    public function dynamicJsEmailEncoding( ...$args ): string
    {
        return $this->validate()->encoding->dynamic_js_email_encoding( ...$args );
    }

    /**
     * @param mixed $args
     */
    public function tempEncodeAtSymbol( ...$args ): string
    {
        return $this->validate()->encoding->temp_encode_at_symbol( ...$args );
    }

    /**
     * @param mixed $args
     */
    public function encodeAscii( ...$args ): string
    {
        return $this->validate()->encoding->encode_ascii( ...$args );
    }

    /**
     * @param mixed $args
     */
    public function encodeEscape( ...$args ): string
    {
        return $this->validate()->encoding->encode_escape( ...$args );
    }

    /**
     * @param mixed $args
     */
    public function encodeEmailCss( ...$args ): string
    {
        return $this->validate()->encoding->encode_email_css( ...$args );
    }

    /**
     * @param mixed $args
     */
    public function createProtectedMailto( ...$args ): string
    {
        return $this->validate()->encoding->create_protected_mailto( ...$args );
    }

    /**
     * @param mixed $args
     */
    public function createProtectedHrefAtt( ...$args ): string
    {
        return $this->validate()->encoding->create_protected_href_att( ...$args );
    }

    /**
     * @param mixed $args
     */
    public function getEncodedEmailIcon( ...$args ): string
    {
        return $this->validate()->encoding->get_encoded_email_icon( ...$args );
    }

    /**
     * @param mixed $args
     * @return string|bool
     */
    public function generateEmailImageUrl( ...$args )
    {
        return $this->validate()->encoding->generate_email_image_url( ...$args );
    }

    /**
     * @param mixed $args
     */
    public function getEncoderForm( ...$args ): string
    {
        return $this->validate()->form->get_encoder_form( ...$args );
    }


    # LOG ====================================================================

    /**
     * @param mixed $data
     */
    public function log( $data ): void
    {
        error_log( print_r( $data, true ) );
    }

    # USEFUL =================================================================

    private function assetJsDir( string $filename ): string
    {
        return EEB_PLUGIN_DIR . 'assets/js/' . $filename;
    }

    private function assetCssDir( string $filename ): string
    {
        return EEB_PLUGIN_DIR . 'assets/css/' . $filename;
    }

    private function assetJsUrl( string $filename ): string
    {
        return EEB_PLUGIN_URL . 'assets/js/' . $filename;
    }

    private function assetCssUrl( string $filename ): string
    {
        return EEB_PLUGIN_URL . 'assets/css/' . $filename;
    }

    private function isEmptyString( ?string $string ): bool
    {
        return $string !== null && $string !== '';
    }

}
