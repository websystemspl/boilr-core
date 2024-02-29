<?php

namespace Websystems\BoilrCore\Loader;

use Symfony\Component\Yaml\Yaml;
use Websystems\BoilrCore\Resources\Action;
use Websystems\BoilrCore\Resources\AdminRoute;
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\DependencyInjection\Loader\FileLoader;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

class AdminRoutesYamlFileLoader extends FileLoader
{
    private $yamlParser;

    public function load($resource, string $type = null)
    {
        $path = $this->locator->locate($resource);
        $content = $this->loadFile($path);

        // empty file
        if (null === $content) {
            return;
        }

        $this->setCurrentDir(\dirname($path));

        $this->parseDefinitions($content, $path);

    }

    public function supports($resource, string $type = null)
    {
        if (!\is_string($resource)) {
            return false;
        }

        if (null === $type && \in_array(pathinfo($resource, \PATHINFO_EXTENSION), ['yaml', 'yml'], true)) {
            return true;
        }

        return \in_array($type, ['yaml', 'yml'], true);
    }


    private function parseDefinitions(array $content, string $file)
    {
        if (!isset($content['admin_routes'])) {
            return;
        }

        if (!\is_array($content['admin_routes'])) {
            throw new InvalidArgumentException(sprintf('The "admin_routes" key should contain an array in "%s". Check your YAML syntax.', $file));
        }

        foreach ($content['admin_routes'] as $id => $action) {
            $this->parseDefinition($id, $action, $file);
        }
    }

    private function parseDefinition(string $id, $action, string $file, bool $return = false)
    {

        if (null === $action) {
            $action = [];
        }

        // if (!\is_array($action)) {
        //     throw new InvalidArgumentException(sprintf('A action definition must be an array but "%s" found for action "%s" in "%s". Check your YAML syntax.', get_debug_type($action), $id, $file));
        // }

        // if (!isset($action['hook'])) {
        //     throw new InvalidArgumentException(sprintf('There is no hook parameter for "%s" in "%s". Check your YAML syntax.', $id, $file));
        // }

        // if (empty($action['hook'])) {
        //     throw new InvalidArgumentException(sprintf('Parameter hook can not be empty for "%s" in "%s". Check your YAML syntax.', $id, $file));
        // }

        // if (!isset($action['controller'])) {
        //     throw new InvalidArgumentException(sprintf('There is no controller parameter for "%s" in "%s". Check your YAML syntax.', $id, $file));
        // }

        // if (empty($action['controller'])) {
        //     throw new InvalidArgumentException(sprintf('Parameter controller can not be empty for "%s" in "%s". Check your YAML syntax.', $id, $file));
        // }

        // if(!class_exists($action['controller'])) {
        //     throw new InvalidArgumentException(sprintf('There is no class for "%s" in "%s". Check your YAML syntax.', $id, $file));
        // }

        // if (!isset($action['priority'])) {
        //     throw new InvalidArgumentException(sprintf('There is no priority parameter for "%s" in "%s". Check your YAML syntax.', $id, $file));
        // }

        // if (empty($action['priority'])) {
        //     throw new InvalidArgumentException(sprintf('Parameter priority can not be empty for "%s" in "%s". Check your YAML syntax.', $id, $file));
        // }

        // if (!isset($action['action'])) {
        //     throw new InvalidArgumentException(sprintf('There is no action parameter for "%s" in "%s". Check your YAML syntax.', $id, $file));
        // }

        // if (empty($action['action'])) {
        //     throw new InvalidArgumentException(sprintf('Parameter action can not be empty for "%s" in "%s". Check your YAML syntax.', $id, $file));
        // }

        // if (!isset($action['params'])) {
        //     throw new InvalidArgumentException(sprintf('There is no params parameter for "%s" in "%s". Check your YAML syntax.', $id, $file));
        // }

        // if (empty($action['params'])) {
        //     throw new InvalidArgumentException(sprintf('Parameter params can not be empty for "%s" in "%s". Check your YAML syntax.', $id, $file));
        // }

        $adminPage = new AdminRoute(
            $action['parent_slug'],
            __($action['page_title'], basename(plugin_basename($this->container->getParameter('app_path')))),
            __($action['menu_title'], basename(plugin_basename($this->container->getParameter('app_path')))),
            $action['capability'],
            $action['menu_slug'],
            $this->container->get($action['controller']),
            $action['action'],
            $action['iconUrl'],
            $action['position'],                
        );

        $adminPage->publish();
    }

    protected function loadFile($file)
    {
        if (!class_exists('Symfony\Component\Yaml\Parser')) {
            throw new RuntimeException('Unable to load YAML config files as the Symfony Yaml Component is not installed.');
        }

        if (!stream_is_local($file)) {
            throw new InvalidArgumentException(sprintf('This is not a local file "%s".', $file));
        }

        if (!is_file($file)) {
            throw new InvalidArgumentException(sprintf('The file "%s" does not exist.', $file));
        }

        if (null === $this->yamlParser) {
            $this->yamlParser = new YamlParser();
        }

        try {
            $configuration = $this->yamlParser->parseFile($file, Yaml::PARSE_CONSTANT | Yaml::PARSE_CUSTOM_TAGS);
        } catch (ParseException $e) {
            throw new InvalidArgumentException(sprintf('The file "%s" does not contain valid YAML: ', $file).$e->getMessage(), 0, $e);
        }

        return $this->validate($configuration, $file);
    }

    private function validate($content, string $file): ?array
    {
        if (null === $content) {
            return $content;
        }

        if (!\is_array($content)) {
            throw new InvalidArgumentException(sprintf('The file "%s" is not valid. It should contain an array. Check your YAML syntax.', $file));
        }

        foreach ($content as $namespace => $data) {
            if (\in_array($namespace, ['admin_routes'])) {
                continue;
            }
        }

        foreach ($content as $namespace => $data) {
            if (\in_array($namespace, ['admin_routes'])) {
                continue;
            }

            throw new InvalidArgumentException(sprintf('The file "%s" is not valid. It should contain "admin_routes". Check your YAML syntax.', $file));
        }

        return $content;
    }
}
