<?php

namespace Websystems\BoilrCore\Resources;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Ajax
{
    private $container;
    private $name;
    private $controller;
    private $action;
    private $noPriv;

    public function __construct(ContainerInterface $container, string $name, string $controller, string $action, bool $noPriv = true)
    {
        $this->container = $container;
        $this->name = $name;
        $this->controller = $controller;
        $this->action = $action;
        $this->noPriv = $noPriv;

        return $this;
    }

    public function publish()
    {
        $object = $this->container->get($this->controller);
        if(true === $this->noPriv) {
            add_action('wp_ajax_nopriv_'.$this->name, [$object, $this->action]);
        }
        add_action('wp_ajax_'.$this->name, [$object, $this->action]);

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
     * Get the value of noPriv
     */ 
    public function getNoPriv()
    {
        return $this->noPriv;
    }
}