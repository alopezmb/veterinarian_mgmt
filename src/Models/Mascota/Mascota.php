<?php

namespace Felican\Models\Mascota;
use Felican\Models\Recordatorio\Recordatorio;

class Mascota
{
    private $pkey;
    private $nombre;
    private $igualada;
    private $fecha_nacimiento;
    private $especie;
    private $raza;
    private $capa;
    private $pelo;
    private $sexo;
    private $nchip;
    private $historial;
    private $observaciones;
    private $recordatorios;
    private $propietario;

    public function __construct($pkey, $nombre, $igualada, $fecha_nacimiento, $especie, $raza, $capa, $pelo, $sexo,$nchip)
    {
        $this->pkey = $pkey;
        $this->nombre = mb_strtoupper(trim($nombre), 'UTF-8');
        $this->igualada = trim($igualada);
        $this->fecha_nacimiento = join('-',explode('/',  strval($fecha_nacimiento)));
        $this->especie =  mb_strtoupper(trim($especie),'UTF-8');
        $this->raza =  mb_strtoupper(trim($raza),'UTF-8');
        $this->capa =  mb_strtoupper(trim($capa),'UTF-8');
        $this->pelo =  mb_strtoupper(trim($pelo),'UTF-8');
        $this->sexo =  mb_strtoupper(trim($sexo),'UTF-8');
        $this->nchip = strval($nchip);
        $this->historial = "";
        $this->observaciones = "";
        $this->recordatorios = [];
        $this->propietario = null;

    }

    /**
    * Setters
    */
    public function set_pkey($pkey)
    {
        $this->pkey = $pkey;
    }

    public function set_nombre($nombre)
    {
        $this->nombre = mb_strtoupper(trim($nombre),'UTF-8');
    }



    public function set_fechanacimiento($fecha_nacimiento)
    {
        $this->fecha_nacimiento = join('-',explode('/',  strval($fecha_nacimiento)));
    }

    public function set_especie($especie)
    {
        $this->especie = mb_strtoupper(trim($especie),'UTF-8');
    }

    public function set_raza($raza)
    {
        $this->raza = mb_strtoupper(trim($raza),'UTF-8');
    }

    public function set_capa($capa)
    {
        $this->capa = mb_strtoupper(trim($capa),'UTF-8');
    }

    public function set_pelo($pelo)
    {
        $this->pelo = mb_strtoupper(trim($pelo),'UTF-8');
    }

    public function set_sexo($sexo)
    {
        $this->sexo = mb_strtoupper(trim($sexo),'UTF-8');
    }

    public function set_nchip($nchip)
    {
        $this->nchip = strval($nchip);
    }

    public function set_igualada($igualada){
        $this->igualada = $igualada;
    }

    public function set_historial($historial)
    {
        $this->historial = $historial;
    }

    public function set_recordatorios($recordatorios)
    {
        $this->recordatorios = $recordatorios;
    }

    public function set_observaciones($observaciones)
    {
        $this->observaciones = $observaciones;
    }

    public function set_propietario($nombredueno)
    {
        $this->propietario = $nombredueno;
    }

    /*
     * Se llama al crear la mascota, permite definir los recordatorios en función
     * del tipo de mascota que sea.
     * */

    public function getInitialRecordatorios($mascota_type):array{

        $recordatorios = [];

        switch (mb_strtolower(trim($mascota_type),'UTF-8')){

            case 'perro':
                $perro1 = new Recordatorio('Rabia','',365);
                $perro2 = new Recordatorio('KC','',365);
                $perro3 = new Recordatorio('Multivalente','',365);
                $perro4 = new Recordatorio('Heptavalente','',365);
                $perro5 = new Recordatorio('Leishmaniosis','',365);
                $perro6 = new Recordatorio('Desparasitaciones','',90);
                $perro7 = new Recordatorio('Igualas','',90);
                $recordatorios = [$perro1,$perro2,$perro3,$perro4,$perro5,$perro6,$perro7];
                break;

            case 'gato':
                $gato1 = new Recordatorio('Rabia','',365);
                $gato2 = new Recordatorio('Pentavalente','',365);
                $gato3 = new Recordatorio('Desparasitaciones','',90);
                $gato4 = new Recordatorio('Igualas','',90);
                $recordatorios = [$gato1,$gato2,$gato3,$gato4];
                break;

            case 'hurón':
                $huron1 = new Recordatorio('Rabia','',365);
                $huron2 = new Recordatorio('Puppy','',365);
                $huron3 = new Recordatorio('Desparasitaciones','',90);
                $recordatorios = [$huron1,$huron2,$huron3];
                break;

            case 'conejo':
                $conejo1 = new Recordatorio('Mixomatosis','',365);
                $conejo2 = new Recordatorio('Desparasitaciones','',90);
                $recordatorios = [$conejo1,$conejo2];
                break;

            default:
                break;

        }

        return $recordatorios;
    }



        /**
        * Getters
        */

    public function getPkey()
    {
        return $this->pkey;
    }

    public function getNombre()
    {
        return $this->nombre;
    }

    public function getIgualada(){
        return $this->igualada;
    }

    public function getFechanacimiento()
    {

        return join('/',explode('-', $this->fecha_nacimiento ));
    }

    public function getEspecie()
    {
        return $this->especie;
    }

    public function getRaza()
    {
        return $this->raza;
    }

    public function getCapa()
    {
        return $this->capa;
    }

    public function getPelo()
    {
        return $this->pelo;
    }

    public function getSexo()
    {
        return $this->sexo;
    }

    public function getNchip()
    {
        return $this->nchip;
    }

    public function getObservaciones()
    {
        return $this->observaciones;
    }

    public function getHistorial()
    {
        return $this->historial;
    }

    public function getRecordatorios()
    {
        return $this->recordatorios;
    }

    public function getPropietario()
    {
        return $this->propietario;
    }

}