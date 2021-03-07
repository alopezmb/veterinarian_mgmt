<?php

namespace Felican\Models\Recordatorio;

class Recordatorio
{
    private $pkey;
    private $concepto;
    private $fecha_recordatorio;
    private $cadencia;

    public function __construct($concepto,$fecha_recordatorio,$cadencia){
        $this->concepto = $concepto;
        $this->fecha_recordatorio = join('-', array_reverse(explode('/', $fecha_recordatorio)));
        $this->cadencia = $cadencia;

    }

    /*
  ** Setters
  */
    public function set_pkey($pkey) {
        $this->pkey = $pkey;
    }

    public function set_concepto($concepto) {
        $this->concepto = $concepto;
    }
    public function set_fecharecordatorio($fecha_recordatorio) {
        $this->fecha_recordatorio =  join('-', array_reverse(explode('/', $fecha_recordatorio)));
    }
    public function set_cadencia($cadencia) {
        $this->cadencia = $cadencia;
    }

    /*
 ** Getters
 */
    public function getPkey() {
        return $this->pkey;
    }
    public function getConcepto() {
       return  $this->concepto;
    }
    public function getFecharecordatorio() {
        return $this->fecha_recordatorio;
    }

    public function getCadencia() {
        return $this->cadencia;
    }
}