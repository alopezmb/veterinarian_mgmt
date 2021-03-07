<?php

namespace Felican\Models\Deuda;

class Deuda
{

    private $pkey;
    private $fecha;
    private $concepto;

    public function __construct($fecha,$concepto) {
        $this->pkey = null;
        $this->fecha = $fecha;
        $this->concepto = $concepto;
    }


    /*
   ** Setters
   */
    public function set_pkey($pkey) {
        $this->pkey = $pkey;
    }

    public function set_fecha($fecha) {
        $this->fecha = $fecha;
    }
    public function set_concepto($concepto) {
        $this->concepto = $concepto;
    }

    /*
 ** Getters
 */
    public function getFecha() {
        return $this->fecha;
    }
    public function getConcepto() {
        return $this->concepto;
    }
    public function getPkey() {
        return $this->pkey;
    }
}