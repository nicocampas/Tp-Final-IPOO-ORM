<?php

include_once("BaseDatos.php");
include_once("Empresa.php");
include_once("Pasajero.php");
include_once("ResponsableV.php");
include_once("Viaje.php");


//---------------- Funciones para escribir textos en colores ----------------

function escribirRojo($texto){
    echo "\033[31m" . $texto . "\033[0m";
}
function escribirVerde($texto){
    echo "\033[32m" . $texto . "\033[0m";
}
function escribirGris($texto){
    echo "\033[37m" . $texto . "\033[0m";
}
function escribirNegro($texto) {
    echo "\033[0;30m" . $texto . "\033[0m";
}

//------------------------ Funciones de Viajes -----------------------

function menu_viajes($objEmpresa){
    $opcion = 0;
    while($opcion !=5){
        escribirNegro("\n* * ");
        escribirGris("Módulo: Gestionar Viajes de ".$objEmpresa->getNombre());
        escribirNegro(" * *");
        escribirNegro(
            "\n1. Mostrar Viajes".
            "\n2. Agregar Viaje".
            "\n3. Modificar Viaje".
            "\n4. Eliminar Viaje".
            "\n5. Volver"
        );
        escribirNegro("\nIngrese una opcion: ");
        $opcion = trim(fgets(STDIN));
        switch ($opcion){
            case 1:
                listarViajes($objEmpresa);
                break;
            case 2:
                asistenteAltaViajes($objEmpresa);
                break;
            case 3:
                asistenteModificacionViajes($objEmpresa);
                break;
            case 4:
                asistenteBajaViajes($objEmpresa);
                break;
            case 5:
                break;
            default:
                escribirRojo("Opción no válida. \n");
                break;
        }
    }
}

function listarViajes($objEmpresa){
    $objEmpresa->setColViajes(Viaje::listar("idempresa = " . $objEmpresa->getIdempresa()));
    if(count($objEmpresa->getColViajes()) == 0){
        escribirRojo("*No hay Viajes cargados*\n");
    }else{
        escribirGris("\nListado de viajes: \n");
        echo $objEmpresa->mostrarColViajes();
    }
} 

function seleccionarViaje($objEmpresa){
    $objEmpresa->setColViajes(Viaje::listar("idempresa = " . $objEmpresa->getIdempresa()));
    escribirGris("\nViajes disponibles: \n");
    $arrIDViajes = array();
    foreach ($objEmpresa->getColViajes() as $objViaje){
        $objViaje->setColPasajeros(Pasajero::listar("idviaje = " . $objViaje->getIdviaje()));
        escribirNegro("--------------------------------------------------------");
        escribirGris("\nid-viaje: ".$objViaje->getIdviaje()." - Destino: ".$objViaje->getDestino()." - Disponibilidad: ".count($objViaje->getColPasajeros())."/".$objViaje->getCantMaxPasajeros()." - Precio: $" . $objViaje->getImporte() . "\n");
        escribirNegro("--------------------------------------------------------");
        array_push($arrIDViajes, $objViaje->getIdviaje());
    }
    escribirNegro("\nIngrese el Id-viaje: ");
    $idViaje = trim(fgets(STDIN));
    if(!in_array($idViaje, $arrIDViajes)){
        throw new Exception("ID de viaje no válido o inexistente. \n");
    }
    return $idViaje;
}

function asistenteAltaViajes($objEmpresa){
    escribirGris("\n ---- Agregar Nuevo Viaje ----");
    escribirNegro("\nIngrese el Destino: ");
    $destino = trim(fgets(STDIN));
    escribirNegro("Ingrese la Cant. Máxima de Pasajeros: ");
    $cantMaxPasajeros = trim(fgets(STDIN));

    $responsableId = seleccionarResponsable();
    $objResponsableV = new ResponsableV();
    $objResponsableV->setNroEmpleado($responsableId);
    $objResponsableV->buscar();

    escribirNegro("Ingrese el Importe: ");
    $importe = trim(fgets(STDIN));
    $objViaje = new Viaje();
    $objViaje->setDestino($destino);
    $objViaje->setCantMaxPasajeros($cantMaxPasajeros);
    $objViaje->setObjResponsableV($objResponsableV);
    $objViaje->setObjEmpresa($objEmpresa);
    $objViaje->setImporte($importe);
    if($objViaje->insertar()){
        escribirVerde("Viaje creado con éxito \n");
        // escribirGris("Los datos del viaje son: \n" . $objViaje);
    }
}

