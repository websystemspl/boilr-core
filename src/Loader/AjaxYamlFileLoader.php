<?php

namespace Websystems\BoilrCore\Loader;

use Websystems\BoilrCore\Resources\Action;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\DependencyInjection\Loader\FileLoader;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Websystems\BoilrCore\Resources\Ajax;

class AjaxYamlFileLoader extends FileLoader
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
        if (!isset($content['ajax'])) {
            return;
        }

        if (!\is_array($content['ajax'])) {
            throw new InvalidArgumentException(sprintf('The "ajax" key should contain an array in "%s". Check your YAML syntax.', $file));
        }

        foreach ($content['ajax'] as $id => $action) {
            $this->parseDefinition($id, $action, $file);
        }
    }

    private function parseDefinition(string $id, $action, string $file, bool $return = false)
    {

        if (null === $action) {
            $action = [];
        }

        if(isset($action['no_priv'])) {
            $noPriv = $action['no_priv'];
        } else {
            $noPriv = true;
        }

        $action = new Ajax(
            $this->container,
            $id,
            $action['controller'],
            $action['action'],
            $noPriv,
        );

        $action->publish();
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
            if (\in_array($namespace, ['ajax'])) {
                continue;
            }
        }

        foreach ($content as $namespace => $data) {
            if (\in_array($namespace, ['ajax'])) {
                continue;
            }

            throw new InvalidArgumentException(sprintf('The file "%s" is not valid. It should contain "ajax". Check your YAML syntax.', $file));
        }

        return $content;
    }
}
