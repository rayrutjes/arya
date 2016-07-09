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

        $destinationDirectory = rtrim($sourceDirectory, '/').'/../dist';
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
        $this->sourceDirectory = $directory;

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
     * @param string $destinationDirectory
     *
     * @return $this
     */
    public function setDestinationDirectory(string $destinationDirectory)
    {
        $this->destinationDirectory = $destinationDirectory;

        return $this;
    }

    /**
     * @return string
     */
    public function getDestinationDirectory()
    {
        return $this->destinationDirectory;
    }

    public function readFile(string $filename)
    {
        $filename = $this->sourceDirectory.'/'.$filename;
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
            $file = trim($matches[2]) !== '' ? $this->getYamlParser()->parse(trim($matches[2])) : [];

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
}