function asistenteModificacionViajes($objEmpresa){
    escribirGris("\n---- Modificar Viaje ----\n");
    $idViaje = seleccionarViaje($objEmpresa);
    $objViaje = new Viaje();
    $objViaje->setIdviaje($idViaje);
    $objViaje->buscar();

    escribirNegro("Ingrese el nuevo Destino: ");
    $destino = trim(fgets(STDIN));
    $objViaje->setDestino($destino);

    escribirNegro("Ingrese la nueva Cant. Máxima de Pasajeros: ");
    $cantMaxPasajeros = trim(fgets(STDIN));
    $objViaje->setCantMaxPasajeros($cantMaxPasajeros);

    escribirNegro("Seleccione nuevo Responsable: \n");
    $responsableVID = seleccionarResponsable();
    $objResponsableV = new ResponsableV();
    $objResponsableV->setNroEmpleado($responsableVID);
    $objViaje->setObjResponsableV($objResponsableV);

    escribirNegro("Ingrese el nuevo Importe: ");
    $importe = trim(fgets(STDIN));
    $objViaje->setImporte($importe);

    if($objViaje->modificar()){
        escribirVerde("Viaje Modificado con éxito ");
        $objViaje->buscar();
        // escribirNegro("Los datos del viaje son: \n" . $objViaje);
    }
}

function asistenteBajaViajes($objEmpresa){
    escribirGris("\n---- Eliminar Viaje ----\n");
    $idViaje = seleccionarViaje($objEmpresa);
    $objViaje = new Viaje();
    $objViaje->setIdviaje($idViaje);
    if($objViaje->eliminar()){
        escribirVerde("El viaje ha sido Eliminado con éxito \n");
    }
}

//------------------------ Funciones de Responsable ------------------------

function listarResponsables()
{
    $responsables = ResponsableV::listar();
    foreach ($responsables as $objResponsable) {
        //Cargo a demanda los viajes del responsable.-
        $viajes = Viaje::listar("rnumeroempleado=".$objResponsable->getNroEmpleado());
        $objResponsable->setColViajes($viajes);
        escribirNegro($objResponsable);
    }
}

/**
 * Selecciona un responsable de la lista de responsables disponibles y retorna su id
 * @return int $idResponsable
 */
function seleccionarResponsable(){
    $responsables = ResponsableV::listar();
    $responsables_id = array();
    foreach ($responsables as $objResponsable) {
        escribirNegro($objResponsable);
        array_push($responsables_id, $objResponsable->getNroEmpleado());
    }
    escribirNegro("\nSeleccione el Nro-Empleado del Responsable: ");
    $idResponsable = trim(fgets(STDIN));
    if (!in_array($idResponsable, $responsables_id)) {
        throw new Exception("El responsable no existe");
    }
    return $idResponsable;
}

//Menu alta de responsables
function asistenteAltaResponsable(){
    escribirGris("\n---- Agregar Responsable ----");
    escribirNegro("\nIngrese el Nombre: ");
    $nombre = trim(fgets(STDIN));
    escribirNegro("Ingrese el Apellido: ");
    $apellido = trim(fgets(STDIN));
    escribirNegro("Ingrese Nro de Licencia : ");
    $licencia = trim(fgets(STDIN));
    $objResponsable = new ResponsableV();
    $objResponsable->setNombre($nombre);
    $objResponsable->setApellido($apellido);
    $objResponsable->setNroLicencia($licencia);
    if ($objResponsable->insertar()) {
        escribirVerde("El Responsable ha sido creado con éxito \n");
        escribirGris("Los datos del responsable son: \n" . $objResponsable);
    }
}
//Menu modificacion de responsables
function asistenteModificacionResponsable(){
    escribirGris("\n---- Modificar Responsable ----\n");
    $idResponsable = seleccionarResponsable();
    $objResponsable = new ResponsableV();
    $objResponsable->setNroEmpleado($idResponsable);
    $objResponsable->buscar();
    escribirNegro("Ingrese el Nuevo Nombre del Responsable: ");
    $nombre = trim(fgets(STDIN));
    escribirNegro("Ingrese el Nuevo Apellido del Responsable: ");
    $apellido = trim(fgets(STDIN));
    escribirNegro("Ingrese el nuevo Nro-Licencia Del Responsable: ");
    $licencia = trim(fgets(STDIN));
    $objResponsable->setNombre($nombre);
    $objResponsable->setApellido($apellido);
    $objResponsable->setNroLicencia($licencia);
    if ($objResponsable->modificar()) {
        escribirVerde("El Responsable ha sido Modificado con éxito \n");
        escribirGris("Los datos del responsable son: \n" . $objResponsable);
    }
}

