<?php 
class Viaje{
    private $idviaje;
    private $destino;
    private $cantMaxPasajeros;
    private $objEmpresa;
    private $objResponsableV;
    private $importe;
    private $colPasajeros;
    private $mensajeOperacion;

    // Metodo Constructor:
    public function __construct(){
        $this->idviaje = "";
        $this->destino = "";
        $this->cantMaxPasajeros = 0;
        $this->objEmpresa = new Empresa();
        $this->objResponsableV = new ResponsableV();
        $this->importe = 0;
        $this->colPasajeros = array();
    }

    public function cargarViaje($idviaje, $destino, $cantMaxPasajeros, $objEmpresa, $objResponsableV, $importe){
        $this->setIdviaje($idviaje);
        $this->setDestino($destino);
        $this->setCantMaxPasajeros($cantMaxPasajeros);
        $this->setObjEmpresa($objEmpresa);
        $this->setObjResponsableV($objResponsableV);
        $this->setImporte($importe);
    }

    // Metodos set:
    public function setIdviaje($idviaje){
        $this->idviaje = $idviaje;
    }
    public function setDestino($destino){
        $this->destino = $destino;
    }
    public function setCantMaxPasajeros($cantMaxPasajeros){
        $this->cantMaxPasajeros = $cantMaxPasajeros;
    }
    public function setObjEmpresa($objEmpresa){
        $this->objEmpresa = $objEmpresa;
    }
    public function setObjResponsableV($objResponsableV){
        $this->objResponsableV = $objResponsableV;
    }
    public function setImporte($importe){
        $this->importe = $importe;
    }
    public function setColPasajeros($colPasajeros){
        $this->colPasajeros = $colPasajeros;
    }
    public function setMensajeOperacion($mensajeOperacion){
        $this->mensajeOperacion = $mensajeOperacion;
    }

    // Metodos get:
    public function getIdviaje(){
        return $this->idviaje;
    }
    public function getDestino(){
        return $this->destino;
    }
    public function getCantMaxPasajeros(){
        return $this->cantMaxPasajeros;
    }
    public function getObjEmpresa(){
        return $this->objEmpresa;
    }
    public function getObjResponsableV(){
        return $this->objResponsableV;
    }
    public function getImporte(){
        return $this->importe;
    }
    public function getColPasajeros(){
        return $this->colPasajeros;
    }
    public function getMensajeOperacion(){
        return $this->mensajeOperacion;
    }

    // Metodos ORM

    /**
     * Agrega un objeto pasajero a la coleccion de objetos pasajero del viaje. PUEDE O NO COINCIDIR CON LA DB.
     */
    public function agregarPasajeroAlArray($arrayPasajeros)
    {
        if (count($this->getColPasajeros()) < $this->getCantMaxPasajeros()) {
            array_push($this->colPasajeros, $arrayPasajeros);
        } else {
            throw new Exception("No hay más lugar en el viaje.");
        }
    }

    /** Obtiene los datos de viaje, DEVUELVE UN ARRAY DE OBJETOS VIAJE*/
    public static function listar($cond = ""){
        $objBaseDatos = new BaseDatos();
        $query = "SELECT * FROM viaje";
        if($cond != ""){
            $query.=" WHERE " . $cond;
        }
        $arrayViajes = array();
        if($objBaseDatos->Iniciar()){
            if($objBaseDatos->Ejecutar($query)){
                while($row2 = $objBaseDatos->Registro()){
                
                    $objEmpresa = new Empresa(); 
                    $objEmpresa->setIdempresa($row2["idempresa"]);
                    $objEmpresa->buscar();

                    $objResponsableV = new ResponsableV();
                    $objResponsableV->setNroEmpleado($row2["rnumeroempleado"]);
                    $objResponsableV->buscar();

                    $objViaje = new Viaje();
                    $objViaje->setIdviaje($row2["idviaje"]);
                    $objViaje->setColPasajeros(Pasajero::listar("idviaje = " . $row2["idviaje"]));
                    $objViaje->cargarViaje($row2["idviaje"], $row2["vdestino"], $row2["vcantmaxpasajeros"], $objEmpresa, $objResponsableV, $row2["vimporte"]);

                    array_push($arrayViajes, $objViaje);
                }
            }else{
                $this->setMensajeOperacion($objBaseDatos->getError());
            }
        }else{
            $this->setMensajeOperacion($objBaseDatos->getError());
        }
        return $arrayViajes;
    }

