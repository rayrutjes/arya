<?php

namespace RayRutjes\Arya;

interface Plugin
{
    /**
     * @param array $files
     * @param Arya  $arya
     *
     * @return
     */
    public function __invoke(array &$files, Arya $arya);
}
