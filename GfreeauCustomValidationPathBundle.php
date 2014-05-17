<?php

namespace Gfreeau\Bundle\CustomValidationPathBundle;

use Gfreeau\Bundle\CustomValidationPathBundle\DependencyInjection\CompilerPass\AddCustomValidationPathPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class GfreeauCustomValidationPathBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddCustomValidationPathPass());
    }
}
