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
}
