<?php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'solr_search';

$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if(mysqli_connect_error()){
    die('Database Connection Error: ' . mysqli_error());
}

/*Used for getting last report nid*/

function getLastPortalReportNid(){
    // $query = 'SELECT * FROM '

    return 0;
}

function insertLastPortalReportNid($id){
    global $conn;
    $query = 'REPLACE INTO download_track (download_track_portal_report_nid) VALUES(?)';
    $statement = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($statement, 'i', $id);
    mysqli_stmt_execute($statement);
    mysqli_close($conn);
}