<?php

/*require_once '../phpmodules/db/connect.php';
include('Mascota.php');
include('RecordatorioDAO.php');
*/

namespace Felican\Models\Mascota;
use Felican\Models\Cliente\Cliente;
use Felican\Models\Recordatorio\RecordatorioDAO;
use \RedBeanPHP\R as R;

class MascotaDAO
{

    public function connect()
    {
        connect();

    }

    /**
     * Creates a pet entry in the db for the specified Mascota object.
     * A client object is also required because Cliente:Mascota is 1:N, so Mascota
     * is stored in another table. The Client object passed as argument is used to map the
     * created pet to the specified client.
     *
     * @param Mascota $mascota Instance of the Mascota we want to store in the db.
     * @param String $id_cliente : ID of the client  which the created Mascota maps to.
     */
    public function create(Mascota $mascota , String $id_cliente)
    {
        R::begin();
        try {

            /*Create the pet bean. */

            $mascota_bean = R::dispense(['_type' => 'mascota',
                'nombre' => $mascota->getNombre(),
                'igualada' => $mascota->getIgualada(),
                'birthdate' => $mascota->getFechanacimiento(),
                'especie' => $mascota->getEspecie(),
                'raza' => $mascota->getRaza(),
                'capa' => $mascota->getCapa(),
                'pelo' => $mascota->getPelo(),
                'sexo' => $mascota->getSexo(),
                'nchip' => $mascota->getNchip(),
            ]);

            /* Now we have to specify the mapping between the pet and the owner.*/
            $cliente_bean = R::load( 'cliente', $id_cliente );
            $cliente_bean->ownMascotaList[] = $mascota_bean;
            R::store($cliente_bean); //Changes are now synced.


            /* We now set client id and owner name as fields in the 'mascota' table*/
            /* Plus save the id given by the db into another property (pkey)*/
            $mascota_saved_bean = end( $cliente_bean->ownMascotaList );
            $id = $mascota_saved_bean->id;
            $mascota_saved_bean->pkey = (int) $id;
            $mascota->set_pkey((int) $id);
            R::store($mascota_saved_bean);

            R::commit();

        } catch (\Exception $e) {
            R::rollback();
        }

    }

    /**
     * Retrieve a Mascota instance and assign it as a property to the specified client.
     *
     * This read method does not return anything. It reads the Client object passed as argument and
     * retrieves the pets owned by that client. These pets are turned into Mascota objects and passed
     * as a property of the Cliente object given.
     *
     * NOTE: only the basic properties of Mascota are converted into the object
     * because in the page where this is used only these are needed. Advanced properties such
     * as the 1:N Recordatorios  ,etc will only take up unnecessary space.
     *
     * @param
     * @param Cliente $cliente Owner of the pets.
     */
    public function read(Cliente $cliente)
    {
        R::begin();
        try {
            $cliente_bean = R::load( 'cliente', $cliente->getPkey() );
            $mascotas = [];
            foreach( $cliente_bean->ownMascotaList as $m ) {
                $mascota = new Mascota(
                    $m->id,
                    $m->nombre,
                    $m->igualada,
                    $m->birthdate,
                    $m->especie,
                    $m->raza,
                    $m->capa,
                    $m->pelo,
                    $m->sexo,
                    $m->nchip
                );

                array_push($mascotas,$mascota);


            }

            $cliente->set_mascotas($mascotas); //filling a Cliente object with the Mascota objects que owns.
            R::commit();

        } catch (\Exception $e) {
            R::rollback();
        }
    }

