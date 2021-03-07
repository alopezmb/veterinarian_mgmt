<?php
/*include('Deuda.php'); */

namespace Felican\Models\Deuda;
use Felican\Models\Cliente\Cliente;
use \RedBeanPHP\R as R;

class DeudaDAO
{
    public function connect()
    {
        connect();

    }

    /**
     * Creates a debt entry in the database (1:N with Cliente) and links it to
     * the given Cliente.
     *
     * @param Deuda $deuda  Deuda object we want to store as a record in the db.
     * @param Cliente $cliente Cliente instance we want to associate the debt to.
     */
    public function create(Deuda $deuda , Cliente $cliente)
    {
        R::begin();
        try {
            $deuda_bean = R::dispense(['_type' => 'deuda',
                'fecha' => $deuda->getFecha(),
                'concepto' => $deuda->getConcepto(),
            ]);
            $cliente_bean = R::load( 'cliente', $cliente->getPkey() );
            $cliente_bean->ownDeudaList[] = $deuda_bean;
            R::store($cliente_bean);

            $deuda_saved_bean = end( $cliente_bean->ownDeudaList );
            $id = $deuda_saved_bean->id;
            $deuda_saved_bean->pkey = (int) $id;
            $deuda->set_pkey((int) $id);
            R::store($deuda_saved_bean);

            R::commit();

        } catch (\Exception $e) {
            R::rollback();
        }

    }

    public function read(Cliente $cliente)
    {
        R::begin();
        try {
            $cliente_bean = R::load( 'cliente', $cliente->getPkey() );
            $deudas = [];
            foreach( $cliente_bean->ownDeudaList as $d ) {
                $deuda = new Deuda(
                    $d->fecha,
                    $d->concepto
                );
                $deuda->set_pkey($d->id);
                array_push($deudas,$deuda);
            }
            $cliente->set_historialdeuda($deudas);
            R::commit();

        } catch (\Exception $e) {
            R::rollback();
        }
    }

    public function update(Deuda $deuda)
    {

        R::begin();
        try {

            $deuda_bean = R::load( 'deuda', $deuda->getPkey() );
            $deuda_bean->nombre = $deuda->getFecha();
            $deuda_bean->concepto = $deuda->getConcepto();

            R::store($deuda_bean);
            R::commit();



        } catch (\Exception $e) {
            R::rollback();
        }
    }

    public function deleteAllforClient(Cliente $cliente){
        R::begin();
        try {
            $cliente_bean = R::load('cliente',$cliente->getPkey());
            $cliente_bean->xownDeudaList = array();
            R::store( $cliente_bean );
            R::commit();

        } catch (\Exception $e) {
            R::rollback();
        }

    }

    public function delete($id){
        R::begin();
        try {

            R::trash('deuda',$id);
            R::commit();

        } catch (\Exception $e) {
            R::rollback();
        }

    }

}