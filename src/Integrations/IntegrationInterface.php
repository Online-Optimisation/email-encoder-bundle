<?php

namespace OnlineOptimisation\EmailEncoderBundle\Integrations;

interface IntegrationInterface
{
    public function boot(): void;

    // public function is_active(): bool;
}
