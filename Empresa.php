<?php 
class Empresa{
    private $idempresa;
    private $nombre;
    private $direccion;
    private $colViajes;
    private $mensajeOperacion;

    // Metodo Constructor:
    public function __construct(){
        $this->idempresa = "";
        $this->nombre = "";
        $this->direccion = "";
        $this->colViajes = array();
    }
    
    public function cargarEmpresa($idempresa, $nombre, $direccion){
        $this->setIdempresa($idempresa);
        $this->setNombre($nombre);
        $this->setDireccion($direccion);
    }

    // Metodos set:
    public function setIdempresa($idempresa){
        $this->idempresa = $idempresa;
    }
    public function setNombre($nombre){
        $this->nombre = $nombre;
    }
    public function setDireccion($direccion){
        $this->direccion = $direccion;
    }
    public function setColViajes($colViajes){
        $this->colViajes = $colViajes;
    }
    public function setMensajeOperacion($mensajeOperacion){
        $this->mensajeOperacion = $mensajeOperacion;
    }

    // Metodos get:
    public function getIdempresa(){
        return $this->idempresa;
    }
    public function getNombre(){
        return $this->nombre;
    }
    public function getDireccion(){
        return $this->direccion;
    }
    public function getColViajes(){
        return $this->colViajes;
    }    
    public function getMensajeOperacion(){
        return $this->mensajeOperacion;
    }

    // Metodos ORM

    /** RETORNA UN ARRAY DE OBJETOS EMPRESA */
    public static function listar($cond = ""){
        $objBaseDatos = new BaseDatos();
        $query="SELECT * FROM empresa";
        if ($cond!=""){
            $query.=" WHERE ".$cond;
        }
        $arrayEmpresas = array();
        if($objBaseDatos->Iniciar()){
            if($objBaseDatos->Ejecutar($query)){
                while($row2=$objBaseDatos->Registro()){
                    $objEmpresa = new Empresa();
                    $objEmpresa->cargarEmpresa($row2['idempresa'],$row2['enombre'],$row2['edireccion']);
                    array_push($arrayEmpresas,$objEmpresa);
                }
            }  
        }else{
            $this->setMensajeOperacion($objBaseDatos->getError());
        }
        return $arrayEmpresas;
    }

    public function buscar(){
        $objBaseDatos = new BaseDatos();
        $query="SELECT * FROM empresa WHERE idempresa=".$this->getIdempresa();

        if($this->getIdempresa() == null){
            throw new Exception("No se puede cargar la empresa desde BD porque el objeto no tiene un id seteado.");
        }
        if($objBaseDatos->Iniciar()){
            if($objBaseDatos->Ejecutar($query)){
            $row2=$objBaseDatos->Registro();
            $this->setNombre($row2["enombre"]);
            $this->setDireccion($row2["edireccion"]);   
            }
        }else{
            $this->setMensajeOperacion($objBaseDatos->getError());
        }
    }

    public function insertar(){
        $objBaseDatos = new BaseDatos();
        $query =" INSERT INTO empresa (enombre,edireccion)
        VALUES ('".$this->getNombre()."','".$this->getDireccion()."')";
        $retorno = false;
        if($objBaseDatos->Iniciar()){
            if($id = $objBaseDatos->devuelveIDInsercion($query)){
                $this->setIdempresa($id);
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
        $query = "UPDATE empresa SET enombre='".$this->getNombre()."', edireccion='".$this->getDireccion()."' WHERE idempresa=".$this->getIdempresa();
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
        $query = "DELETE FROM empresa WHERE idempresa=".$this->getIdempresa();
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

    public function mostrarColViajes(){
        $black = "\033[0;30m";
        $white = "\033[37m";
        $arrayViajes = $this->getColViajes();
        $text = "";
        foreach($arrayViajes as $objViaje){
            $text .= $black."\nId-viaje: ".$white.$objViaje->getIdviaje().
                     $black." - Destino: ".$white.$objViaje->getDestino().
                     $black." - Disponibilidad: ".$white.count($objViaje->getColPasajeros())."/".$objViaje->getCantMaxPasajeros().
                     $black." - Precio: $".$white.$objViaje->getImporte().
                     $white.$objViaje->getObjResponsableV();
        }
        return $text;
    }

    public function __toString() {
        $black = "\033[0;30m";
        $white = "\033[37m";
        $reset = "\033[0m";

        $text = $white."\n------------------------------------------------------------------------------------------------------------------------------------------------".
                $white."\n             Datos Empresa:".
                $black."\nId: ".$white.$this->getIdempresa().
                $black."\nEmpresa: ".$white.$this->getNombre().
                $black."\nDireccion: ".$white.$this->getDireccion().
                $white."\n\n           Viajes Disponibles:\n".
                $reset.$this->mostrarColViajes().
                $white."\n------------------------------------------------------------------------------------------------------------------------------------------------";
        return $text;
    }

}//end class empresa 