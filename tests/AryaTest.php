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
        $srcDir = $stubsDir.'/test1/';

        $arya = new Arya($srcDir);

        $this->assertEquals($srcDir, $arya->getSourceDirectory());
    }

    public function testSourceDirectoryCanBeChanged()
    {
        $stubsDir = dirname(__FILE__).'/stubs';
        $srcDir = $stubsDir.'/test1/';
        $newSrcDir = $stubsDir.'/test2/';

        $arya = new Arya($srcDir);
        $arya = $arya->setSourceDirectory($newSrcDir);

        $this->assertInstanceOf(Arya::class, $arya);
        $this->assertEquals($newSrcDir, $arya->getSourceDirectory());
    }

    public function testShouldNormalizeSourceDirectory()
    {
        $stubsDir = dirname(__FILE__).'/stubs';
        $srcDir = $stubsDir.'/test1';

        $arya = new Arya($srcDir);

        $expected = $stubsDir.'/test1/';
        $this->assertEquals($expected, $arya->getSourceDirectory());

        $expected = $stubsDir.'/test1/../build/';
        $this->assertEquals($expected, $arya->getDestinationDirectory());
    }

    public function testProvidesDefaultDestinationDirectory()
    {
        $stubsDir = dirname(__FILE__).'/stubs';
        $srcDir = $stubsDir.'/test1/';
        $destinationDir = $srcDir.'../build/';

        $arya = new Arya($srcDir);

        $this->assertEquals($destinationDir, $arya->getDestinationDirectory());
    }

    public function testDestinationDirectoryCanBeChanged()
    {
        $stubsDir = dirname(__FILE__).'/stubs';
        $srcDir = $stubsDir.'/test1/';
        $destinationDir = $stubsDir.'/custom-destination/';

        $arya = new Arya($srcDir);
        $arya = $arya->setDestinationDirectory($destinationDir);

        $this->assertInstanceOf(Arya::class, $arya);
        $this->assertEquals($destinationDir, $arya->getDestinationDirectory());
    }

    public function testCanReadAFile()
    {
        $stubsDir = dirname(__FILE__).'/stubs';
        $srcDir = $stubsDir.'/test1/';

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
        $srcDir = $stubsDir.'/test3';

        $arya = new Arya($srcDir);
        $arya->readFile('invalid-front-matter.md');
    }

    public function testCanReadAllSourceFiles()
    {
        $stubsDir = dirname(__FILE__).'/stubs';
        $srcDir = $stubsDir.'/test1/';

        $arya = new Arya($srcDir);
        $files = $arya->read();

        $expected = [
            'index.md' => [
                'title'   => 'Homepage',
                'content' => 'Home page content.',
            ],
            'subdir1/index.md' => [
                'title'   => 'Homepage',
                'content' => 'Home page content.',
            ],
            'subdir1/subsubdir1/index.md' => [
                'title'   => 'Homepage',
                'content' => 'Home page content.',
            ],
            'subdir2/index.md' => [
                'title'   => 'Homepage',
                'content' => 'Home page content.',
            ],
        ];

        $this->assertEquals($expected, $files);
    }

    public function testCanBuild()
    {
        $stubsDir = dirname(__FILE__).'/stubs';
        $srcDir = $stubsDir.'/build-test/';

        $arya = new Arya($srcDir);
        $files = $arya->build();

        $expected = [
            'index.md' => [
                'title'   => 'Homepage',
                'content' => 'Homepage content.',
            ],
            'blog/article.md' => [
                'title'   => 'Article',
                'content' => 'Article content.',
            ],
        ];

        $this->assertEquals($expected, $files);

        $filename = $stubsDir.'/build/index.md';
        $fileContent = 'Homepage content.';
        $this->assertFileExists($filename);
        $this->assertEquals($fileContent, file_get_contents($filename));

        $filename = $stubsDir.'/build/blog/article.md';
        $fileContent = 'Article content.';
        $this->assertFileExists($filename);
        $this->assertEquals($fileContent, file_get_contents($filename));
    }

    public function testPluginExecution()
    {
        $stubsDir = dirname(__FILE__).'/stubs';
        $srcDir = $stubsDir.'/plugin-test/';

        $plugin1 = new ArbitraryFilePlugin('test1.md', ['title' => 'Test1', 'content' => 'Test1 content.']);
        $plugin2 = new ArbitraryFilePlugin('blog/test2.md', ['title' => 'Test2', 'content' => 'Test2 content.']);

        $arya = new Arya($srcDir);
        $arya = $arya->use($plugin1);
        $this->assertInstanceOf(Arya::class, $arya);

        $arya = $arya->use($plugin2);

        $files = $arya->build();
        $expected = [
            'index.md' => [
                'title'   => 'Homepage',
                'content' => 'Homepage content.',
            ],
            'test1.md' => [
                'title'   => 'Test1',
                'content' => 'Test1 content.',
            ],
            'blog/test2.md' => [
                'title'   => 'Test2',
                'content' => 'Test2 content.',
            ],
        ];

        $this->assertEquals($expected, $files);
    }

    public function testCanCleanBuildDirectory()
    {
        $stubsDir = dirname(__FILE__).'/stubs';
        $srcDir = $stubsDir.'/clean-test/';
        $destDir = $stubsDir.'/clean-build/';

        $dirtyFilename = $destDir.'dirty.html';
        $newFilename = $destDir.'index.md';

        @mkdir($destDir, 0777, true);
        touch($dirtyFilename);

        $arya = new Arya($srcDir);
        $arya->setDestinationDirectory($destDir);
        $arya = $arya->setClean(true);

        $this->assertInstanceOf(Arya::class, $arya);

        $arya->build();

        $this->assertFileNotExists($dirtyFilename);
        $this->assertFileExists($newFilename);
    }
}
