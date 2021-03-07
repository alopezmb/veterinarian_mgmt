<?php

namespace Felican\Models\Cliente;
use Felican\Models\Mascota\Mascota;
use Felican\Models\Mascota\MascotaDAO;
use Felican\Models\Recordatorio\Recordatorio;
use Felican\Models\Deuda\DeudaDAO;
use \RedBeanPHP\R as R;

class ClienteDAO
{
    private function addTel($lista_tlfs,$cliente_bean){
        $cliente_bean->xownTelefonoList = array();
        foreach($lista_tlfs as $tlfs){
            $tlf_bean = R::dispense(['_type' => 'telefono',
                'tlf' => $tlfs
            ]);
            $cliente_bean->ownTelefonoList[] = $tlf_bean;
        }
    }

    /**
     * Creates an instance of class Cliente
     *
     * When the user first creates a client, only personal information is stored.
     * Thus, by creating a client neither DEBT history nor related PETS yet exist.
     *
     * @param Cliente $cliente An instance of class Cliente
     */
    public function create($cliente)
    {
        R::begin();
        try {
            $cliente_bean = R::dispense(['_type' => 'cliente',
                'pkey' => $cliente->getPkey(),
                'nombre' => $cliente->getNombre(),
                'apellidos' => $cliente->getApellidos(),
                'direccion' => $cliente->getDireccion(),
                'cp' => $cliente->getCp(),
                'poblacion' => $cliente->getPoblacion(),
                'provincia' => $cliente->getProvincia(),
                'email' => $cliente->getEmail(),
                'dni' => $cliente->getDni(),
                'deuda' => $cliente->getDeuda(),

            ]);
            $this->addTel($cliente->getTels(),$cliente_bean);

            $id = R::store($cliente_bean);
            $cliente_bean->pkey = (int) $id;
            R::store($cliente_bean);
            $cliente->set_pkey((int) $id);
            R::commit();
            return $id;
        } catch (\Exception $e) {
            R::rollback();
        }

    }


    /**
     * Finds and returns a client by its database id.
     *
     * This function reads everything about the client, including DEBTS and PETS.
     * Pets will also have their history and vaccine reminders associated. Hence, this
     * function provides access to the full information about a client.
     *
     *
     *
     * @param int|string $id client id.
     * @return Cliente Returns Cliente object or null if not found
     */
    public function read($id)
    {
        R::begin();
        try {

            // Load client from the database. Returns a bean.
            $cliente_bean = R::load('cliente', $id);

            R::commit();

            // Bean has to be converted into an object of class Cliente.
            //The basic properties are initially set.
            $cliente = new Cliente(
                $cliente_bean->pkey,
                $cliente_bean->nombre,
                $cliente_bean->apellidos,
                $cliente_bean->direccion,
                $cliente_bean->cp,
                $cliente_bean->poblacion,
                $cliente_bean->provincia,
                $cliente_bean->email,
                $cliente_bean->dni,
                (float)$cliente_bean->deuda
            );

            /*  TELEFONOS, DEUDAS and MASCOTAS : EACH ONE OF THEM ARE SEPARATE TABLES (1:N) */

            /*  We have to perform special actions in order to retrieve them and assign them as properties
            of the Client object.*/

            //Retrieve associated TELEPHONES.
            $tlfs = [];
            foreach ($cliente_bean->ownTelefonoList as $tel_bean) {
                array_push($tlfs, $tel_bean->tlf);
            }
            $cliente->set_tels($tlfs);

            //Retrieve associated DEBTS to make them accesible by the $cliente object.
            $deudaDAO = new DeudaDAO();
            $deudaDAO->read($cliente);

            //Retrieve associated PETS.
            $mascotaDAO = new MascotaDAO();
            $mascotaDAO->read($cliente);

            return $cliente;

        } catch (\Exception $e) {
            R::rollback();
        }

    }


