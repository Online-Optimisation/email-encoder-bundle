<?php

namespace OnlineOptimisation\EmailEncoderBundle\Front\Shortcodes;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class ContentShortcode
{
    use PluginHelper;

    protected string $tag = 'eeb_content';
    protected string $newTag = 'eeb_protect_content';


    public function tag(): string
    {
        return $this->tag;
    }


    public function handle( array $atts = [], ?string $content = null ): string
    {
        _doing_it_wrong(
            __METHOD__,
            sprintf( 'The [%s] shortcode is deprecated. Use [%s] instead.', $this->tag, $this->newTag ),
            '2.3.0'
        );

        return ( new ProtectContentShortcode() )->handle( $atts, $content );
    }

}
