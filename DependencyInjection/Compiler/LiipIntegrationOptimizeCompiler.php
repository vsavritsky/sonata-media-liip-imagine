<?php

namespace Enemis\SonataMediaLiipImagineBundle\DependencyInjection\Compiler;

use Enemis\SonataMediaLiipImagineBundle\Provider\ImageProvider;
use Enemis\SonataMediaLiipImagineBundle\Thumbnail\LiipImagineThumbnail;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Sonata\MediaBundle\DependencyInjection\Configuration;

class LiipIntegrationOptimizeCompiler implements CompilerPassInterface
{
    private $sonataConfig;
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $this->sonataConfig = $this->getSonataConfig($container);
        $filterSets = $container->getParameterBag()->get('liip_imagine.filter_sets');

        $liipImagineDefinition = $container->getDefinition('sonata.media.thumbnail.liip_imagine');
        $liipImagineDefinition->addMethodCall('setCacheManager', [new Reference('liip_imagine.cache.manager')]);

        $liipImagineDefinition->addMethodCall('setFilterSets', [$filterSets]);

        $liipResizerDefinition = $container->getDefinition('enemis.sonata_media_liip_imagine.liip_resizer');
        $chainResizerDefinition = $container->getDefinition('enemis.sonata_media_liip_imagine.chain_resizer');

        $liipResizerDefinition->replaceArgument(0, new Reference('sonata.media.adapter.image.default'));
        $liipResizerDefinition->replaceArgument(1, '');
        $liipResizerDefinition->replaceArgument(2, new Reference('sonata.media.metadata.proxy'));

        $chainResizerDefinition->replaceArgument(0, new Reference('sonata.media.adapter.image.default'));
        $chainResizerDefinition->replaceArgument(1, '');
        $chainResizerDefinition->replaceArgument(2, new Reference('sonata.media.metadata.proxy'));
        $chainResizerDefinition->addMethodCall('setFilterSets', [$filterSets]);

        $providers = [];
        foreach ($container->findTaggedServiceIds('sonata.media.provider') as $id => $attributes) {
            $definition = $container->getDefinition($id);

            if ($definition->getArgument(4)->__toString() === 'sonata.media.thumbnail.liip_imagine') {
                $providers[$id] = $definition;
                $definition->addMethodCall('setResizer', array(new Reference('enemis.sonata_media_liip_imagine.chain_resizer')));
            }
        }

        $pool = $container->getDefinition('sonata.media.pool');

        $sonataConfig = $this->getSonataConfig($container);

        $calls = $pool->getMethodCalls();
        $mappingFormats = [];

        foreach ($calls as &$call) {
            if ($call[0] !== 'addContext') {
                continue;
            }

            $contextName = $call[1][0];

            $formats = array_filter($filterSets, function ($element) use ($contextName) {
                return strpos($element, $contextName) !== false;
            }, ARRAY_FILTER_USE_KEY);

            $call[1][2] = $formats;

            $mappingFormats[$contextName] = $formats;
        }

        foreach ($sonataConfig['contexts'] as $name => $context) {
            foreach ($context['providers'] as $id) {
                if (!array_key_exists($id, $providers)) {
                    continue;
                }

                $definition = $providers[$id];

                foreach ($mappingFormats[$name] as $format => $config) {
                    $definition->addMethodCall('addFormat', array($format, $config));
                }
            }
        }

        if (array_key_exists('sonata.media.provider.image', $providers)) {
            /**
             * @var Definition $definition
             */
            $definition = $providers['sonata.media.provider.image'];

            if ($definition->getClass() === \Sonata\MediaBundle\Provider\ImageProvider::class) {
                $definition->setClass(ImageProvider::class);
            }
        }
        if (array_key_exists('sonata.media.provider.video', $providers)) {
            /**
             * @var Definition $definition
             */
            $definition = $providers['sonata.media.provider.video'];
            if ($definition->getClass() === \Sonata\MediaBundle\Provider\YouTubeProvider::class) {
                $definition->setClass(YouTubeProvider::class);
            }
        }

        $pool->setMethodCalls($calls);
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return array
     */
    private function getSonataConfig(ContainerBuilder $container)
    {
        $config = $container->getExtensionConfig('sonata_media');
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), $config);

        return $config;
    }
}
