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
use Felican\Models\Cliente\ClienteDAO;
use Felican\Models\Mascota\Mascota;
use Felican\Models\Mascota\MascotaDAO;
use Felican\Models\Recordatorio\Recordatorio;
use Felican\Models\Recordatorio\RecordatorioDAO;


final class PetController
{
    private $templateRenderer;
    private $session;
    private $clienteDAO;
    private $mascotaDAO;

    public function __construct(TemplateRenderer $templateRenderer, Session $session) {
        $this->templateRenderer = $templateRenderer;
        $this->session = $session;
        $this->clienteDAO = new ClienteDAO();
        $this->mascotaDAO = new MascotaDAO();
        $this->recordatorioDAO = new RecordatorioDAO();
    }

    public function newPetForm(Request $request, $vars): Response
    {
        $id_cliente= $vars['ownerId'];
        $listarazasperros = [];
        $listarazasgatos = [];
        $cliente = $this->clienteDAO->read($id_cliente);
        $nombrecliente = $cliente->getApellidos().", ".$cliente->getNombre();
        include_once($_SERVER['DOCUMENT_ROOT'].'/constants/listarazas.php'); //cargo las variables listarazasgatos y listarazasperros
        $content = $this->templateRenderer->render('Nueva_Edita_MascotaView.html.twig',
            [
                'nombrecliente'=>$nombrecliente,
                'id_cliente' => $id_cliente,
                'razas_perros'=>$listarazasperros,
                'razas_gatos' =>$listarazasgatos
            ]);
        return new Response($content);
    }

    public function createOrEditPet(Request $request , $vars): Response
    {
        $check = 'ok';
        $id_cliente = "";
        $id_mascota = "";

        try {

            $especieselection = $request->get('especieselection');
            $cruce = $request->get('ismestizo');

            if (strcmp(trim($especieselection), 'perro') == 0) {
                $raza_perro = trim(mb_strtolower($request->get('razasperros'), 'UTF-8'));
                $raza = $cruce == 'si' && strcmp($raza_perro, 'mestizo') != 0 ? 'Cruce de ' . $raza_perro : $raza_perro;

            } elseif (strcmp(trim($request->get('especieselection')), 'gato') == 0) {
                $raza_gato = $request->get('razasgatos');
                $raza = $cruce == 'si' ? 'Cruce de ' . $raza_gato : $raza_gato;
            } else {
                $raza = "NA";
            }

            $capa = empty($_POST['capamascota']) ? "-" : $_POST['capamascota'];
            $pelo = empty($_POST['pelomascota']) ? "-" : $_POST['pelomascota'];
            $chip = empty($_POST['chipmascota']) ? "-" : $_POST['chipmascota'];

            $nombre = $request->get('nombremascota');
            $igualado = $request->get('igualado');
            $fecha_nacimiento = $request->get('nacimientomascota');
            $especie = $request->get('especieselection');
            $sexo = $request->get('sexomascota');

            // Creacion Mascota
            if(array_key_exists('ownerId', $vars)){

                $id_cliente = strval($vars['ownerId']);

                $mascota = new Mascota(
                    -1,
                    $nombre,
                    $igualado,
                    $fecha_nacimiento,
                    $especie,
                    $raza,
                    $capa,
                    $pelo,
                    $sexo,
                    $chip
                );

                $this->mascotaDAO->create($mascota, $id_cliente);

                $recordatorios = $mascota->getInitialRecordatorios($especie);
                foreach($recordatorios as $recordatorio){
                    $this->recordatorioDAO->create($recordatorio,$mascota);
                }


            }

            //Edicion Mascota

            elseif(array_key_exists('id', $vars)){

                $id_mascota = strval($vars['id']);
                $mascota = $this->mascotaDAO->xRead($id_mascota);

                $mascota->set_nombre($nombre);
                $mascota->set_fechanacimiento($fecha_nacimiento);
                $mascota->set_especie($especie);
                $mascota->set_raza($raza);
                $mascota->set_capa($capa);
                $mascota->set_pelo($pelo);
                $mascota->set_sexo($sexo);
                $mascota->set_nchip($chip);

                $this->mascotaDAO->update($mascota);

            }
            else{
                $check = 'error';
            }
        }

        catch(\Error $e){
            $check = 'error';
        }
        catch(\Exception $e){
            $check = 'error';

        }finally{

            $message="";
            $redirectURL="";

            if(!empty($id_cliente)){
                //Crear mascota: acciones finales
                $message = $check == 'ok' ? 'Mascota registrada correctamente' : 'Hubo un error al añadir la mascota al registro.';
                $check = $check == 'ok' ? 'check_ok' : 'check_error';
                $redirectURL = '/client/'.$id_cliente;
            }
            elseif(!empty($id_mascota)){
                //Editar mascota: acciones finales
                $message = $check == 'ok' ? 'Datos de la mascota actualizados correctamente.' : 'Hubo un error al actualizar la información de la mascota.';
                $check = $check == 'ok' ? 'check_ok' : 'check_error';
                $redirectURL = '/pet/'.$id_mascota;
            }
            else{
                $message = "Se ha producido un error.";
                $redirectURL = '/';

            }

            $this->session->getFlashBag()->add(
                $check,
                $message
            );

            return new RedirectResponse($redirectURL);
        }
    }

