<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/../lib/autoload.php");
header("Content-type: application/json");

$a = new Anagram($_GET["q"]);
$a->setMax($_GET["max"]);
$a->setMin($_GET["min"]);
$a->getAnagrams();

?>

