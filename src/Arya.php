<?php

namespace RayRutjes\Arya;

final class Arya
{
    /**
     * @var string
     */
    private $sourceDirectory;

    /**
     * @param string $sourceDirectory
     */
    public function __construct(string $sourceDirectory)
    {
        $this->setSourceDirectory($sourceDirectory);
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
}
