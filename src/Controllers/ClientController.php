<?php
/**
 * Created by PhpStorm.
 * User: Alejandro
 * Date: 20/12/2020
 * Time: 13:40
 */

namespace Felican\Controllers;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Felican\Configurations\Rendering\TemplateRenderer;
use Felican\Models\Cliente\Cliente;
use Felican\Models\Cliente\ClienteDAO;
use Felican\Models\Deuda\Deuda;
use Felican\Models\Deuda\DeudaDAO;


final class ClientController
{
    private $templateRenderer;
    private $session;
    private $clienteDAO;
    private $deudaDAO;

    public function __construct(TemplateRenderer $templateRenderer, Session $session) {
        $this->templateRenderer = $templateRenderer;
        $this->session = $session;
        $this->clienteDAO = new ClienteDAO();
        $this->deudaDAO = new DeudaDAO();
    }

    public function newClientForm(): Response
    {
        $content = $this->templateRenderer->render('Nuevo_Edita_ClienteView.html.twig');
        return new Response($content);
    }

    public function createClient(Request $request): Response
    {
        $cliente = new Cliente(
            -1,
            mb_strtoupper($request->get('nombre'), 'UTF-8'),
            mb_strtoupper($request->get('apellidos'), 'UTF-8'),
            mb_strtoupper($request->get('direccion'), 'UTF-8'),
            $request->get('cp'),
            mb_strtoupper($request->get('poblacion'), 'UTF-8'),
            mb_strtoupper($request->get('provincia'), 'UTF-8'),
            mb_strtolower($request->get('email'), 'UTF-8'),
            strtoupper($request->get('dni')),
            0.0
        );
        $cliente->set_tels($request->get('tlflist'));

        $clientID = $this->clienteDAO->create($cliente);

        return new RedirectResponse('/client/'.$clientID);
    }

    public function editClientForm(Request $request, $vars): Response
    {
        $client_id = $vars['id'];
        $cliente = $this->clienteDAO->read($client_id);

        $content = $this->templateRenderer->render('Nuevo_Edita_ClienteView.html.twig',
            [
                'cliente'=>$cliente,
            ]);
        return new Response($content);
    }

    public function editClient(Request $request, $vars): Response
    {
        $check = 'ok';

        try{
            $clientID = $vars['id'];
            $cliente = $this->clienteDAO->read($clientID);

            $cliente->set_nombre(mb_strtoupper($request->get('nombre'), 'UTF-8'));
            $cliente->set_apellidos(mb_strtoupper($request->get('apellidos'), 'UTF-8'));
            $cliente->set_direccion( mb_strtoupper($request->get('direccion'), 'UTF-8'));
            $cliente->set_cp($request->get('cp'));
            $cliente->set_poblacion(mb_strtoupper($request->get('poblacion'), 'UTF-8'));
            $cliente->set_provincia(mb_strtoupper($request->get('provincia'), 'UTF-8'));
            $cliente->set_email(mb_strtolower($request->get('email'), 'UTF-8'));
            $cliente->set_dni(strtoupper($request->get('dni')));
            $cliente->set_tels($request->get('tlflist'));

            $this->clienteDAO->update($cliente);
            $check='ok';
        }
        catch(\Error $e){
            $check = 'error';
        }
        catch(\Exception $e){
            $check = 'error';

        }finally{

            $message = $check == 'ok' ? 'Cambios en el cliente guardados correctamente' : 'Hubo un error al guardar los cambios en el cliente';
            $check = $check == 'ok' ? 'check_ok' : 'check_error';

            $this->session->getFlashBag()->add(
                $check,
                $message
            );

            $redirectURL = '/client/'.$clientID;
            return new RedirectResponse($redirectURL);
        }

    }


    public function listClients(Request $request) : Response {

        $clientes = $this->clienteDAO->readAll();
        $content = $this->templateRenderer->render('ClientList.html.twig',[
            'listado_clientes'=>$clientes
        ]);
        return new Response($content);
    }

    public function showClient(Request $request, $vars) : Response {


        $flash = $this->getNotificationInfo(); // Recojo primero el mensaje flash
        $client_id = $vars['id'];
        $cliente = $this->clienteDAO->read($client_id);

        $prevNext = explode(',',$this->clienteDAO->readPrevNext($client_id));
        $previousId =  $prevNext[0];
        $nextId = $prevNext[1];

        $content = $this->templateRenderer->render('ClientInfo.html.twig',[
            'cliente'=>$cliente,
            'previousId' => $previousId,
            'nextId' => $nextId,
            'flash' => $flash
        ]);
        return new Response($content);
    }

    public function deleteClient(Request $request, $vars) : Response {

        $clientID = $vars['id'];
        $this->clienteDAO->delete($clientID);

        $redirectURL = "/home";
        return new RedirectResponse($redirectURL);
    }

    /*
     * AJAX Request
     */

    public function saveDebt(Request $request, $vars): Response{

        $clientID = $vars['id'];
        $cliente = $this->clienteDAO->read($clientID);

        $cliente->set_deuda($request->get('deuda'));
        $this->clienteDAO->update($cliente);

        return new Response("Deuda del cliente actualizada.");
    }

    /*
     * AJAX Request
     */

    public function saveDebtHistory(Request $request, $vars): Response{

        $error = 0;

        try{
            $clientID = $vars['id'];
            $historialdeuda = $request->get('historialdeuda');
            $cliente = $this->clienteDAO->read($clientID);

            if (!empty($historialdeuda)) {

                $this->deudaDAO->deleteAllforClient($cliente);

                for ($i = 0; $i < count($historialdeuda); $i++) {

                    $fecha = trim(strval($historialdeuda[$i][0]));
                    $concepto = trim(strval($historialdeuda[$i][1]));

                    $deuda = new Deuda(
                        $fecha,
                        $concepto
                    );
                    $this->deudaDAO->create($deuda, $cliente);
                }

            } else {
                $this->deudaDAO->deleteAllforClient($cliente);
            }
        }
        catch(\Error $e){
            $error=1;
        }
        catch(\Exception $e){
            $error=1;
        }
        finally{
            return new Response($error);
        }

    }


    private function getNotificationInfo():array{

        $flash = array('check_type'=>'','message'=>'');
        $check_ok_message = $this->session->getFlashBag()->get('check_ok',[]);
        $check_error_message = $this->session->getFlashBag()->get('check_error',[]);

        if(!empty($check_ok_message)){
            $flash['check_type'] = 'check_ok';
            $flash['message'] = $check_ok_message;
        }
        else if (!empty($check_error)){
            $flash['check_type'] = 'check_error';
            $flash['message'] = $check_error_message;
        }
        else{
            $flash = array('check_type'=>'','message'=>'');
        }

        return $flash;
    }

}