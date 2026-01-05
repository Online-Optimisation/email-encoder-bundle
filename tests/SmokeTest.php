<?php

declare ( strict_types = 1 );

use PHPUnit\Framework\TestCase;

final class SmokeTest extends TestCase {

    public function test_wordpress_load () : void 
    {
        $this->assertTrue ( function_exists ( 'add_action' ) );
        $this->assertTrue ( defined ( 'ABSPATH' ) );
    }

    public function test_plugin_entrypoint() : void 
    {
        $this->assertTrue (
            class_exists ( \OnlineOptimisation\EmailEncoderBundle\Tester::class )
        );
    }

}

