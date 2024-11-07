<?php

   $name=$_GET["filename"];
$FileName =$name;

   
header('Content-disposition: attachment; filename="'.$FileName.'"');
readfile($FileName);

?>
