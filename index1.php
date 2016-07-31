<?php
require_once "vendor/autoload.php";

$a=new \Mohuishou\Lib\YxzwSign();
$b=$a->index(15680698256,"lailin");
print_r($b);
//$b=$a->sign();
//print_r($b);
//$a->signNum();