//Menu baja de responsables
function asistenteBajaResponsable(){
    escribirGris("\n---- Eliminar Responsable ----\n");
    $idResponsable = seleccionarResponsable();
    $objResponsable = new ResponsableV();
    $objResponsable->setNroEmpleado($idResponsable);
    //Verificar que no tenga viajes asignados
    $viajes = Viaje::listar("rnumeroempleado=".$idResponsable);
    if (count($viajes) > 0) {
        throw new Exception("El responsable tiene viajes asignados, por lo que no se lo puede eliminar.");
    }
    if ($objResponsable->eliminar()) {
        escribirVerde("El Responsable ha sido borrado con éxito \n");
    }
}

function menu_Responsable(){
    $opcion = 0;
    while($opcion !=5){
        escribirNegro("\n* * ");
        escribirGris("Módulo: Gestionar Responsables");
        escribirNegro(" * *");
        escribirNegro(
                    "\n1. Mostrar Responsables".
                    "\n2. Agregar Responsable".
                    "\n3. Modificar Responsable".
                    "\n4. Eliminar Responsable".
                    "\n5. Volver"
        );
        escribirNegro("\nIngrese una opcion: ");
        $opcion = trim(fgets(STDIN));
        switch ($opcion){
            case 1:
                listarResponsables();
                break;
            case 2:
                asistenteAltaResponsable();
                break;
            case 3:
                asistenteModificacionResponsable();
                break;
            case 4:
                asistenteBajaResponsable();
                break;
            case 5:
                break;
            default:
                escribirRojo("Opción no válida. \n");
                break;
        }
    }
}
//------------------------ Funciones de Pasajero ------------------------

function listarPasajerosPorViaje($objEmpresa){
    $idViaje = seleccionarViaje($objEmpresa);
    $pasajeros = Pasajero::listar("idviaje = $idViaje");
    if (count($pasajeros) == 0) {
        escribirRojo("\nNo hay pasajeros para este viaje\n");
    } else {
        escribirGris("\n---- Pasajeros del viaje: ----\n");
        foreach ($pasajeros as $objPasajero) {
            escribirGris($objPasajero);
        }
    }
}

function verificarSiPoseeViajeAsignado($dni){
    $retorno = false;
    $p = Pasajero::listar("pdocumento = $dni");
    if (count($p) > 0) {
        $retorno = true;
    }
    return $retorno;
}

//Menu alta de pasajeros
function asistenteAltaPasajero($objEmpresa){
    escribirGris("\n---- Agregar Pasajero ----");
    escribirNegro("\nIngrese el DNI: ");
    $dni = trim(fgets(STDIN));
    if (verificarSiPoseeViajeAsignado($dni)) {
        throw new Exception("El pasajero ya posee un viaje asignado, utilice la opción de modificar pasajero");
    }
    $idViaje = seleccionarViaje($objEmpresa);
    escribirNegro("Ingrese el Nombre: ");
    $nombre = trim(fgets(STDIN));
    escribirNegro("Ingrese el Apellido: ");
    $apellido = trim(fgets(STDIN));
    escribirNegro("Ingrese el Telefono: ");
    $telefono = trim(fgets(STDIN));

    $objViaje = new Viaje();
    $objViaje->setIdviaje($idViaje);
    $objViaje->buscar();

    $objPasajero = new Pasajero();
    $objPasajero->cargarPasajero($nombre, $apellido, $dni, $telefono, $objViaje);
    //Verificar si el viaje no va lleno y agregarlo al arreglo.
    $objViaje->agregarPasajeroAlArray($objPasajero);

    $objPasajero->setObjViaje($objViaje);
    if ($objPasajero->insertar()) {
        escribirVerde("Pasajero agregado con éxito \n");
        escribirGris("Los datos del pasajero son: \n" . $objPasajero);
    }
}

