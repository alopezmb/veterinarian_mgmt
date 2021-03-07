<?php declare(strict_types=1);

use Auryn\Injector;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Session;

use Felican\Configurations\Rendering\TemplateRenderer;
use Felican\Configurations\Rendering\TwigTemplateRendererFactory;
use Felican\Configurations\Rendering\TemplateDirectory;
use Felican\Configurations\Database\ConnectionFactory;

use Felican\Models\Mailing\MailFactory;
use Felican\Models\Mailing\MailerInterface;

require_once(ROOT_DIR.'/constants/constants.php');

$injector = new Injector();

/*********************** Twig Injections *************************************/

//Delegamos la instanciación de TemplateRenderer a una función. Cuando se haga la
//delegación, Auryn devuelve el resultado de la función que se le pasa cuando
//tenga que instanciar el TemplateRenderer
$injector->delegate(
    TemplateRenderer::class,
    function () use ($injector): TemplateRenderer {
        $factory = $injector->make(TwigTemplateRendererFactory::class);
        return $factory->create();
    }
);
//Provisino la clase TemplateDirectory con el parámetro templateDIR y su valor,
//que es lo que se inyectará al constructor del TemplateDirectory. Hay que hacerlo
//porque lo provisino con un string y no con una clase que Auryn pueda instanciar.
$injector->define(TemplateDirectory::class, [':templateDIR' => TEMPLATE_DIR]);
/**************************************************************************** */

$injector->delegate(
    MailerInterface::class,
    function () use ($injector): MailerInterface {
        $factory = $injector->make(MailFactory::class);
        return $factory->create();
    }
);

$confact = new ConnectionFactory(DB_URL);
$injector->share($confact);

$injector->alias(SessionInterface::class, Session::class);


return $injector;