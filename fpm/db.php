<?php
  function getDB() {
    $host = "db";
    $user = "root";
    $password = "password";
    $database = "Moon";
    $con = mysqli_connect($host, $user, $password, $database);
    return $con;
  };
?>