    /**
     * Updates personal information of the client.
     *
     * NOTE: DEBTS and PETS are not updated using this function.
     *
     *
     * @param int|string $id ID of the client.
     */
    public function update(Cliente $cliente)
    {
        R::begin();
        try {
            $cliente_bean = R::load( 'cliente', $cliente->getPkey() );

            $cliente_bean->nombre = $cliente->getNombre();
            $cliente_bean->apellidos = $cliente->getApellidos();
            $cliente_bean->direccion = $cliente->getDireccion();
            $cliente_bean->cp = $cliente->getCp();
            $cliente_bean->poblacion = $cliente->getPoblacion();
            $cliente_bean->provincia = $cliente->getProvincia();
            $cliente_bean->email = $cliente->getEmail();
            $cliente_bean->dni = $cliente->getDni();
            $cliente_bean->deuda = $cliente->getDeuda();
            $cliente_bean->nombre = $cliente->getNombre();
            $this->addTel($cliente->getTels(),$cliente_bean);


            R::store($cliente_bean);
            R::commit();


        } catch (\Exception $e) {
            R::rollback();
        }

    }

    /**
     * Deletes the client specified by their db id. This function deletes
     * EVERYTHING related to this client.
     *
     * @param Cliente $cliente An instance of class Cliente
     */
    public function delete($id){
        R::begin();
        try {

            /*First we need to delete 1:N information mapped from other tables: Telefono, Deuda, Mascota.
            If we delete the client bean directly, 1:N data gets unrelated but not deleted, thus taking up
            unnecessary space.
             */

            $cliente_bean = R::load('cliente',$id);
            $cliente_bean->xownTelefonoList = array();
            $cliente_bean->xownDeudaList = array();

            /* Deleting mascotas is different because at the same time Mascotas have 1:N relationships
               with other properties. */
            $mascotas_bean_array = $cliente_bean->ownMascotaList;
            $mascotaDAO = new MascotaDAO();
            foreach($mascotas_bean_array as $mascota_bean){
                $mascotaDAO->delete($mascota_bean->id);
            }


            R::store( $cliente_bean ); //Need to save changes so that 1:N data is destroyed correctly.

            /* We can now delete the client bean*/
            R::trash('cliente',$id);
            R::commit();

        } catch (\Exception $e) {
            R::rollback();
        }

    }

    /** Retrieves all the clients.
     *
     * This function is used to populate the client table from
     * 'Listado de Clientes' page. The function retrieves a list with
     * only some properties of clients filled in, just those that are displayed
     * in the table.
     *
     * Retrieving all of the clients' properties would take significantly more resources (and time),
     * and it is not necessary to do so.
     *
     * @return array $cliente_shortlist  List of all client objects with only basic props.
     */
    public function readAll()
    {
        R::begin();
        try {

            $cliente_bean_array = R::findAll('cliente');
            R::commit();
            $cliente_shortlist = [];

            $search = explode(",","ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u");
            $replace = explode(",","c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u");

            foreach ($cliente_bean_array as $cliente_bean) {
                $apellidos = str_replace($search,$replace,mb_strtolower($cliente_bean->apellidos,'UTF-8'));
                $cliente = new Cliente(
                    $cliente_bean->pkey,
                    $cliente_bean->nombre,
                    mb_strtoupper($apellidos,'UTF-8'),
                    $cliente_bean->direccion,
                    $cliente_bean->cp,
                    $cliente_bean->poblacion,
                    $cliente_bean->provincia,
                    $cliente_bean->email,
                    $cliente_bean->dni,
                    (float)$cliente_bean->deuda
                );

                /*
                $tlfs = [];
                foreach( $cliente_bean->ownTelefonoList as $tel_bean ) {
                    array_push($tlfs,(int)$tel_bean->tlf);
                }
                $cliente->set_tels($tlfs); */
                array_push($cliente_shortlist, $cliente);

            }
            return $cliente_shortlist;

        } catch (\Exception $e) {
            R::rollback();
        }

    }

