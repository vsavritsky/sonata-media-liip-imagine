<?php

/**
 * @author Andrey Stadnik <stadnikandreypublic@gmail.com>
 */
namespace Enemis\SonataMediaLiipImagineBundle;

use Enemis\SonataMediaLiipImagineBundle\DependencyInjection\Compiler\LiipIntegrationBeforeOptimizationCompiler;
use Enemis\SonataMediaLiipImagineBundle\DependencyInjection\Compiler\LiipIntegrationOptimizeCompiler;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SonataMediaLiipImagineBundle extends Bundle
{
    /**
     * Builds the bundle.
     *
     * It is only ever called once when the cache is empty.
     *
     * This method can be overridden to register compilation passes,
     * other extensions, ...
     *
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new LiipIntegrationBeforeOptimizationCompiler());
        $container->addCompilerPass(new LiipIntegrationOptimizeCompiler(), PassConfig::TYPE_OPTIMIZE);
    }
}
