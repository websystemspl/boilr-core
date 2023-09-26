<?php

namespace Websystems\BoilrCore\Service;

class Wordrpress implements WordpressInterface
{
    public function getOption($option, $defaultValue = false)
    {
        return get_option($option, $defaultValue);
    }

    public function updateOption($option, $value, $autoload = null)
    {
        return update_option($option, $value, $autoload);
    }
}