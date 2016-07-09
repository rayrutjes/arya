<?php

namespace RayRutjes\Arya;

final class Arya
{
    /**
     * @var string
     */
    private $sourceDirectory;

    /**
     * @var string
     */
    private $buildDirectory;

    /**
     * @param string $sourceDirectory
     */
    public function __construct(string $sourceDirectory)
    {
        $this->setSourceDirectory($sourceDirectory);

        $buildDirectory = rtrim($sourceDirectory, '/').'/../build';
        $this->setBuildDirectory($buildDirectory);
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
     * @param string $buildDirectory
     *
     * @return $this
     */
    public function setBuildDirectory(string $buildDirectory)
    {
        $this->buildDirectory = $buildDirectory;

        return $this;
    }

    /**
     * @return string
     */
    public function getBuildDirectory()
    {
        return $this->buildDirectory;
    }
}
