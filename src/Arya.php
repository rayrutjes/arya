<?php

namespace RayRutjes\Arya;

use Symfony\Component\Yaml\Yaml;

final class Arya
{
    /**
     * @var string
     */
    private $sourceDirectory;

    /**
     * @var string
     */
    private $destinationDirectory;

    /**
     * @var Yaml
     */
    private $yamlParser;

    /**
     * @var array
     */
    private $plugins = array();

    /**
     * @param string $sourceDirectory
     */
    public function __construct(string $sourceDirectory)
    {
        $this->setSourceDirectory($sourceDirectory);

        $destinationDirectory = $sourceDirectory.'../build';
        $this->setDestinationDirectory($destinationDirectory);
    }

    /**
     * @param string $directory
     *
     * @return $this
     */
    public function setSourceDirectory(string $directory)
    {
        if (!is_dir($directory)) {
            throw new \InvalidArgumentException(sprintf('Source directory "%s" does not exist.', $directory));
        }
        $this->sourceDirectory = rtrim($directory, '/').'/';

        return $this;
    }

    /**
     * @return string
     */
    public function getSourceDirectory()
    {
        return $this->sourceDirectory;
    }

    /**
     * @param string $directory
     *
     * @return $this
     */
    public function setDestinationDirectory(string $directory)
    {
        $this->destinationDirectory = rtrim($directory, '/').'/';

        return $this;
    }

    /**
     * @return string
     */
    public function getDestinationDirectory()
    {
        return $this->destinationDirectory;
    }

    public function read()
    {
        $sourceDirectory = $this->sourceDirectory;
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($sourceDirectory));
        $sourceDirectoryLength = strlen($this->sourceDirectory);
        $files = [];
        foreach ($iterator as $name => $fileInfo) {
            /** @var \SplFileInfo $fileInfo */
            if ($fileInfo->isDir()) {
                continue;
            }

            $filename = substr($fileInfo->getRealPath(), $sourceDirectoryLength);
            $files[$filename] = $this->readFile($filename);
        }

        return $files;
    }

    /**
     * @param string $filename
     *
     * @return array
     */
    public function readFile(string $filename)
    {
        if (!$this->isAbsolutePath($filename)) {
            $filename = $this->sourceDirectory.'/'.$filename;
        }

        if (!file_exists($filename)) {
            throw new \InvalidArgumentException(sprintf('File "%s" does not exist.', $filename));
        }

        if (!is_readable($filename)) {
            throw new \InvalidArgumentException(sprintf('File "%s" is not readable.', $filename));
        }

        $content = file_get_contents($filename);

        $file = [];
        $regex = '~^('
            .implode('|', array_map('preg_quote', array('---')))  # $matches[1] start separator
."){1}[\r\n|\n]*(.*?)[\r\n|\n]+("                                 # $matches[2] between separators
.implode('|', array_map('preg_quote', array('---')))              # $matches[3] end separator
."){1}[\r\n|\n]*(.*)$~s";                                         # $matches[4] document content

        if (preg_match($regex, $content, $matches) === 1) { // There is a Front matter
            $file = trim($matches[2]) !== '' ? (array) $this->getYamlParser()->parse(trim($matches[2])) : [];

            if (isset($file['content'])) {
                throw new \LogicException('The "content" key can not be part of the front-matter.');
            }

            $content = trim($matches[4]);
        }

        $file['content'] = $content;

        return $file;
    }

    /**
     * @param Yaml $parser
     *
     * @return $this
     */
    public function setYamlParser(Yaml $parser)
    {
        $this->yamlParser = $parser;

        return $this;
    }

    /**
     * @return Yaml
     */
    public function getYamlParser()
    {
        return $this->yamlParser ?? new Yaml();
    }

    /**
     * @param Plugin $plugin
     *
     * @return $this
     */
    public function use (Plugin $plugin)
    {
        $this->plugins[] = $plugin;

        return $this;
    }

    /**
     * @param array $files
     * @param array $plugins
     *
     * @return array
     */
    public function run(array $files, array $plugins)
    {
        foreach ($plugins as $plugin) {
            $files = $plugin($files, $this);
        }

        return $files;
    }

    /**
     * @param array  $files
     * @param string $directory
     */
    public function write(array $files, string $directory)
    {
        if (!is_dir($directory)) {
            $this->createDirectory($directory);
        }

        foreach ($files as $filename => $data) {
            $path = $this->toAbsolutePath($directory, $filename);
            $this->writeFile($path, $data);
        }
    }

    /**
     * @param string $filename
     * @param array  $data
     */
    public function writeFile(string $filename, array $data)
    {
        if (!$this->isAbsolutePath($filename)) {
            throw new \InvalidArgumentException(sprintf('Filename "%s" is not an absolute path.', $filename));
        }

        $fileDirectory = dirname($filename);
        if (!file_exists($fileDirectory)) {
            $this->createDirectory($fileDirectory);
        }

        file_put_contents($filename, $data['content']);
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    private function isAbsolutePath(string $path)
    {
        return strpos($path, '/') === 0;
    }

    /**
     * @param string $directory
     * @param string $filename
     *
     * @return string
     */
    private function toAbsolutePath(string $directory, string $filename)
    {
        if ($this->isAbsolutePath($filename)) {
            throw new \InvalidArgumentException(sprintf('Filename "%s" should be a relative path.', $filename));
        }

        return $directory.$filename;
    }

    /**
     * @param $path
     */
    private function createDirectory($path)
    {
        $success = mkdir($path, 0777, true);

        if (false === $success) {
            throw new \RuntimeException(sprintf('Unable to create directory "%s"', $path));
        }
    }

    /**
     * @return array
     */
    public function build()
    {
        $files = $this->read();
        $files = $this->run($files, $this->plugins);
        $this->write($files, $this->destinationDirectory);

        return $files;
    }
}
