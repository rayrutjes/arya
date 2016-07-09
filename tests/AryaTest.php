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
        $stubsDir = dirname(__FILE__).'/stubs';
        $srcDir = $stubsDir.'/test1';

        $arya = new Arya($srcDir);

        $this->assertEquals($srcDir, $arya->getSourceDirectory());
    }

    public function testSourceDirectoryCanBeChanged()
    {
        $stubsDir = dirname(__FILE__).'/stubs';
        $srcDir = $stubsDir.'/test1';
        $newSrcDir = $stubsDir.'/test2';

        $arya = new Arya($srcDir);
        $arya = $arya->setSourceDirectory($newSrcDir);

        $this->assertInstanceOf(Arya::class, $arya);
        $this->assertEquals($newSrcDir, $arya->getSourceDirectory());
    }

    public function testProvidesDefaultBuildDirectory()
    {
        $stubsDir = dirname(__FILE__).'/stubs';
        $srcDir = $stubsDir.'/test1';
        $buildDir = $srcDir.'/../build';

        $arya = new Arya($srcDir);

        $this->assertEquals($buildDir, $arya->getBuildDirectory());
    }

    public function testBuildDirectoryCanBeChanged()
    {
        $stubsDir = dirname(__FILE__).'/stubs';
        $srcDir = $stubsDir.'/test1';
        $buildDir = $stubsDir.'/custom-build';

        $arya = new Arya($srcDir);
        $arya = $arya->setBuildDirectory($buildDir);

        $this->assertInstanceOf(Arya::class, $arya);
        $this->assertEquals($buildDir, $arya->getBuildDirectory());
    }
}
