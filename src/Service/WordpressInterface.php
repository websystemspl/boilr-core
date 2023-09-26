<?php

namespace Websystems\BoilrCore\Service;

interface WordpressInterface
{
    public function getOption($option, $defaultValue = false);
    public function updateOption($option, $value, $autoload = null);
}