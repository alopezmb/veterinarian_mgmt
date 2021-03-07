<?php
//include('Recordatorio.php');

namespace Felican\Models\Recordatorio;
use Felican\Models\Mascota\Mascota;
use \RedBeanPHP\R as R;

class RecordatorioDAO
{

    public function connect()
    {
        connect();

    }

    //Creo deuda y vinculo a mascota
    public function create(Recordatorio $recordatorio , Mascota $mascota)
    {
        R::begin();
        try {
            $recordatorio_bean = R::dispense(['_type' => 'recordatorio',
                'concepto' => $recordatorio->getConcepto(),
                'recordatorio' => $recordatorio->getFecharecordatorio(),
                'cadencia'=> $recordatorio->getCadencia(),
            ]);
            $mascota_bean = R::load( 'mascota', $mascota->getPkey() );
            $mascota_bean->ownRecordatorioList[] = $recordatorio_bean;
            R::store($mascota_bean);

            $recordatorio_saved_bean = end( $mascota_bean->ownRecordatorioList );
            $id = $recordatorio_saved_bean->id;
            $recordatorio_saved_bean->pkey = (int) $id;
            $recordatorio->set_pkey((int) $id);
            R::store($recordatorio_saved_bean);

            R::commit();

        } catch (\Exception $e) {
            R::rollback();
        }

    }

    public function read(Mascota $mascota)
    {
        R::begin();
        try {
            $mascota_bean = R::load( 'mascota', $mascota->getPkey() );
            $recordatorios = [];
            foreach( $mascota_bean->ownRecordatorioList as $r ) {
                $recordatorio = new Recordatorio(
                    $r->concepto,
                    $r->recordatorio,
                    $r->cadencia
                );
                $recordatorio->set_pkey($r->id);
                array_push($recordatorios,$recordatorio);

            }
            $mascota->set_recordatorios($recordatorios);
            R::commit();

        } catch (\Exception $e) {
            R::rollback();
        }
    }

    public function update( Recordatorio $recordatorio)
    {

        R::begin();
        try {
            $recordatorio_bean = R::load( 'recordatorio', $recordatorio->getPkey() );
            $recordatorio_bean->concepto = $recordatorio->getConcepto();
            $recordatorio_bean->recordatorio = $recordatorio->getFecharecordatorio();
            $recordatorio_bean->cadencia = $recordatorio->getCadencia();

            R::store($recordatorio_bean);
            R::commit();



        } catch (\Exception $e) {
            R::rollback();
        }
    }

    public function deleteAllforPet(Mascota $mascota){
        R::begin();
        try {
            $mascota_bean = R::load('mascota',$mascota->getPkey());
            $mascota_bean->xownRecordatorioList = array();
            R::store( $mascota_bean );
            R::commit();

        } catch (\Exception $e) {
            R::rollback();
        }

    }

    public function delete($id){
        R::begin();
        try {

            //Borro la bean de mascoya
            R::trash('recordatorio',$id);
            R::commit();

        } catch (\Exception $e) {
            R::rollback();
        }

    }



}