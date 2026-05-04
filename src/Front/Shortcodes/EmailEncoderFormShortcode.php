<?php

namespace OnlineOptimisation\EmailEncoderBundle\Front\Shortcodes;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class EmailEncoderFormShortcode
{
    use PluginHelper;

    protected string $tag = 'eeb_form';

    public function tag(): string
    {
        return $this->tag;
    }


    /**
     * @param array< string, string > $atts
     * @param string $content
     * @return string
     */
    public function handle( array $atts = [], ?string $content = null ): string
    {
        return $this->getEncoderForm();
    }

}
