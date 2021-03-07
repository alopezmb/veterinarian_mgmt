<?php

namespace Felican\Models\Cliente;


class Cliente
{
    private $pkey;
    private $nombre;
    private $apellidos;
    private $direccion;
    private $cp;
    private $poblacion;
    private $provincia;
    private $tels;
    private $email;
    private $dni;
    private $deuda;
    private $historial_deuda;
    private $mascotas;

    public function __construct($pkey,$nombre,$apellidos,$direccion,$cp,$poblacion,$provincia,$email,$dni,$deuda) {
        $this->pkey = (empty($pkey) ? -1 : $pkey);
        $this->nombre = (empty($nombre) ? "" : $nombre);
        $this->apellidos = (empty($apellidos) ? "" : $apellidos);
        $this->direccion = (empty($direccion) ? "" : $direccion);
        $this->cp = (empty($cp) ? 0 : $cp);
        $this->poblacion = (empty($poblacion) ? "" : $poblacion);
        $this->provincia = (empty($provincia) ? "" : $provincia);
        $this->tels = [];
        $this->email = (empty($email) ? "" : $email);
        $this->dni = (empty($dni) ? "" : $dni);
        $this->deuda = (empty($deuda) ? 0.00 : $deuda);
        $this->historial_deuda = [];
        $this->mascotas = [];


    }

    /*
    ** Setters
    */
    public function set_pkey($pkey) {
        $this->pkey = $pkey;
    }
    public function set_nombre($nombre) {
        $this->nombre = $nombre;
    }
    public function set_apellidos($apellidos) {
        $this->apellidos = $apellidos;
    }
   /* public function set_igualado($igualado) {
        $this->igualado = $igualado;
    } */
    public function set_direccion($direccion) {
        $this->direccion = $direccion;
    }
    public function set_cp($cp) {
        $this->cp = $cp;
    }
    public function set_poblacion($poblacion) {
        $this->poblacion = $poblacion;
    }
    public function set_provincia($provincia) {
        $this->provincia = $provincia;
    }
    public function set_tels($tels) {
        $this->tels = $tels;
    }
    public function set_email($email) {
        $this->email = $email;
    }
    public function set_dni($dni) {
        $this->dni = $dni;
    }
    public function set_deuda($deuda) {
        $this->deuda = $deuda;
    }

    public function set_historialdeuda($historial_deuda) {
        $this->historial_deuda = $historial_deuda;
    }
    public function set_mascotas($mascotas) {
        $this->mascotas = $mascotas;
    }

    /*
    ** Getters
    */

    public function getPkey() {
        return $this->pkey;
    }
    public function getNombre() {
        return $this->nombre;
    }
    public function getApellidos() {
        return $this->apellidos;
    }
   /* public function get_igualado() {
        return $this->igualado;
    } */
    public function getDireccion() {
       return $this->direccion;
    }
    public function getCp() {
        return $this->cp;
    }
    public function getPoblacion() {
        return $this->poblacion;
    }
    public function getProvincia() {
        return $this->provincia;
    }
    public function getTels() {
        return $this->tels;
    }
    public function getEmail() {
        return $this->email;
    }
    public function getDni() {
        return $this->dni;
    }
    public function getDeuda() {
        return $this->deuda;
    }
    public function getHistorialdeuda() {
        return $this->historial_deuda;
    }
    public function getMascotas() {
        return $this->mascotas;
    }


}