//Menu modificacion de pasajeros
function asistenteModificacionPasajero($objEmpresa){
    escribirGris("\n---- Modificar Pasajero ----");
    escribirNegro("\nIngrese el Nuevo DNI: ");
    $dni = trim(fgets(STDIN));
    $objPasajero = new Pasajero();
    $objPasajero->setDni($dni);
    $objPasajero->buscar();

    escribirNegro("Ingrese el Nuevo Nombre: ");
    $nombre = trim(fgets(STDIN));
    escribirNegro("Ingrese el Nuevo Apellido: ");
    $apellido = trim(fgets(STDIN));
    escribirNegro("Ingrese el Nuevo Telefono : ");
    $telefono = trim(fgets(STDIN));
    $objPasajero->setNombre($nombre);
    $objPasajero->setApellido($apellido);
    $objPasajero->setTelefono($telefono);

    //Editar numero de viaje
    $idViaje = seleccionarViaje($objEmpresa);
    $objViaje = new Viaje();
    $objViaje->setIdviaje($idViaje);
    $objViaje->buscar();
    if(count($objViaje->getColPasajeros()) == $objViaje->getCantMaxPasajeros()){
        throw new Exception("El viaje seleccionado ya se encuentra lleno, seleccione otro viaje");
    }
    $objPasajero->setObjViaje($objViaje);

    if($objPasajero->modificar()){
        escribirVerde("Pasajero Modificado con éxito \n");
        escribirGris("Los datos del pasajero son: \n" . $objPasajero);
    }
}

//Menu baja de pasajeros
function asistenteBajaPasajero(){
    escribirGris("\n---- Eliminar Pasajero ----");
    escribirNegro("\nIngrese el DNI del pasajero a Eliminar: ");
    $dni = trim(fgets(STDIN));
    $objPasajero = new Pasajero();
    $objPasajero->setDni($dni);
    $objPasajero->buscar();
    if ($objPasajero->eliminar()) {
        escribirVerde("Se ha Eliminado el Pasajero con éxito \n");
    }
}

function menu_pasajeros($objEmpresa){
    $opcion = 0;
    while($opcion !=5){
        escribirNegro("\n* * ");
        escribirGris("Módulo: Gestionar Pasajeros de ".$objEmpresa->getNombre());
        escribirNegro(" * *");
        escribirNegro(
            "\n1. Mostrar Pasajeros por viaje".
            "\n2. Agregar Pasajero a un viaje".
            "\n3. Modificar Pasajero".
            "\n4. Eliminar Pasajero".
            "\n5. Volver"
        );
        escribirNegro("Ingrese una opcion: ");
        $opcion = trim(fgets(STDIN));
        switch ($opcion){
            case 1:
                listarPasajerosPorViaje($objEmpresa);
                break;
            case 2:
                asistenteAltaPasajero($objEmpresa);
                break;
            case 3:
                asistenteModificacionPasajero($objEmpresa);
                break;
            case 4:
                asistenteBajaPasajero();
                break;
            case 5:
                break;
            default:
                escribirRojo("Opción no válida. \n");
                break;
        }
    }
}

//------------------------ Funciones de Empresa ------------------------

//ListarEmpresas
function listarEmpresas(){
    escribirGris("\nLista de Empresas: ");
    $empresas = Empresa::listar();
    foreach ($empresas as $objEmpresa) {
        $objEmpresa->setColViajes(Viaje::listar("idempresa = " . $objEmpresa->getIdempresa()));
        escribirGris($objEmpresa);
    }
    return $empresas;
}

//Seleccionar objeto empresa por id y retornarlo
function seleccionarEmpresa(){
    $empresas = listarEmpresas();
    escribirNegro("\nIngrese el Id-Empresa: ");
    $idEmpresa = trim(fgets(STDIN));
    $retorno = null;
    $i = 0;
    while ($retorno == null && $i < count($empresas)) {
        $objEmpresa = $empresas[$i];
        if ($objEmpresa->getIdempresa() == $idEmpresa) {
            $retorno = $objEmpresa;
        }
        $i++;
    }
    if ($retorno == null) {
        throw new Exception("No se encontró la empresa con id $idEmpresa \n");
    }
    return $retorno;
}

//Menu alta de empresas
function asistenteAltaEmpresa()
{
    escribirGris("\n ---- Agregar Nueva Emprea ----");
    escribirNegro("\nIngrese el Nombre: ");
    $nombre = trim(fgets(STDIN));
    escribirNegro("Ingrese la Dirección: ");
    $direccion = trim(fgets(STDIN));
    $objEmpresa = new Empresa();
    $objEmpresa->setNombre($nombre);
    $objEmpresa->setDireccion($direccion);
    if ($objEmpresa->insertar()) {
        escribirVerde("Empresa guardada con éxito \n");
        // escribirNegro("DATOS: \n" . $objEmpresa . "\n");
    }
    return $objEmpresa;
}

