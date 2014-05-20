<?php

namespace Gfreeau\Bundle\CustomValidationPathBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Finder\Finder;
use InvalidArgumentException;

class AddCustomValidationPathPass implements CompilerPassInterface
{
    const CONFIG_NAME = 'gfreeau_custom_validation_path';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig(self::CONFIG_NAME);

        if (empty($configs)) {
            return;
        }

        $configs = $container->getParameterBag()->resolveValue($configs);

        foreach($configs as $config) {
            $this->processConfig($container, $config);
        }
    }

    private function processConfig(ContainerBuilder $container, array $config)
    {
        if (!isset($config['directories'])) {
            return;
        }

        $requiredOptions = array('path', 'type', 'recursive');
        $requiredKeys = array_flip($requiredOptions);
        $requiredCount = count($requiredOptions);

        foreach($config['directories'] as $directory) {
            if (count(array_intersect_key($directory, $requiredKeys)) < $requiredCount) {
                continue;
            }

            $directory['type'] = $this->getConfigExtension($directory['type']);

            $mappingFilesKey = $this->getMappingKey($directory['type']);

            if (!$container->hasParameter($mappingFilesKey)) {
                continue;
            }

            $files = $this->getValidatorFiles($directory['path'], $directory['type'], $directory['recursive']);

            if (empty($files)) {
                continue;
            }

            foreach($files as $file) {
                $container->addResource(new FileResource($file));
            }

            $files = array_merge(
                $container->getParameter($mappingFilesKey),
                $files
            );

            $container->setParameter($mappingFilesKey, $files);
        }
    }

    /**
     * @param string $path
     * @param string $type
     * @param boolean $recursive yml|xml
     * @return array
     * @throws \InvalidArgumentException
     */
    private function getValidatorFiles($path, $type, $recursive)
    {
        if (!is_dir($path)) {
            throw new InvalidArgumentException(sprintf("%s: validation file path '%s' does not exist", self::CONFIG_NAME, $path));
        }

        if (!in_array($type, array('xml', 'yml'))) {
            throw new InvalidArgumentException(sprintf("%s: invalid validation file type '%s'", self::CONFIG_NAME, $type));
        }

        $finder = new Finder();

        if (!$recursive) {
            $finder->depth(0);
        }

        $finder->files()->in($path)->name('*.' . $type);

        if ($finder->count() == 0) {
            return array();
        }

        $files = array();

        foreach($finder as $file)
        {
            $files[] = $file->getRealpath();
        }

        return $files;
    }

    private function getMappingKey($extension)
    {
        if ('yml' == $extension) {
            $extension = 'yaml';
        }

        return 'validator.mapping.loader.'.$extension.'_files_loader.mapping_files';
    }

    private function getConfigExtension($extension)
    {
        $extTranslations = array(
            'yaml' => 'yml',
        );

        if (isset($extTranslations[$extension])) {
            return $extTranslations[$extension];
        }

        return $extension;
    }
}