    /**
     * eXclusive Read Mascota : Mascota full object created and returned.
     *
     * This is used to manage the page 'descripcionmascota', where we need all the information
     * on them.
     * @param
     * @param Cliente $cliente Owner of the pets.
     */
    public function xRead($id_mascota)
    {
        $mascota = null;
        R::begin();

        try {
            $mascota_bean = R::load('mascota', $id_mascota);
            $mascota = new Mascota(
                $mascota_bean->id,
                $mascota_bean->nombre,
                $mascota_bean->igualada,
                $mascota_bean->birthdate,
                $mascota_bean->especie,
                $mascota_bean->raza,
                $mascota_bean->capa,
                $mascota_bean->pelo,
                $mascota_bean->sexo,
                $mascota_bean->nchip
            );

            $owner = $mascota_bean->cliente;
            $owner_name = $owner->apellidos.", ".$owner->nombre;
            $mascota->set_propietario([$owner->id,$owner_name]); //nombre del propietario en [1]

            $mascota->set_historial($mascota_bean->historial); //Get pet's history
            $mascota->set_observaciones($mascota_bean->observaciones);

            /* Pets have a 1:N relationship with Recordatorios. So we perform a similar action
            to what Clientes do with Mascotas, this time, the property 'recordatorio' is filled
            for each Mascota object */
            $recordatorioDAO = new RecordatorioDAO();
            $recordatorioDAO->read($mascota);
            R::commit();
            return $mascota;

        } catch (\Exception $e) {
            R::rollback();
        }
    }

    /**
     * Updates the basic information about a Mascota object, i.e.
     * info which is not 1:N. Thus, Recordatorios are not updated here, however,
     * the pets' history is, as it is simply a string property, not another table.
     *
     *
     * @param Mascota $mascota Instance of Mascota whose info we wish to udpate.
     */
    public function update(Mascota $mascota)
    {
        R::begin();
        try {
            $mascota_bean = R::load( 'mascota', $mascota->getPkey() );
            $mascota_bean->nombre = $mascota->getNombre();
            $mascota_bean->igualada = $mascota->getIgualada();
            $mascota_bean->birthdate = $mascota->getFechanacimiento();
            $mascota_bean->especie = $mascota->getEspecie();
            $mascota_bean->raza = $mascota->getRaza();
            $mascota_bean->capa = $mascota->getCapa();
            $mascota_bean->pelo = $mascota->getPelo();
            $mascota_bean->sexo = $mascota->getSexo();
            $mascota_bean->nchip = $mascota->getNchip();
            $mascota_bean->historial = $mascota->getHistorial();
            $mascota_bean->observaciones = $mascota->getObservaciones();
            R::store($mascota_bean);
            R::commit();



        } catch (\Exception $e) {
            R::rollback();
        }

    }

    /**
     * Delete the entry corresponding to the Mascota with the given id.
     * This will delete EVERYTHING related to the Mascota instance,
     * (all of its child properties, the owner record is, of course, kept intact).
     *
     * NOTE that deleting a Client record will automatically delete all the Mascotas related
     * to it, but this is viceversa not true.
     *
     * @param int $id DB id of the Mascota instance we want to delete.
     */
    public function delete($id){
        R::begin();
        try {
            /*First delete 1:N records from Recordatorio */
            $mascota_bean = R::load('mascota',$id);
            $mascota_bean->xownRecordatorioList = array();
            R::store($mascota_bean);

            /*Now mascota can be correctly deleted. */
            R::trash('mascota',$id);
            R::commit();

        } catch (\Exception $e) {
            R::rollback();
        }

    }

    /** Reads all the mascotas table and retrieves an array containing Mascota objects of
     * each record.
     *
     * NOTE that, as with Cliente's readAll(), this only retrieves a basic set of properties,
     * because this function is intended to be used to fill the 'listado mascotas' page.
     *
     * @return array $mascota_array an array containing all the Mascota objects contained in the db.
     */
    public function readAll()
    {
        R::begin();
        try {
            $mascota_bean_array = R::findAll('mascota');
            R::commit();
            $mascota_array = [];

            foreach($mascota_bean_array as $mascota_bean){
                $mascota = new Mascota(
                    $mascota_bean->pkey,
                    $mascota_bean->nombre,
                    $mascota_bean->igualada,
                    $mascota_bean->birthdate,
                    $mascota_bean->especie,
                    $mascota_bean->raza,
                    $mascota_bean->capa,
                    $mascota_bean->pelo,
                    $mascota_bean->sexo,
                    $mascota_bean->nchip
                );

                $owner = $mascota_bean->cliente;
                $owner_name = $owner->apellidos.", ".$owner->nombre;
                $mascota->set_propietario([$owner->id,$owner_name]);

                array_push($mascota_array,$mascota);

            }

            return $mascota_array;

        } catch (\Exception $e) {
            R::rollback();
        }

    }


}