    /** Retrieves all the clients whose pet(s) have a pending reminder during the range of
     * specified dates
     *
     * This function will be called to populate the reminder table from which a pdf is generated.
     * Thus, the function needs to return an object that contains the following properties:
     * -Surnames,Name of client
     * Phone numbers
     * - Name of pet
     * type of reminder
     * date of reminder
     *
     *
     */
    public function readClientsByDate($start,$end)
    {

        R::begin();
        try {

            $res = R::getAll( 'SELECT * FROM recordatorio WHERE recordatorio.recordatorio >= :inicio AND recordatorio.recordatorio <= :final ORDER BY recordatorio ASC',
                [
                    ':inicio' => $start,
                    ':final'=> $end,
                ]

            );
            $selected_clientes = [];
            $recordatorios_beans = R::convertToBeans( 'recordatorio', $res );
            foreach ($recordatorios_beans as $recordatorio_bean){

                $recordatorio = new Recordatorio(
                    $recordatorio_bean->concepto,
                    $recordatorio_bean->recordatorio,
                    $recordatorio_bean->cadencia
                );

                $mascota_bean = $recordatorio_bean->mascota;
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

                $mascota->set_recordatorios($recordatorio);


                $cliente_bean = $mascota_bean->cliente;
                $cliente = new Cliente(
                    $cliente_bean->pkey,
                    $cliente_bean->nombre,
                    $cliente_bean->apellidos,
                    $cliente_bean->direccion,
                    $cliente_bean->cp,
                    $cliente_bean->poblacion,
                    $cliente_bean->provincia,
                    $cliente_bean->email,
                    $cliente_bean->dni,
                    (float) $cliente_bean->deuda
                );
                $tlfs = [];
                foreach( $cliente_bean->ownTelefonoList as $tel_bean ) {
                    array_push($tlfs,$tel_bean->tlf);
                }
                $cliente->set_tels($tlfs);

                $cliente->set_mascotas($mascota);
                array_push($selected_clientes,$cliente);
            }

            R::commit();
            usort($selected_clientes, function($a, $b) {
                $c1 = utf8_encode($a->getApellidos());
                $c2 = utf8_encode($b->getApellidos());
                return strcmp(utf8_encode($a->getApellidos()), utf8_encode($b->getApellidos()));
            });
            return $selected_clientes;
        } catch (\Exception $e) {
            R::rollback();
        }

    }

    /** Retrieves previous and next client in alphabetical order
     *
     * This function is used to pass to the left and right arrows the id of the previous
     * and next clients in the 'descripcioncliente' view.
     *
     * @return  $prevNext_clienteid key value array. The key is current client's id, the values
     * are the previous and next clients (alphabetical order), separated by commas.
     * e.g. current client: Acosta, Juan (id = 5)  Prev client: Abad, Pepa (id = 24). Next client: Blasco, Anton. (id =8)
     * key-> value = '5'->'24,8'
     */
    public function readPrevNext($id)
    {
        R::begin();
        try {
            $prevNext = "";
            $prevNextRaw = R::getAll( 'SELECT id, apellidos FROM Cliente ORDER BY apellidos COLLATE binary');
            R::commit();


            for($i = 0; $i < count($prevNextRaw); $i++){
                if($prevNextRaw[$i]['id'] == intval($id)){

                    $previousIndex = ($i > 0) ? $i-1 : -1;
                    $previousId = $previousIndex != -1 ? $prevNextRaw[$previousIndex]['id'] : -1;

                    $nextIndex = ($i >= count($prevNextRaw)-1) ? -1 : $i+1;
                    $nextId =  $nextIndex != -1 ? $prevNextRaw[$nextIndex]['id'] : -1;

                    $prevNext= $previousId.','.$nextId;
                    return $prevNext;
                }
            }
            return $prevNext;

        } catch (\Exception $e) {
            R::rollback();
        }

    }






}