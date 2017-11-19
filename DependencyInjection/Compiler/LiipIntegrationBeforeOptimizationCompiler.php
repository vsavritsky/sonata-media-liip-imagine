<?php

namespace Enemis\SonataMediaLiipImagineBundle\DependencyInjection\Compiler;

use Enemis\SonataMediaLiipImagineBundle\Provider\ImageProvider;
use Enemis\SonataMediaLiipImagineBundle\Thumbnail\LiipImagineThumbnail;
use Enemis\SonataMediaLiipImagineBundle\Twig\Extension\MediaExtension;
use Sonata\MediaBundle\Provider\ImageProvider as SonataImageProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class LiipIntegrationBeforeOptimizationCompiler implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $container->getParameterBag()->set('sonata.media.thumbnail.liip_imagine', LiipImagineThumbnail::class);
        $definition = $container->getDefinition('sonata.media.twig.extension');
        $definition->setClass(MediaExtension::class);
    }
}
