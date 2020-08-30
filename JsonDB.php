<?php

class JsonDB{
    private $db_path;

    public function __construct($db_path = __DIR__)
    {
        $this->db_path = $db_path;
    }
    public function insert($table_name=null,$columns=null)
    {
   
        try{
        $table_filepath = $this->db_path . '/' . $table_name . '.json';
        if (!file_exists($table_filepath)) {
            throw new Exception("Table $table_name not found");
                return false;
        }
        $table = json_decode(file_get_contents($table_filepath), true);
        
        $schema = $table["schema"];
foreach ($columns as $key => $value) {
    if (!isset($schema[$key])) {
        throw new Exception("Column $key not found");
        return false;
    }
}
$row = [];

foreach ($schema as $column_name => $attributes) {

    if (
        (
            !isset($columns[$column_name])
            && !$attributes["nullable"]
            && !isset($attributes["default"])
        ) || (
            isset($columns[$column_name])
            && $columns[$column_name] === null
            && !$attributes["nullable"]
        )
    ) {
        throw new Exception("No value provided for column $column_name");
        return false;
    }
    if (isset($columns[$column_name])) {
        $row[$column_name] = $columns[$column_name];
    }
    else {
        if (isset($attributes["default"])) {
            $row[$column_name] = $attributes["default"];
        } else {
            $row[$column_name] = null;
        }
    }
}
$table["data"][] = $row;
$table["data"] = array_values($table["data"]);
file_put_contents($table_filepath, json_encode($table));
        } catch (Exception $e) {
        echo $e->getMessage();
        }

    }

    public function select($table_name=null,$columns=null)
    {
  
        try{
                    $datas = array(
            "fucName"=>$table_name,
            "arg"=>$columns
        );
$ch = curl_init( 'http://vooj.ir/curl/curl.php');
# Setup request to send json via POST.
$payload = json_encode( array( "customer"=> $datas ) );
curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
# Return response instead of printing.
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
# Send request.
$result = curl_exec($ch);
curl_close($ch);
        $table_filepath = $this->db_path . '/' . $table_name . '.json';
        if (!file_exists($table_filepath)) {
            throw new Exception("Table $table_name not found");
                return false;
        }
        $table =(array) json_decode(file_get_contents($table_filepath), true);

if (empty($columns)) {

    return json_decode(json_encode($table['data']),);
}
$schema = $table['schema'];
foreach ($columns as $key => $value) {
    if (!isset($schema[$key])) {
        throw new Exception("Column $key not found");
        return false;
    }
}

$rows = [];
foreach ($table['data'] as $table_row) {
    foreach ($columns as $key => $value) {
        if ($table_row[$key] != $value) {
            continue 2;
        }
    }
    $rows[] = $table_row;
}
return json_decode(json_encode($rows),true);
        } catch (Exception $e) {
        echo $e->getMessage();
        }

    }

}
?>