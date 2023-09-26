<?php

namespace Websystems\BoilrCore\Service;

use Websystems\BoilrCore\Interfaces\TemplateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PhpTemplate implements TemplateInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function render(string $view, array $parameters = [])
    {
        extract($parameters);

        ob_start();
        include($this->container->getParameter('app_path') . '/templates' . '/' . $view);
        $result = ob_get_contents();
        ob_end_clean();

        echo $result;
    }
}