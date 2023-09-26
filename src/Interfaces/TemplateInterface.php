<?php

namespace Websystems\BoilrCore\Interfaces;

interface TemplateInterface
{
    public function render(string $view, array $parameters = []);
}