<?php

namespace RayRutjes\Arya;

interface Plugin
{
    /**
     * @param array $files
     * @param Arya  $arya
     *
     * @return array
     */
    public function __invoke(array $files, Arya $arya);
}
