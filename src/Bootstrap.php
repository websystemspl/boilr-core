<?php

namespace Websystems\BoilrCore;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Config\FileLocator;
use Websystems\BoilrCore\Event\BootEvent;
use Websystems\BoilrCore\Event\ActivateEvent;
use Websystems\BoilrCore\Loader\AjaxYamlFileLoader;
use Websystems\BoilrCore\Loader\ActionsYamlFileLoader;
use Websystems\BoilrCore\Loader\FiltersYamlFileLoader;
use Websystems\BoilrCore\Loader\RestApiYamlFileLoader;
use Websystems\BoilrCore\Loader\AdminRoutesYamlFileLoader;
use Websystems\BoilrCore\Loader\FrontRoutesYamlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Websystems\BoilrCore\Loader\AdminHandlersYamlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Bootstrap
{
    /** @var String */
    private $appPath;

    /** @var ContainerBuilder */
    private $containerBuilder;

    private $adminRoutes;

    public function __construct(string $filePath)
    {
        $this->appPath = plugin_dir_path($filePath);
        $this->createContainer();
		$this->containerBuilder
			->get('Symfony\Component\EventDispatcher\EventDispatcherInterface')
			->dispatch(new BootEvent($this->containerBuilder), BootEvent::BEFORE)
		; 
        $this->loadAdminRoutes();
        $this->loadActions();
        $this->loadFilters();
        $this->loadAdminHandlers();
        $this->loadRestApiEndpoints();
        $this->loadAjaxHandlers();
        $this->loadFrontRoutes();
        $this->loadAssets();
        register_activation_hook($this->appPath . '/' . basename(plugin_basename($filePath)), [$this, 'onActivate']);
		$this->containerBuilder
			->get('Symfony\Component\EventDispatcher\EventDispatcherInterface')
			->dispatch(new BootEvent($this->containerBuilder), BootEvent::AFTER)
		;        
    }

    public function onActivate()
    {
		$this->containerBuilder
			->get('Symfony\Component\EventDispatcher\EventDispatcherInterface')
			->dispatch(new ActivateEvent($this->containerBuilder))
		;  
    }

    private function createContainer()
    {
        $this->containerBuilder = new ContainerBuilder();
        $this->containerBuilder->setAlias('Symfony\Component\DependencyInjection\ContainerInterface', 'service_container');
        $this->containerBuilder->setParameter('app_path', $this->appPath);   
        
        $session = $this->containerBuilder->register('Symfony\Component\HttpFoundation\Session\Session')
            ->setPublic(true)
        ;
        
        // if ( ! session_id() ) {
        //     $session->addMethodCall('start');
        // }
        
        $loader = new YamlFileLoader($this->containerBuilder, new FileLocator());
        $loader->load(__DIR__ . '/../config/services.yaml');
        $loader->load($this->appPath . '/config/services.yaml');
        $this->containerBuilder->compile();
    }

    private function loadAdminRoutes()
    {
        $loader = new AdminRoutesYamlFileLoader($this->containerBuilder, new FileLocator($this->appPath . '/config'));
        $loader->load('admin_routes.yaml');
        $this->adminRoutes = $loader->getContent();
    }

    private function loadActions()
    {
        $loader = new ActionsYamlFileLoader($this->containerBuilder, new FileLocator($this->appPath . '/config'));
        $loader->load('actions.yaml');
    }

    private function loadFilters()
    {
        $loader = new FiltersYamlFileLoader($this->containerBuilder, new FileLocator($this->appPath . '/config'));
        $loader->load('filters.yaml');
    }

    private function loadFrontRoutes()
    {
        $loader = new FrontRoutesYamlFileLoader($this->containerBuilder, new FileLocator($this->appPath . '/config'));
        $loader->load('front_routes.yaml');
    }

    private function loadAdminHandlers()
    {
        $loader = new AdminHandlersYamlFileLoader($this->containerBuilder, new FileLocator($this->appPath . '/config'));
        $loader->load('admin_handlers.yaml');
    }

    private function loadRestApiEndpoints()
    {
        $loader = new RestApiYamlFileLoader($this->containerBuilder, new FileLocator($this->appPath . '/config'));
        $loader->load('rest.yaml');
    }

    private function loadAjaxHandlers()
    {
        $loader = new AjaxYamlFileLoader($this->containerBuilder, new FileLocator($this->appPath . '/config'));
        $loader->load('ajax.yaml');
    }

    private function loadAssets()
    {
       
        $finder = new Finder();

        try {
            
            $finder->files()->name('entrypoints.json')->in($this->containerBuilder->getParameter('app_path') . '/dist');

            foreach($finder as $file) {
                $fileData = json_decode($file->getContents(), true);
                foreach($fileData['entrypoints'] as $type => $entrypoint) {
                    if($type === 'admin' && $this->canLoadAdminAssets() === true) {
                        add_action('admin_enqueue_scripts', function() use ($entrypoint) {
                            foreach($entrypoint['js'] as $jsScript) {
                                wp_enqueue_script($jsScript, $jsScript, ['wp-util'], false, true);
                            }
                            foreach($entrypoint['css'] as $cssScript) {                       
                                wp_enqueue_style($cssScript, $cssScript);
                            }
                        }, 99);
                    }
    
                    if($type === 'front') {
                        add_action('wp_enqueue_scripts', function() use ($entrypoint) {
                            foreach($entrypoint['js'] as $jsScript) {                               
                                wp_enqueue_script($jsScript, $jsScript, ['wp-util'], false, true);
                            }
                            foreach($entrypoint['css'] as $cssScript) {                            
                                wp_enqueue_style($cssScript, $cssScript);
                            }
                        }, 99);
                    }
                }
            }            
        } catch (\Throwable $th) {

        }
    }

    private function canLoadAdminAssets()
    {
        $request = $this->containerBuilder->get('request');
        foreach($this->adminRoutes['admin_routes'] as $adminRoute) {
            if($request->query->get('page') === $adminRoute['menu_slug']) {
                return true;
            }
        }        

        return false;
    }
}
