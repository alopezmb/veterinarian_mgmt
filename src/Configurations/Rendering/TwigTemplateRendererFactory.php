<?php declare(strict_types=1);

namespace Felican\Configurations\Rendering;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Twig_Loader_Filesystem;
use Twig_Environment;
use Twig_Function;

final class TwigTemplateRendererFactory
{

    private $templateDirectory;
    private $session;

    public function __construct(
        TemplateDirectory $templateDirectory,
        Session $session
    ) {
        $this->templateDirectory = $templateDirectory;
        $this->session = $session;
    }

    public function create(): TwigTemplateRenderer
    {
        $loader = new Twig_Loader_Filesystem([
            $this->templateDirectory->toString(),
        ]);
        $twigEnvironment = new Twig_Environment($loader);

        $twigEnvironment->addFunction(
            new Twig_Function('get_flash_bag', function (): FlashBagInterface {
                return $this->session->getFlashBag();
            })
        );

        return new TwigTemplateRenderer($twigEnvironment);
    }
}