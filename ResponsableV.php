<?php 
class ResponsableV{
    private $nroEmpleado;
    private $nroLicencia;
    private $nombre;
    private $apellido;
    private $colViajes;
    private $mensajeOperacion;

    public function __construct(){
        $this->nroEmpleado = "";
        $this->nroLicencia = "";
        $this->nombre = "";
        $this->apellido = "";
        $this->colViajes = array();
    }

    public function cargarResponsableV($nroEmpleado, $nroLicencia, $nombre, $apellido, $colViajes = []){
        $this->setNroEmpleado($nroEmpleado);
        $this->setNroLicencia($nroLicencia);
        $this->setNombre($nombre);
        $this->setApellido($apellido);
        $this->setColViajes($colViajes);
    }

    //Metodos set:
    public function setNroEmpleado($nroEmpleado){
        $this->nroEmpleado = $nroEmpleado;
    }
    public function setNroLicencia($nroLicencia){
        $this->nroLicencia = $nroLicencia;
    }
    public function setNombre($nombre){
        $this->nombre = $nombre;
    }
    public function setApellido($apellido){
        $this->apellido = $apellido;
    }
    public function setColViajes($colViajes){
        $this->colViajes = $colViajes;
    }
    public function setMensajeOperacion($mensajeOperacion){
        $this->mensajeOperacion = $mensajeOperacion;
    }
    

    //Metodos get:
    public function getNroEmpleado(){
        return $this->nroEmpleado;
    }
    public function getNroLicencia(){
        return $this->nroLicencia;
    }
    public function getNombre(){
        return $this->nombre;
    }
    public function getApellido(){
        return $this->apellido;
    }
    public function getColViajes(){
        return $this->colViajes;
    }
    public function getMensajeOperacion(){
        return $this->mensajeOperacion;
    }
    
    // Metodos ORM

    /** Obtiene los datos de viaje, DEVUELVE UN ARRAY DE OBJETOS VIAJE*/
    public static function listar($cond = ""){
        $objBaseDatos = new BaseDatos();
        $query = "SELECT * FROM responsable ";
        if($cond != ""){
            $query.=" WHERE ".$cond;
        }
        $array = array();
        if($objBaseDatos->Iniciar()){
            if($objBaseDatos->Ejecutar($query)){
                
                while($row2 = $objBaseDatos->Registro()){
                    $objResponsableV = new ResponsableV();
                    $objResponsableV->cargarResponsableV($row2["rnumeroempleado"], $row2["rnumerolicencia"], $row2["rnombre"], $row2["rapellido"]);
                    array_push($array,$objResponsableV);
                }
            }else{
                $this->setMensajeOperacion($objBaseDatos->getError());
            }
        }else{
            throw new Exception("Error al conectar a la base de datos.");
        }
        return $array;
    }

    public function buscar(){
        $retorno = false;
        $objBaseDatos = new BaseDatos();
        $query="SELECT * FROM responsable WHERE rnumeroempleado=".$this->getNroEmpleado();
        if($this->getNroEmpleado()==null){
            throw new Exception("No se puede cargar el responsable desde BD porque el objeto no tiene un id seteado.");
        }
        if($objBaseDatos->Iniciar()){
            if($objBaseDatos->Ejecutar($query)){
                if($row2=$objBaseDatos->Registro()){
                    $this->cargarResponsableV($row2["rnumeroempleado"], $row2["rnumerolicencia"], $row2["rnombre"], $row2["rapellido"]);
                    $retorno = true;
                }
            }else{
                $this->setMensajeOperacion($objBaseDatos->getError());
            }
        }else{
            throw new Exception("Error al conectar a la base de datos.");
        }
        return $retorno;
    }

    public function insertar(){
        $objBaseDatos = new BaseDatos();
        $query = "INSERT INTO responsable(rnumerolicencia,rnombre,rapellido)
        VALUES ('".$this->getNroLicencia()."','".$this->getNombre()."','".$this->getApellido()."')";
        
        if(!$objBaseDatos->Iniciar()){
            throw new Exception("Error al conectar a la base de datos.");
        }
        if($id=$objBaseDatos->devuelveIDInsercion($query)){
            $this->setNroEmpleado($id);
            return true;
        }else{
            $this->setMensajeOperacion($objBaseDatos->getError());
        }
    }
    
    public function modificar(){
        $objBaseDatos = new BaseDatos;
        $query ="UPDATE responsable SET rnumerolicencia='".$this->getNroLicencia()."',rnombre='".$this->getNombre()."',rapellido='".$this->getApellido()."' 
        WHERE rnumeroempleado=".$this->getNroEmpleado();

        if(!$objBaseDatos->Iniciar()){
            throw new Exception("Error al conectar a la base de datos.");
        }
        if($objBaseDatos->Ejecutar($query)){
            return true;
        }else{
            $this->setMensajeOperacion($objBaseDatos->getError());
        }
    }

    public function eliminar(){
        $objBaseDatos = new BaseDatos;
        $query="DELETE FROM responsable WHERE rnumeroempleado=".$this->getNroEmpleado();
        
        if(!$objBaseDatos->Iniciar()){
            throw new Exception("Error al conectar a la base de datos.");
        }
        if($objBaseDatos->Ejecutar($query)){
            return true;
        }else{
            $this->setMensajeOperacion($objBaseDatos->getError());
        }
    }

    // Funcion para recorrer la coleccion de los viajes del Responsable:
    public function mostrarColViajes(){
        $arrayViajes = $this->getColViajes();
        $black = "\033[0;30m";
        $white = "\033[37m";
        $text = $white."\nViajes del Responsable: ";

        if(count($arrayViajes) == 0){
            $text= "No hay viajes cargados para este responsable.";
        }else{
            foreach($arrayViajes as $objViaje){
            $text .= 
                     $black."\nid-viaje: ".$white.$objViaje->getIdviaje().
                     $black." - Destino: ".$white.$objViaje->getDestino().
                     $black." - Disponibilidad: ".$white.count($objViaje->getColPasajeros())."/".$objViaje->getCantMaxPasajeros().
                     $black." - Precio: $".$white.$objViaje->getImporte()."\n";           
        }
        }
        return $text;
    }

    // Funcion __toString:
    public function __toString(){
        $black = "\033[0;30m";
        $white = "\033[37m";
        $text = $white."||".$black."Responsable: ".
                $white.$this->getApellido()." ".$this->getNombre().
                $black." Nro. Empleado: ".$white.$this->getNroEmpleado().
                $black." Nro. Licencia: ".$white.$this->getNroLicencia().$white."||\n".
                $white.(count($this->getColViajes())>0 ? $this->mostrarColViajes() : "");
    
        return $text;
    }

}//end class responsable