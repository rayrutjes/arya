<?php

namespace RayRutjes\Arya\Test;

use RayRutjes\Arya\Arya;

class AryaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testShouldNotAcceptUnvalidSourceDirectory()
    {
        new Arya('non-existing-directory');
    }

    public function testCanBeInitialized()
    {
        $dir = dirname(__FILE__).'/stubs/test1';
        $arya = new Arya($dir);

        $this->assertEquals($dir, $arya->getSourceDirectory());
    }
}
