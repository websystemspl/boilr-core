<?php

namespace Websystems\BoilrCore\Resources;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Action
{
    private $container;
    private $name;
    private $hook;
    private $controller;
    private $action;
    private $priority;
    private $params;

    public function __construct(ContainerInterface $container, string $name, string $hook, string $controller, string $action, int $priority, int $params)
    {
        $this->container = $container;
        $this->name = $name;
        $this->hook = $hook;
        $this->controller = $controller;
        $this->action = $action;
        $this->priority = $priority;
        $this->params = $params;

        return $this;
    }

    public function publish()
    {
        $object = $this->container->get($this->controller);
        add_action($this->hook, [$object, $this->action], $this->priority, $this->params);

        return $this;
    }    

    /**
     * Get the value of name
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the value of hook
     */ 
    public function getHook()
    {
        return $this->hook;
    }

    /**
     * Get the value of controller
     */ 
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Get the value of action
     */ 
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Get the value of priority
     */ 
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Get the value of params
     */ 
    public function getParams()
    {
        return $this->params;
    }
}