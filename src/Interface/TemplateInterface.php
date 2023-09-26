<?php

namespace Websystems\BoilrCore\Interface;

interface TemplateInterface
{
    public function render(string $view, array $parameters = []);
}