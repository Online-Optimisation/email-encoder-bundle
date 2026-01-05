<?php

namespace OnlineOptimisation\EmailEncoderBundle\Validate;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class Validate
{
    use PluginHelper;

    public Encoding $encoding;
    public Filters $filters;
    public EncoderForm $form;


    public function boot(): void
    {
        $this->encoding = new Encoding();
        $this->filters  = new Filters();
        $this->form     = new EncoderForm();

        $this->encoding->boot();
        $this->filters->boot();
        $this->form->boot();
    }

}
