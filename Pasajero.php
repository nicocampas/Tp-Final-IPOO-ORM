<?php 

include_once("ResponsableV.php");
include_once("Viaje.php");

class Pasajero{
    private $nombre;
    private $apellido;
    private $dni;
    private $telefono;
    private $objViaje;
    private $mensajeOperacion;

    // Metodo Constructor:
    public function __construct(){
        $this->nombre = "";
        $this->apellido = "";
        $this->dni = "";
        $this->telefono = "";
        $this->objViaje = new Viaje();
    }

    public function cargarPasajero($nombre, $apellido, $dni, $telefono, $objViaje){
        $this->setNombre($nombre);
        $this->setApellido($apellido);
        $this->setDni($dni);
        $this->setTelefono($telefono);
        $this->setObjViaje($objViaje);
    }

    //Metodos set:
    public function setNombre($nombre){
        $this->nombre = $nombre;
    }
    public function setApellido($apellido){
        $this->apellido = $apellido;
    }
    public function setDni($dni){
        $this->dni = $dni;
    }
    public function setTelefono($telefono){
        $this->telefono = $telefono;
    }
    public function setObjViaje($objViaje){
        $this->objViaje = $objViaje;
    }
    public function setMensajeOperacion($mensajeOperacion){
        $this->mensajeOperacion = $mensajeOperacion;
    }

    
    //Metodos get:
    public function getNombre(){
        return $this->nombre;
    }
    public function getApellido(){
        return $this->apellido;
    }
    public function getDni(){
        return $this->dni;
    }
    public function getTelefono(){
        return $this->telefono;
    }
    public function getObjViaje(){
        return $this->objViaje;
    }
    public function getMensajeOperacion(){
        return $this->mensajeOperacion;
    }

    // Metodo __toString:
    public function __toString(){
        $black = "\033[0;30m";
        $white = "\033[37m";
        $text = $white."|| ".$black."Pasajero: ".
                $white.$this->getApellido()." ".$this->getNombre().
                $black." DNI: ".$white.$this->getDni().
                $black." Telefono: ".$white.$this->getTelefono().$white." ||\n";
        return $text;
    }

    // Metodos ORM:

    public static function listar($cond = ""){
        $objBaseDatos = new BaseDatos();
        $query = "SELECT * FROM pasajero";

        if($cond != ""){
            $query = $query . " WHERE " . $cond;
        }
        if($objBaseDatos->Iniciar()){
            if($objBaseDatos->Ejecutar($query)){
                $arrayPasajeros = array();
                while($row2 = $objBaseDatos->Registro()){
                    $objPasajero = new Pasajero();
                    $objViaje = new Viaje();
                    $objViaje->setIdviaje($row2["idviaje"]);
                    $objPasajero->cargarPasajero($row2["pnombre"], $row2["papellido"], $row2["pdocumento"], $row2["ptelefono"], $objViaje);
                    array_push($arrayPasajeros,$objPasajero);
                }   
            }else{
                $this->setMensajeOperacion($objBaseDatos->getError());
            }
        }else{
            $this->setMensajeOperacion($objBaseDatos->getError());
        }
        return $arrayPasajeros;
    }

    public function buscar(){
        $retorno = false;
        $objBaseDatos = new BaseDatos();
        $query = "SELECT * FROM pasajero WHERE pdocumento = " . $this->getDni();

        if($objBaseDatos->Iniciar()){
            if($objBaseDatos->Ejecutar($query)){
                if($row2 = $objBaseDatos->Registro()){
                    $objViaje = new Viaje();
                    $objViaje->setIdviaje($row2["idviaje"]);
                    $this->cargarPasajero($row2["pnombre"], $row2["papellido"], $row2["pdocumento"], $row2["ptelefono"], $objViaje);
                    $retorno = true;
                }else{
                    throw new Exception("No se encontró el pasajero con DNI: " . $this->getDni());
                }
            }else{
                $this->setMensajeOperacion($objBaseDatos->getError());
            }
        }else{
            $this->setMensajeOperacion($objBaseDatos->getError());
        }
        return $retorno;
    }

    public function insertar(){
        $objBaseDatos = new BaseDatos();
        if($this->getObjViaje()->getIdviaje() == null){
            throw new Exception("El viaje no está seteado.");
        }
        $query = " INSERT INTO pasajero (pdocumento,pnombre,papellido,ptelefono,idviaje) 
        VALUES ('".$this->getDni()."','".$this->getNombre()."','".$this->getApellido()."','".$this->getTelefono()."','".$this->getObjViaje()->getIdviaje()."')";
        $retorno = false;
        if($objBaseDatos->Iniciar()){
            if($objBaseDatos->Ejecutar($query)){
                $retorno = true;
            }else{
                $this->setMensajeOperacion($objBaseDatos->getError());
            }
        }else{
            $this->setMensajeOperacion($objBaseDatos->getError());
        }
        return $retorno;
    }

    public function modificar(){
        $objBaseDatos = new BaseDatos();
        $query = "UPDATE pasajero SET pnombre = '" . $this->getNombre() . "', papellido = '" . $this->getApellido() . "', ptelefono = '" . $this->getTelefono() ."', idviaje = '".$this->getObjViaje()->getIdviaje(). "' WHERE pdocumento = '" . $this->getDni() . "'";
        $retorno = false;
        if($objBaseDatos->Iniciar()){
            if($objBaseDatos->Ejecutar($query)){
                $retorno = true;
            }else{
                $this->setMensajeOperacion($objBaseDatos->getError());
            }
        }else{
            $this->setMensajeOperacion($objBaseDatos->getError());
        }
        return $retorno;
    }

    public function eliminar(){
        $objBaseDatos = new BaseDatos();
        $query = "DELETE FROM pasajero WHERE pdocumento = '" . $this->getDni() . "'";
        $retorno = false;
        if($objBaseDatos->Iniciar()){
            if($objBaseDatos->Ejecutar($query)){
                $retorno = true;
            }else{
                $this->setMensajeOperacion($objBaseDatos->getError());
            }
        }else{
            $this->setMensajeOperacion($objBaseDatos->getError());
        }
        return $retorno;
    }


}//end class pasajero