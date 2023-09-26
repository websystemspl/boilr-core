<?php

namespace Websystems\BoilrCore\Resources;

class AdminRoute
{
    public $parentSlug;
    public $pageTitle;
    public $menuTitle;
    public $capability;
    public $menuSlug;
    public $controller;
    public $action;
    public $iconUrl = '';
    public $position = null;

    public function __construct(?string $parentSlug = null, string $pageTitle, string $menuTitle, string $capability, string $menuSlug, $controller, string $action, string $iconUrl = '', ?int $position = null)
    {
        $this->parentSlug = $parentSlug;
        $this->pageTitle = $pageTitle;
        $this->menuTitle = $menuTitle;
        $this->capability = $capability;
        $this->menuSlug = $menuSlug;
        $this->controller = $controller;
        $this->action = $action;
        $this->iconUrl = $iconUrl;
        $this->position = $position;

        return $this;
    }

    public function publish()
    {
        if(null === $this->parentSlug) {
            \add_action('admin_menu', function() {
                \add_menu_page(
                    $this->pageTitle,
                    $this->menuTitle,
                    $this->capability,
                    $this->menuSlug,
                    [$this->controller, $this->action],
                    $this->iconUrl,
                    $this->position,
                ); 
            }, 1);
        }

        if(null !== $this->parentSlug) {
            \add_action('admin_menu', function() {
                \add_submenu_page(
                    $this->parentSlug,
                    $this->pageTitle,
                    $this->menuTitle,
                    $this->capability,
                    $this->menuSlug,
                    [$this->controller, $this->action],
                    $this->position,                
                ); 
            }, 1);
        }
    }    
}