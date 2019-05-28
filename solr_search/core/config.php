<?php

ini_set('memory_limit', '-1');

//include 'vendor/autoload.php';
require_once(dirname(__FILE__).'/../vendor/autoload.php');
require_once (dirname(__FILE__).'/scripts/functions.php');
require_once(dirname(__FILE__).'/Solr/Service.php' );

$solr = new Apache_Solr_Service( $solr_host, $solr_port, $solr_home/*.$solr_core*/ );
$parser = new \Smalot\PdfParser\Parser();
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if($conn->connect_error){
    die('Database Connection Error: ' . $conn->connect_error);
}else if( ! $solr -> ping() ){
    echo 'Solr Service not Responding. ';
    exit;
} 