    public function buscar(){
        $retorno = false;
        $objBaseDatos = new BaseDatos();
        $query ="SELECT * FROM viaje WHERE idviaje = ".$this->getIdviaje(); 
        if($objBaseDatos->Iniciar()){
            if($objBaseDatos->Ejecutar($query)){
                if($row2=$objBaseDatos->Registro()){

                    $objEmpresa = new Empresa();
                    $objEmpresa->setIdempresa($row2["idempresa"]);
                    $objEmpresa->buscar();

                    $objResponsableV = new ResponsableV();
                    $objResponsableV->setNroEmpleado($row2["rnumeroempleado"]);
                    $objResponsableV->buscar();
                    
                    $pasajeros = Pasajero::listar("idviaje = " . $this->getIdviaje());
                    $this->setColPasajeros($pasajeros);
                    $this->cargarViaje($row2["idviaje"], $row2["vdestino"], $row2["vcantmaxpasajeros"], $objEmpresa, $objResponsableV, $row2["vimporte"]);
                    $retorno = true;
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
        $query = "INSERT INTO viaje (vdestino, vcantmaxpasajeros,idempresa, rnumeroempleado, vimporte) 
        VALUES ('" . $this->getDestino() . "','" . $this->getCantMaxPasajeros() . "','" . $this->getObjEmpresa()->getIdempresa()
         . "','" . $this->getObjResponsableV()->getNroEmpleado() . "','" . $this->getImporte() . "')";
        if($objBaseDatos->Iniciar()){
            if($id=$objBaseDatos->devuelveIDInsercion($query)){
                $this->setIdviaje($id);
                return true;
            }else{
                $this->setMensajeOperacion($objBaseDatos->getError());
            }
        }else{
            $this->setMensajeOperacion($objBaseDatos->getError());
        }
    }

    public function modificar(){
        $objBaseDatos = new BaseDatos();
        $query = "UPDATE viaje SET vdestino = '" . $this->getDestino() . "', vcantmaxpasajeros = '" . $this->getCantMaxPasajeros() . "', idempresa = '" . $this->getObjEmpresa()->getIdempresa() . "', rnumeroempleado = '" . $this->getObjResponsableV()->getNroEmpleado() . "', vimporte = '" . $this->getImporte() . "' WHERE idviaje = '" . $this->getIdviaje() . "'";
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
        $objBaseDatos = new BaseDatos;
        if($this->getIdviaje() == null){
            throw new Exception("Error al eliminar un viaje en la base de datos. El id del viaje es nulo.");
        }
        $query = "DELETE FROM viaje WHERE idviaje = '" . $this->getIdviaje() . "'";
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

    // Funcion para recorrer la coleccion de los pasajeros y mostrarlos:
    public function mostrarColPasajeros(){
        $pasajeros = $this->getColPasajeros();
        $text = "";
        if(count($pasajeros) == 0){
            $text= "No hay pasajeros cargados en este viaje.";
        }else{
            for ($i=0; $i < count($pasajeros); $i++) { 
                $text = $text."\n".$pasajeros[$i];
            }
        }
        return $text;
    }

    // Funcion __toString:
    public function __toString(){
        $text ="ID Viaje: ".$this->getIdviaje().
               "\nDestino: ".$this->getDestino().
               "\nCant. Maxima de Pasajeros: ".$this->getCantMaxPasajeros().
               "\nEmpresa: ".$this->getObjEmpresa().
               "\nResponsable Viaje: ".$this->getObjResponsableV().
               "\nImporte: $ ".$this->getImporte().
               "\nPasajeros: ".$this->mostrarColPasajeros()."\n";
        return $text;
    }

}//end class viaje
