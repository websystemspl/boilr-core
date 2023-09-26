<?php

namespace Websystems\BoilrCore\Loader;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\DependencyInjection\Loader\FileLoader;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;


class AdminHandlersYamlFileLoader extends FileLoader
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
        if (!isset($content['admin_handlers'])) {
            return;
        }

        if (!\is_array($content['admin_handlers'])) {
            throw new InvalidArgumentException(sprintf('The "admin_handlers" key should contain an array in "%s". Check your YAML syntax.', $file));
        }

        foreach ($content['admin_handlers'] as $id => $adminHandler) {
            $this->parseDefinition($id, $adminHandler, $file);
        }
    }

    private function parseDefinition(string $id, $adminHandler, string $file, bool $return = false)
    {

        if (null === $adminHandler) {
            $adminHandler = [];
        }

        if (!\is_array($adminHandler)) {
            throw new InvalidArgumentException(sprintf('A admin_handler definition must be an array but "%s" found for admin_handler "%s" in "%s". Check your YAML syntax.', get_debug_type($adminHandler), $id, $file));
        }

        if (!isset($adminHandler['controller'])) {
            throw new InvalidArgumentException(sprintf('There is no controller parameter for "%s" in "%s". Check your YAML syntax.', $id, $file));
        }

        if (empty($adminHandler['controller'])) {
            throw new InvalidArgumentException(sprintf('Parameter controller can not be empty for "%s" in "%s". Check your YAML syntax.', $id, $file));
        }

        if(!class_exists($adminHandler['controller'])) {
            throw new InvalidArgumentException(sprintf('There is no class for "%s" in "%s". Check your YAML syntax.', $id, $file));
        }

        if (!isset($adminHandler['action'])) {
            throw new InvalidArgumentException(sprintf('There is no action parameter for "%s" in "%s". Check your YAML syntax.', $id, $file));
        }

        if (empty($adminHandler['action'])) {
            throw new InvalidArgumentException(sprintf('Parameter action can not be empty for "%s" in "%s". Check your YAML syntax.', $id, $file));
        }

        $object = $this->container->get($adminHandler['controller']);
        add_action('admin_post_' . $id, [$object, $adminHandler['action']]);
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
            if (\in_array($namespace, ['admin_handlers'])) {
                continue;
            }
        }

        foreach ($content as $namespace => $data) {
            if (\in_array($namespace, ['admin_handlers'])) {
                continue;
            }

            throw new InvalidArgumentException(sprintf('The file "%s" is not valid. It should contain "admin_handlers". Check your YAML syntax.', $file));
        }

        return $content;
    }
}
