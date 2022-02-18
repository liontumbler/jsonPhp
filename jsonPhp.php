<?php
class DbJson
{
    private $db;
    private $data;

    private $fileDb = "db/db.json";

    //constructor de multiples constructores
    function __construct()
	{
		//obtengo un array con los parámetros enviados a la función
		$params = func_get_args();
		//saco el número de parámetros que estoy recibiendo
		$num_params = func_num_args();
		//cada constructor de un número dado de parámtros tendrá un nombre de función
		//atendiendo al siguiente modelo __construct1() __construct2()...
		$funcion_constructor ='__construct'.$num_params;
		//compruebo si hay un constructor con ese número de parámetros
		if (method_exists($this,$funcion_constructor)) 
			//si existía esa función, la invoco, reenviando los parámetros que recibí en el constructor original
			call_user_func_array(array($this,$funcion_constructor),$params);
	}

    /**
     * si instancia el objeto con un valor crea un archivo en la direccion solicitada
     * con formato json. 
     * si no tiene valor al instanciar la clase hace una coneccion a la DB.json ya creada por la libreria
     *  
     */
    function __construct0()
	{
		$this->conectar();
	}
	
    function __construct1($direccion)
	{
        $this->fileDb = "$direccion.json";
        file_put_contents($this->fileDb, "");
		$this->conectar();
	}

    private function conectar()
    {
        $this->data = file_get_contents($this->fileDb);
        $this->db = json_decode($this->data, true);
    }

    public function CreateOnlyTables($tablas = array())
    {
        $numNewTablas = count($tablas);
        if ($numNewTablas > 0) {

            $actualizar = 0;
            for ($i=0; $i < $numNewTablas; $i++) {   

                $repetido = 0;
                foreach ($this->db as $clave => $valor) {
                    //echo "$clave <==> $valor | ";
                    if($clave == $tablas[$i])
                        $repetido++;
                }
                
                if($repetido == 0){
                    $this->db[$tablas[$i]] = array();
                    $actualizar = 1;
                }else {
                    echo 'ya hay una tablas con el nombre '.$tablas[$i];
                    echo '<br>';
                }
            }

            if($actualizar)
                //actualiza la db sin borrar los datos que ya tiene
                file_put_contents($this->fileDb, json_encode($this->db));

            return $actualizar;
            
        }else {
            echo 'CrearTablas tiene un array vacio';
            return 0;
        }
    }

    public function CreateFieldsTable($tabla, $campos = array())
    {
        $crear = 0;
        //si esta la tabla en la DB
        if(isset($this->db[$tabla])){

            //hacer una copia de un array
            $arrayObject = new ArrayObject($this->db[$tabla]);
            $newArray = $arrayObject->getArrayCopy();

            $newIndex = count($this->db[$tabla]);

            //valida el id
            $validarTabla = 0;
            if(!isset($this->db[$tabla][0]['id']))
                $newArray[0]['id'] = 0;
            else{
                $validarTabla = 1; 
                //ultimo id de la tabla
                $ultimoId = $this->db[$tabla][($newIndex - 1)]['id'];
                //creando posicion nueva
                $newArray[$newIndex]['id'] = (int)$ultimoId + 1;
            }

            foreach ($campos as $clave => $valor) {

                $valorCampo;
                if(is_numeric($valor))
                    $valorCampo = (float)$valor;
                else
                    $valorCampo = $valor;

                if ($validarTabla) {                     
                    if(isset($this->db[$tabla][0][$clave])){
                        
                        if(is_numeric($this->db[$tabla][0][$clave]) && is_numeric($valorCampo))
                            $crear = 1;
                        elseif(is_string($this->db[$tabla][0][$clave]) && is_string($valorCampo))
                            $crear = 1;
                        else
                            echo 'el valor `'.$valorCampo.'` que se mando del campo `'.$clave.'` no corresponde al formato de la tabla '.$tabla;

                        $newArray[$newIndex][$clave] = $valorCampo;

                    }else
                        echo 'se inserto el resto menos el campo `'.$clave.'`, porque no existe en la tabla '.$tabla;

                }else{
                    $crear = 1;
                    $newArray[0][$clave] = $valorCampo;
                }
            }

            if($crear){
                $this->db[$tabla] = $newArray;
                //actualiza la db sin borrar los datos que ya tiene
                file_put_contents($this->fileDb, json_encode($this->db));
            }
                
            return $crear;
        }
        //no esta en la DB
        else{
            echo 'La tabla `'.$tabla.'` no existe, se crea la tabla y se insertan datos: '.implode(", ",$campos);
            $this->db[$tabla] = array();
            return $this->CreateFieldsTable($tabla, $campos);
        }
    }

    public function UpdateFieldsTableId($id, $tabla, $campos = array())
    {
        $actualizar = 0;
        for ($i=0; $i < count($this->db[$tabla]); $i++) { 
                
            if($this->db[$tabla][$i]['id'] == $id){
                foreach ($this->db[$tabla][$i] as $clave => $valor) {
                    if ($clave != 'id') {
                        foreach ($campos as $campo => $v) {
                            $actualizar = 1;
                            if($clave == $campo)
                                $this->db[$tabla][$i][$clave] = $v;
                        }
                    }
                }
            }
        }

        if ($actualizar) 
            //actualiza la db sin borrar los datos que ya tiene
            file_put_contents($this->fileDb, json_encode($this->db));
        
        return $actualizar;
    }

    public function DeleteFieldsTableId($id)
    {
        $elimino = 0;
        for ($i=0; $i < count($this->db[$tabla]); $i++) { 
            
            if($this->db[$tabla][$i]['id'] == $id){
                $elimino = 1;
                unset($this->db[$tabla][$i]);
            }
        }

        if ($elimino) 
            file_put_contents($this->fileDb, json_encode($this->db));
        
        return $elimino;
    }

    public function All($tabla = null)
    {
        //var_dump($this->db);
        if($this->db == null)
            return 'la DB esta vacia, se puede llenar manualmente en el archivo o crear desde aca con el metodo CrearTablas(Array)';
        else{
            if($tabla == null)
                return $this->db;
            else
                return $this->db[$tabla];
        }
    }

    public function GetTableId($id, $tabla)
    {
        $newTabla = array();
        for ($i=0; $i < count($this->db[$tabla]); $i++) { 
            if($this->db[$tabla][$i]['id'] == $id){
                $newTabla = $this->db[$tabla][$i];
            }
        }

        return $newTabla;
    }

    public function GetTableField($field, $value, $tabla, $condicion = "=")
    {
        $newTabla = array();
        echo $condicion;
        for ($i=0; $i < count($this->db[$tabla]); $i++) { 
            if($condicion == "="){
                if($this->db[$tabla][$i][$field] == $value)
                    $newTabla[] = $this->db[$tabla][$i];
            }elseif($condicion == ">="){
                if($this->db[$tabla][$i][$field] >= $value)
                    $newTabla[] = $this->db[$tabla][$i];
            }elseif($condicion == "<="){
                if($this->db[$tabla][$i][$field] <= $value)
                    $newTabla[] = $this->db[$tabla][$i];
            }elseif($condicion == ">"){
                if($this->db[$tabla][$i][$field] > $value)
                    $newTabla[] = $this->db[$tabla][$i];
            }elseif($condicion == "<"){
                if($this->db[$tabla][$i][$field] < $value)
                    $newTabla[] = $this->db[$tabla][$i];
            }
        }

        return $newTabla;
    }


}
?>