    public function editPetForm(Request $request, $vars): Response
    {
        $listarazasperros="";
        $listarazasgatos="";
        $mascota_id = $vars['id'];
        $mascota = $this->mascotaDAO->xRead($mascota_id);
        include_once($_SERVER['DOCUMENT_ROOT'].'/constants/listarazas.php'); //cargo las variables listarazasgatos y listarazasperros

        $content = $this->templateRenderer->render('Nueva_Edita_MascotaView.html.twig',
            [
                'mascota'=>$mascota,
                'razas_perros'=>$listarazasperros,
                'razas_gatos' =>$listarazasgatos
            ]);
        return new Response($content);
    }




    public function listPets(Request $request) : Response {

        $mascotas = $this->mascotaDAO->readAll();
        $content = $this->templateRenderer->render('PetList.html.twig',[
            'listado_mascotas'=>$mascotas
        ]);
        return new Response($content);
    }

    public function showPet(Request $request, $vars) : Response {

        $flash = $this->getNotificationInfo(); // Recojo primero el mensaje flash
        $mascota_id = $vars['id'];
        $mascota = $this->mascotaDAO->xRead($mascota_id);

        $content = $this->templateRenderer->render('PetInfo.html.twig',[
            'mascota'=>$mascota,
            'flash' => $flash
        ]);
        return new Response($content);
    }

    public function deletePet(Request $request, $vars) : Response {

        $mascota_id = $vars['id'];
        $client_id = $request->get('id_cliente');
        $this->mascotaDAO->delete($mascota_id);

        $redirectURL = "/client/".$client_id;
        return new RedirectResponse($redirectURL);
    }

    // A partir de aquí son llamadas AJAX

    public function savePetHistory(Request $request, $vars) : Response {

        $error=0;

        try{
            $historialmascota = trim($request->get('historialmascota'));
            $mascota_id = $vars['id'];
            $mascota = $this->mascotaDAO->xRead($mascota_id);
            $mascota->set_historial($historialmascota);
            $this->mascotaDAO->update($mascota);
        }
        catch(\Exception $e){
            $error=1;
        }
        catch(\Error $e){
            $error=1;
        }
        finally{
            return new Response($error);
        }
    }

    public function savePetObservations(Request $request, $vars) : Response {

        $error=0;

        try{
            $observacionesmascota= trim($request->get('observacionesmascota'));
            $mascota_id = $vars['id'];
            $mascota = $this->mascotaDAO->xRead($mascota_id);
            $mascota->set_observaciones($observacionesmascota);
            $this->mascotaDAO->update($mascota);
        }
        catch(\Exception $e){
            $error=1;
        }
        catch(\Error $e){
            $error=1;
        }
        finally{
            return new Response($error);
        }
    }

    public function updateIguala(Request $request, $vars) : Response {

        $error=0;

        try{
            $id_mascota = $vars['id'];
            $mascota = $this->mascotaDAO->xRead($id_mascota);
            $igualavalue = $request->get('igualada');
            $mascota->set_igualada($igualavalue);
            $this->mascotaDAO->update($mascota);
        }
        catch(\Exception $e){
            $error=1;
        }
        catch(\Error $e){
            $error=1;
        }
        finally{
            return new Response($error);
        }
    }

    public function savePetReminders(Request $request) : Response
    {

        $error = 0;
        $recordatorios = $request->get('recordatorios');

        try{
            if (!empty($recordatorios)) {

                for ($i = 0; $i < count($recordatorios); $i++) {

                    $concepto = trim(strtolower(strval($recordatorios[$i][0])));
                    $fecha_rec = trim($recordatorios[$i][1]);
                    $cadencia_concepto = (int)trim(strval($recordatorios[$i][2]));
                    $pkey = (int)trim(strval($recordatorios[$i][3]));

                    $fecha_explode = explode('/', $fecha_rec);
                    if (count($fecha_explode) != 3 && !empty($fecha_rec)) {
                        $error = 1;
                        break;
                    } else if (!empty($fecha_rec) && !checkdate($fecha_explode[1], $fecha_explode[0], $fecha_explode[2])) {
                        $error = 1;
                        break;
                    } else {

                        $recordatorio = new Recordatorio(
                            $concepto,
                            $fecha_rec,
                            $cadencia_concepto
                        );
                        $recordatorio->set_pkey($pkey);

                        $this->recordatorioDAO->update($recordatorio);

                        $error = 0;
                    }
                }

            } else {
                $error = 1;
            }
        }
        catch (\Error $e) {
            $error = 1;
        } catch (\Exception $e) {
            $error = 1;
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