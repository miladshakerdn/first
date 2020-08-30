<?php
error_reporting(E_ALL);
include_once "helper.php";
include_once "JsonDB.php";
$db = new JsonDB(__DIR__ . '/db_files');
//$db->delete('table1');
//$db->update('table1', ['first_name' => 'Milad'], ['first_name' => 'MILO']);
//$db->insert('table1', ['first_name' => '','last_name'=>'Imani', 'country' => 'usa']);
//$db->insert('table1', ['first_name' => 'elina2']);
//echo $db->last_indexes;
//$db->select('table1'); $db->schema;
//$db->update('table1',['first_name' => 'Elina'], ['last_name'=>'Imani']);

d($db->select('table1',['country' => 'Iran']));


class customException extends Exception {
  public function errorMessage() {
    //error message
    $errorMsg = $this->getMessage().' is not a valid E-Mail address.';
    return $errorMsg;
  }
}

$email = "";

try {
  try {
    //check for "example" in mail address
    if(strpos($email, "example") !== FALSE) {
      //throw exception if email is not valid
      throw new Exception($email);
    }
  }
  catch(Exception $e) {
    //re-throw exception
    throw new customException($email);
  }
}

catch (customException $e) {
  //display custom message
  echo $e->errorMessage();
}
?> 