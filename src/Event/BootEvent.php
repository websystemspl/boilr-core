<?php

namespace Websystems\BoilrCore\Event;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Websystems\BoilrCore\Bootstrap;
use Symfony\Contracts\EventDispatcher\Event;

class BootEvent extends Event
{
    public const BEFORE = 'boot.event.before';
    public const AFTER = 'boot.event.after';

    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
}