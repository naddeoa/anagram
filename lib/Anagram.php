<?php
require_once("autoload.php");

class Anagram{

  private $input;
  private $db;
  private $words;

  private $min;
  private $max;

  public function __construct($input){
    $this->input = $input;
    $this->db = new Database();
    $this->words = array();
    $this->min = -1;
    $this->max = 9999;
  }

  public function setMin($min){
    $this->min = $min == NULL? $this->min : $min;
  }

  public function setMax($max){
    $this->max = $max == NULL ? $this->max : $max;
  }

  private function strDiff($str1,$str2){
    return join(array_diff(str_split($str1), str_split($str2)));
  }
 
 
  private function permute(&$save, $sofar, $input){
  
    if(strlen($input) <= 1){
      $this->saveIfWord($sofar.$input);
      //array_push($save,$sofar.$input);
    }else{
      for($i=0; $i<strlen($input); $i++){
        $diff = $this->strDiff($input,$input[$i]);
        $this->saveIfWord($input);
        for($j=0; $j<strlen($diff); $j++){
          $this->permute($save, $sofar.$input[$i].$diff[$j], $this->strDiff($diff,$diff[$j]));
        }
      }
    }
  }


  private function saveIfWord($w){
    $word = $this->db->getWord($w);
    if($word !== null)
      $this->words[$word] = 1;
  }

  public function getAnagrams(){
    $permutations = array();
    $this->permute($permutations,"",$this->input);
    $toJson = array();
    foreach($this->words as $k => $v){
      if(strlen($k) >= $this->min && strlen($k) <= $this->max)
        array_push($toJson,$k);
    }
    
    $jsonWrapper = array();
    $jsonWrapper["words"] = $toJson;
    echo json_encode($jsonWrapper);

  }

}



?>

