<?php
/**
 * Created by PhpStorm.
 * User: Alejandro
 * Date: 19/12/2020
 * Time: 23:26
 */

namespace Felican\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Felican\Configurations\Rendering\TemplateRenderer;

final class HomepageController
{
    private $templateRenderer;

    public function __construct(TemplateRenderer $templateRenderer) {
        $this->templateRenderer = $templateRenderer;
    }

    public function show(): Response
    {

        $content = $this->templateRenderer->render('HomeView.html.twig');
        return new Response($content);
    }
}