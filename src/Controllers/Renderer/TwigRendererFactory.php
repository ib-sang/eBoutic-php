<?php

namespace Controllers\Renderer;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Controllers\Renderer\TwigRenderer;
use Psr\Container\ContainerInterface;
use Twig\Extension\DebugExtension;

class TwigRendererFactory
{
    /**
     *
     * __invoke
     *
     * @param  mixed $container
     *
     * @return TwigRenderer
     */
    public function __invoke(ContainerInterface $container):TwigRenderer
    {
        $viewPath = $container->get('views.path');
        $loader = new FilesystemLoader($viewPath);
        $twig = new Environment($loader, ['debug' => true]);
        $twig->addExtension(new DebugExtension());
        if ($container->has('twig.extensions')) {
            foreach ($container->get('twig.extensions') as $extension) {
                $twig->addExtension($extension);
            }
        }
        return new TwigRenderer($twig);
    }
}
