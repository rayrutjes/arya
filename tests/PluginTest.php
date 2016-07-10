<?php

namespace RayRutjes\Arya\Test;

use RayRutjes\Arya\Arya;
use RayRutjes\Arya\Plugin;

class PluginTest implements Plugin
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var array
     */
    private $data;

    /**
     * @param string $filename
     * @param array  $data
     */
    public function __construct(string $filename, array $data)
    {
        $this->filename = $filename;
        $this->data = $data;
    }

    /**
     * @param array $files
     * @param Arya  $arya
     *
     * @return array
     */
    public function __invoke(array $files, Arya $arya)
    {
        $files[$this->filename] = $this->data;

        return $files;
    }
}
