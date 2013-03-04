<?php
require_once("autoload.php");

class Database
{
  
  private $conn;
  private $stmnt = null;
  
  public function __construct()
  {
  
  }

  function __destruct()
  {
    $this->setConnection(NULL);
  }

 
    
  private function getSqlString($row)
  {
    $columns = array_keys($row);
    $sql = $columns[0];
    for($i = 1; $i < count($columns); $i++){
      $sql .= ",".$columns[$i];
    }
    return $sql;
  }
  

  private function getSqlBindString($row)
  {
    $columns = array_keys($row);
    $sql = "?";
    for($i = 1; $i < count($columns); $i++){
      $sql .= ",?";
    }
    return $sql;
  }


  public function testWord($word){
    $db = $this->connect();
    
    $stmnt = $db->prepare("SELECT count(1) as num FROM words where word like '$word%'");

    $str = null;
    $stmnt->execute();
    while($row = $stmnt->fetch(PDO::FETCH_ASSOC)){
      $str = $row["num"];
    }


    $this->disconnect();
    return $str;
  }


  /**
    Get an associative array of all of the words in the dictionary.
    */
  public function getDictionary(){
    $db = $this->connect();
    
    if($this->stmnt == null)
      $this->stmnt = $db->prepare("SELECT word FROM words");

    $dictionary = array();
    $this->stmnt->execute();

    while($row = $this->stmnt->fetch(PDO::FETCH_ASSOC)){
      $dictionary[$row["word"]] = 1;
    }

    $this->disconnect();
    return $dictionary;

  }


  public function getWord($word){
    $db = $this->connect();
    
    if($this->stmnt == null)
      $this->stmnt = $db->prepare("SELECT word FROM words where word=?");

    $str = null;
    $this->stmnt->bindParam(1, $word, PDO::PARAM_STR);
    $this->stmnt->execute();

    while($row = $this->stmnt->fetch(PDO::FETCH_ASSOC)){
      $str = $row["word"];
    }


    $this->disconnect();
    return $str;
  }

  /**
  Establishes a connection to the database based on the contents
  of the .ini file. A new connection will be made if the current
  connection is set to NULL, else, the connection still exists so
  none will be remade. Persistent connections are being used.

  It is the job of the code that calls Database::getInstance() to
  catch this error. The error should be handled using the Error 
  class, and it should be handeled outside of this class because the
  Error class takes a Response object so that it can send useful
  information back to the client in JSON form. This class never
  deals with Responses directly.
  */
  private function connect()
  {
    if($this->getConnection() !== NULL)
      return $this->getConnection();

    $options = $this->getDbOptions();
    
    $pdo = new PDO($this->getDSN());
    
    $this->setConnection($pdo);
    return $this->getConnection();
  }

  /**
  Disconnect a database connection if persistence is not being 
  used.
  */
  private function disconnect()
  {
    $options = $this->getDbOptions();
    if($options[PDO::ATTR_PERSISTENT])
      return;

    $this->setConnection(NULL);
  }

  private function getConfig()
  {
    return $this->conf;
  }

  private function getConnection()
  {
    return $this->conn;
  }

  private function setConnection($conn)
  {
    $this->conn = $conn;
  }

  private function getDbOptions()
  {
    return array(
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_PERSISTENT => true);
  }

  private function getDSN()
  {
    $root = $_SERVER["DOCUMENT_ROOT"]."../lib";
    return "sqlite:$root/words.sqlite3";
  }

}

?>
