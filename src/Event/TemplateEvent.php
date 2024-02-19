<?php

namespace Websystems\BoilrCore\Event;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Websystems\BoilrCore\Bootstrap;
use Symfony\Contracts\EventDispatcher\Event;

class TemplateEvent extends Event
{
    private ContainerInterface $container;
    private $data;

    public function __construct(ContainerInterface $container, $data = null)
    {
        $this->container = $container;
        $this->data = $data;
    }

    /**
     * Get the value of container
     */ 
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set the value of container
     *
     * @return  self
     */ 
    public function setContainer($container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get the value of data
     */ 
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set the value of data
     *
     * @return  self
     */ 
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}