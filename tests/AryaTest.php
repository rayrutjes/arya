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

    public function testProvidesDefaultDestinationDirectory()
    {
        $stubsDir = dirname(__FILE__).'/stubs';
        $srcDir = $stubsDir.'/test1';
        $destinationDir = $srcDir.'/../dist';

        $arya = new Arya($srcDir);

        $this->assertEquals($destinationDir, $arya->getDestinationDirectory());
    }

    public function testDestinationDirectoryCanBeChanged()
    {
        $stubsDir = dirname(__FILE__).'/stubs';
        $srcDir = $stubsDir.'/test1';
        $destinationDir = $stubsDir.'/custom-destination';

        $arya = new Arya($srcDir);
        $arya = $arya->setDestinationDirectory($destinationDir);

        $this->assertInstanceOf(Arya::class, $arya);
        $this->assertEquals($destinationDir, $arya->getDestinationDirectory());
    }

    public function testCanReadAFile()
    {
        $stubsDir = dirname(__FILE__).'/stubs';
        $srcDir = $stubsDir.'/test1';

        $arya = new Arya($srcDir);
        $data = $arya->readFile('index.md');

        $expected = [
            'title'   => 'Homepage',
            'content' => 'Home page content.',
        ];

        $this->assertEquals($expected, $data);
    }

    /**
     * @expectedException \LogicException
     */
    public function testShouldDetectInvalidFrontMatter()
    {
        $stubsDir = dirname(__FILE__).'/stubs';
        $srcDir = $stubsDir.'/test1';

        $arya = new Arya($srcDir);
        $arya->readFile('invalid-front-matter.md');
    }
}
