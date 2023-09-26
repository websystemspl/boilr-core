<?php

namespace Websystems\BoilrCore;

use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class Controller
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function setContainer(ContainerInterface $container): ?ContainerInterface
    {
        $previous = $this->container;
        $this->container = $container;

        return $previous;
    }

    public function render(string $view, array $parameters = [])
    {
        $templateService = $this->container->get('Websystems\BoilrCore\Interfaces\TemplateInterface');
        $templateService->render($view, $parameters);
    }
}