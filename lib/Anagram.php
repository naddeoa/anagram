<?php
require_once("autoload.php");

class Anagram{

  private $input;
  private $db;
  private $words;
  private $dictionary;

  private $min;
  private $max;
  private $used;

  public function __construct($input){
    $this->input = $input;
    $this->db = new Database();
    $this->words = array();
    $this->used = array();
    $this->min = -1;
    $this->max = 9999;
  }

  public function setMin($min){
    $this->min = $min == NULL? $this->min : $min;
  }

  public function setMax($max){
    $this->max = $max == NULL ? $this->max : $max;
  }

  private function getDictionary(){
    if($this->dictionary == null){
      $this->dictionary = $this->db->getDictionary();
    }

    return $this->dictionary;
  }


  /**
    Get everything in str1 minus what is in str2
    */
  private function strDiff($subject,$remove){
    //create an assosiative array for tracking the
    //characters that need to be removed from subject
    $chars = array();
    foreach(str_split($remove) as $char){
      if(!isset($chars[$char]))
        $chars[$char] = 0;
      $chars[$char] += 1; 
    }

    //add characters from subject to the return, unless
    //we need to remove them
    $pruned = "";
    foreach(str_split($subject) as $char){
      if(!isset($chars[$char]))
        $chars[$char] = 0;

      if($chars[$char] == 0)
        $pruned .= $char;
      else
        $chars[$char] -= 1;
    }

    return $pruned;
    //return join(array_diff(str_split($str1), str_split($str2)));
  }



  private $count = 0;
  private $bails = 0;
  private function permute($sofar, $input){
    //error_log("Testing with $sofar and $input");
 
    $this->count++;
    //for every letter in input fish
    for($i=0; $i < strlen($input); $i++){
      //get everything in input that we are not currently
      //looking at. So if input="fish", and input[i]='f'
      //then diff="ish"
      $diff = $this->strDiff($input,$input[$i]);
      $next = $sofar.$input[$i];
      //If next is not the beginning of any word, then don't bother
      //permuting anything passt it. So if next="xx" then clearly
      //we don't have to wander down that branch.
      $this->saveIfWord($next);




      //unfortunately this is really slow.
//      if( !$this->testIfWord($next)){
//        $this->bails++;
//        continue;
//      }

      //call recursively with sofar and the current input
      //letter. So in the running example, sofar="", input[i]='i'
      //and we are passing "i" as the new sofar. The input will
      //be everything in diff that isn't the current diff letter.
      //so if diff[j]=f, then we pass "sh" as the new input (second param).
      if(!$this->isUsed($next))
        $this->permute($next, $diff);

    }

  }



  private function saveIfWord($w){
    if( !(strlen($w) >= $this->min && strlen($w) <= $this->max))
      return;

    $dictionary = $this->getDictionary();
    if($dictionary[$w] == 1)
      $this->words[$w] = 1;

  }


  private function isUsed($w){
    if(isset($this->used[$w]))
      return true;
    $this->used[$w] = 1;
    return false;
  }

  private function testIfWord($w){
    $num = $this->db->testWord($w);
    //error_log("tested $w and got $num");
    if($num == 0)
      return false;
    return true;
  }


  public function getAnagrams(){
    $this->permute("",$this->input);
    $toJson = array();
    foreach($this->words as $k => $v){
      array_push($toJson,$k);
    }
    
    error_log("bails: $this->bails, count: $this->count");

    $jsonWrapper = array();
    $jsonWrapper["count"] = count($toJson);
    $jsonWrapper["words"] = $toJson;
    echo json_encode($jsonWrapper);

  }

}



?>