//Menu modificacion de empresas
function asistenteModificacionEmpresa(){
    escribirGris("\n ---- Modificar Empresa ----");
    $objEmpresa = seleccionarEmpresa();
    escribirNegro(" ---- Seleccionaste la empresa: ");
    escribirGris($objEmpresa->getNombre());
    escribirNegro(" ----\n");
    escribirNegro("Ingrese el Nuevo Nombre: ");
    $nombre = trim(fgets(STDIN));
    escribirNegro("Ingrese la Nueva Dirección: ");
    $direccion = trim(fgets(STDIN));
    $objEmpresa->setNombre($nombre);
    $objEmpresa->setDireccion($direccion);
    if ($objEmpresa->modificar()) {
        escribirVerde("Empresa modificada con éxito \n");
        // escribirNegro("DATOS: \n" . $objEmpresa . "\n");
    }
    return $objEmpresa;
}

//Menu baja de empresas
function asistenteBajaEmpresa(){
    escribirGris("\n ---- Eliminar Empresa ----");
    $objEmpresa = seleccionarEmpresa();
    escribirNegro(" ---- Seleccionaste la empresa: ");
    escribirGris($objEmpresa->getNombre());
    escribirNegro(" ----\n");
    if (true) {
        try {
            if ($objEmpresa->eliminar()) {
                escribirVerde("La empresa ha sido Eliminada con éxito \n");
            }
        } catch (Exception $e) {
            escribirRojo("La empresa no se ha podido Eliminar ya que tiene viajes asignados\n");
        }
    } else {
        escribirRojo("\n La empresa no se ha podido Eliminar \n");
    }
}

function menu_empresa($objEmpresa){
    $opcion = 0;
    while($opcion !=6){
        escribirNegro("\n* * ");
        escribirGris("Módulo: ");
        escribirGris("Gestionar Empresas");
        escribirNegro(" * *");
        escribirNegro(
            "\n1. Seleccionar otra Empresa para operar".
            "\n2. Ver todas las Empresas".
            "\n3. Agregar Empresa".
            "\n4. Modificar Empresa".
            "\n5. Eliminar Empresa".
            "\n6. Volver"
        );
        escribirGris("Ingrese una opcion: ");
        $opcion = trim(fgets(STDIN));
        switch ($opcion){
            case 1:
                $objEmpresa = seleccionarEmpresa($objEmpresa);
                break;
            case 2:
                listarEmpresas();
                break;
            case 3:
                asistenteAltaEmpresa();
                break;
            case 4:
                $modificada = asistenteModificacionEmpresa();
                if ($modificada->getIdempresa() == $objEmpresa->getIdempresa()) {
                    $objEmpresa = $modificada;
                }
                break;
            case 5:
                asistenteBajaEmpresa();
                break;
            case 6:
                break;
            default:
                escribirRojo("Opción no válida. \n");
                break;
        }
        return $objEmpresa;
    }
}

//------------------------ FUNCION PRINCIPAL------------------------
//Menu principal
function menuPrincipal($objEmpresa){
    escribirNegro("\n- - - - - > ");
    escribirGris($objEmpresa->getNombre());
    escribirNegro(" < - - - - -");
    escribirNegro(
            "\n1. Gestionar Empresa".
            "\n2. Gestionar Viajes".
            "\n3. Gestionar Pasajeros".
            "\n4. Gestionar Responsables".
            "\n5. Salir \n"
    );
}

function testViaje()
{
    $objEmpresa = new Empresa();
    $objEmpresa->setIdempresa(1);
    $objEmpresa->buscar();
    $opcion = 0;

    while ($opcion != 5) {
        try {
            menuPrincipal($objEmpresa);
            escribirNegro("Ingrese una opción: ");
            $opcion = trim(fgets(STDIN));
            switch ($opcion) {
                case 1:
                    $objEmpresa = menu_empresa($objEmpresa);
                    break;
                case 2:
                    menu_viajes($objEmpresa);
                    break;
                case 3:
                    menu_pasajeros($objEmpresa);
                    break;
                case 4:
                    menu_Responsable();
                    break;
                case 5:
                    break;
                default:
                    escribirRojo("Opción no válida. \n");
                    break;
            }
        } catch (Exception $e) {
            ("\n\n" . $e->getMessage() . "\n\n");
        }
    }
}


testViaje();


