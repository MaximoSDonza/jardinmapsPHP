<?php 

class conecction{

    function conectar(){
        $con= mysqli_connect('localhost','root','','maps');
        return $con;
    }

}

$c = new conecction();
$connection = $c->conectar();


?>