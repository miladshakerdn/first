<?php

class JsonDB{
public $dir;
public $json_opts;
public $tableDir;
public $content;
public $flash;
public $schema;
public $schemaKey;
public $last_indexes;
private $todo;
	public function __construct( $dir = __DIR__.'/') {
		$this->dir = $dir;
		$this->json_opts[ 'encode' ] = JSON_UNESCAPED_UNICODE;//JSON_PRETTY_PRINT

    }
    // select table
    public function table($table)
    {
        $this->tableDir = sprintf( '%s/%s.json', $this->dir, $table);
        return $this->check_table($table);
    }

    private function check_table($table) {

        // Checks if JSON file exists, if not create
		if( !file_exists( $this->tableDir ) ) {
            throw new Exception("Table {$table} not found");
            return false;
        }
        
		// Read content of JSON file
        $content = json_decode(file_get_contents( $this->tableDir ),true);
        
		// Check if its arrays of jSON
        // if( !is_array( $content ) && !is_object( $content ) ) {
        //     throw new Exception("Table is not arrays of jSON");
        //     return false;
		// }
		// else{
			$this->schema =  $content['schema'];
			$this->schemaKey = array_keys($this->schema);
            $this->content =  $content['data'];

            return true;
        //}

    }
	public function push() {

        // $f = fopen( $this->tableDir, 'w+' );
		// fwrite( $f, json_encode( $this->merge() ) );
        // fclose( $f );
        file_put_contents($this->tableDir, json_encode( $this->merge() ));
        return true;
    }    
    // #FIX lest set in push func
    public function merge()
	{
        dd(array_values($this->content));
        return [
            "schema"=>$this->schema,
            "data"=>array_values($this->content)
        ];
    }
    // Column check
    public function checkDiff($data)
    {
        $data = array_keys($data);
        $keys = $this->schemaKey;
        $dif = array_diff($data,$keys);
            if(count($dif)){
                $dif = implode($dif,',');
                throw new Exception("Column {$dif} not found");
                return false;
            }else{
                return true;
            }

    }
    public function filterArray($data, $callBackedKey = true)
    {
        if(count($data)>0 && is_array($data)){
        $Keys = [];
        foreach ($data as $key => $value) {
            $Keys = array_merge(array_keys(array_column($this->content, $key), $value),$Keys);
        }
        if($callBackedKey){
            return $Keys;
        }
        return array_intersect_key($this->content, array_flip($Keys));
        }
        return ($callBackedKey ? array_keys($this->content):$this->content);

    }
    public function _callValidUpdate($setData)
    {
        foreach ($setData as $columnName => $value) {
            $rulls = (array) $this->schema[$columnName];
            foreach ($rulls as $rullName => $rull) {
                $rulefunc = '_is'. ucfirst($rullName);
                $parseData= [
                    $value,
                    $columnName,
                    $rull
                ];
                $err[] = call_user_func_array(array($this, $rulefunc),$parseData);
            }
        }
        foreach ($err as $val) {
            if(!$val)
                return false;
            }
        return true;        
    }
    // call validetion rulls
    public function _checkValid()
    {
        //$class =  get_class();
        $err = [];

            foreach ($this->schemaKey as $columnName) {
                //array()
                $allRull = json_decode(json_encode($this->schema[$columnName]),true);
                $rulls = array_keys($allRull);
                foreach ($rulls as $rull) {
                    $rulefunc = '_is'. ucfirst($rull);
                    $parseData= [
                        $this->flash[0],
                        $columnName,
                        $allRull[$rull]
                    ];
                    $err[] = call_user_func_array(array($this, $rulefunc),$parseData);
                }
            }
            // fix bug null cloumn value set null
            $dif = array_diff($this->schemaKey,array_keys($this->flash));
            if(count($dif)>0){
                $temp = array_flip($this->schemaKey);
                foreach ($temp as $key => $value) {
                    $temp[$key] = null;
                    //$this->flash[$key] = ($this->flash[$key])
                }
                $this->flash = array_merge($temp,$this->flash);            
            }
            
            foreach ($err as $val) {
                if(!$val)
                return false;
            }
            return true;
    }
    // Validtion is Nullable
    public static function _isNullable($data, $columnName, $rull){
        // if can null
        if($rull){
            return true;
        }
        // if nullable === false
        $result = (trim($data[$columnName]) === '' ? false : true);
        if($result === false){
            throw new Exception("No value provided for column {$columnName}");
            return false;
        }            
        return true;
    }
    // Validtion if data null and has default value, set that
    public function _isDefault($data, $columnName, $defaultValue = ''){
        if(trim($data[$columnName]) === '')
        $data[$columnName] = $defaultValue;
        $this->flash = $data;
        return true;
    }
    //insert to table
    private function _insert($tableName, array $data)
    {

        //$this->table($tableName);
        $this->flash[] = $data;
        if($this->checkDiff($data)){
            if($this->_checkValid()){
            // insert new row
            $this->content[] = $this->flash;
            $this->flash = null;
            // get last insert id
            $this->last_indexes =( count( $this->content ) - 1 );
            // save data
            $this->push();  
            //echo "fake saved";                  
            }      
        }

    }
    // select query
    public function _select($tableName, $data = [])    
    {   

        // check Column
        if($this->checkDiff($data)){
            $data = (count($data) && is_array($data) ? $data : []);
            $res= $this->filterArray($data,false);
            return (!empty($res) ? $res: []);
        }


    }
    public function _update($tableName, $setData = [], $data = [])
    {

        if(count($setData) && is_array($setData) && $this->checkDiff($setData)){

            if($this->_callValidUpdate($setData)){
            $findKey = $this->filterArray($data,true);
            foreach ($findKey as $value) {
                $this->content[$value] = array_merge((array)$this->content[$value],$setData);
            }
            $this->push();
            }
        }

    }
    public function _delete($tableName, $data = [])
    {
        // check Column
        if($this->checkDiff($data)){
            $data = (count($data) && is_array($data) ? $data : []);
            if(count($data)==0){
                $this->content = [];
                $this->push();    
            }
            $key = ($this->filterArray($data,true));
            $this->content = array_diff_key($this->content, array_flip($key));
            $this->push();
        }

    }
    public function __call($functionName,$arg)
    {    
        $this->todo = [
            "select"=>1,
            "insert"=>1,
            "update"=>1,
            "delete"=>1
        ];
    try {
        // need fix overload table
        $this->table($arg[0]);

        $functionName = strtolower($functionName);
        if(array_key_exists($functionName,$this->todo)){
            $func = '_'.strtolower($functionName);
            return call_user_func_array(array($this, $func),$arg);
        }else{
            throw new Exception("Class {$functionName} not found");
            return false;
        }

    } catch (Exception $e) {
        echo $e->getMessage();
    }
    }
}