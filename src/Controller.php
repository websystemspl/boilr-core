<?php

namespace Websystems\BoilrCore;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;

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

    protected function render(string $view, array $parameters = [])
    {
        $parameters['session'] = $this->container->get('request')->getSession();
        $templateService = $this->container->get('Websystems\BoilrCore\Interfaces\TemplateInterface');
        $templateService->render($view, $parameters);
    }

    protected function addFlash(string $type, string $message): void
    {
        
        try {
            $session = $this->container->get('request')->getSession();
        } catch (SessionNotFoundException $e) {
            throw new \LogicException('You cannot use the addFlash method if sessions are disabled.".', 0, $e);
        }

        $session->getFlashBag()->add($type, $message);
    }    
}