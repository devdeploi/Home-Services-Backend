<?php 

$host = "localhost";
$username = "safpro_tech";
$password = "$@Fpro_tech";
$dbname = "G_services";

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
   die("Connection Failed: " . mysqli_connect_error());
}
?>