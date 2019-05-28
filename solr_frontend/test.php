<?php

// $serializedResult = file_get_contents('http://localhost:8983/solr/dtm_reports/select?q=*%3A*&wt=json');
// $data = unserialize($serializedResult);
// var_dump($data);


        //$serializedResult = file_get_contents($url . '&wt=phps');
        //$data = unserialize($serializedResult);
        //var_dump($data);
        // $code = file_get_contents('http://localhost:8983/solr/dtm_reports/select?q=*:*&wt=php');
        // eval("\$data = " . $code . ";");

require_once(__DIR__ . '/URLify.php');
$dtm_export_api = 'http://localhost:8983/solr/dtm_reports/select?q=*:*&wt=json';

$curl = curl_init();
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_URL, $dtm_export_api);
//curl_setopt($curl, CURLOPT_POSTFIELDS, ');
//curl_setopt($curl, CURLOPT_HTTPHEADER, array(
    //'Content-Type: application/json'
    //'Content-Length: 0'
//));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

$result = curl_exec($curl);
if(!$result){
    echo curl_error($curl);
}
curl_close($curl);
// var_dump($result);
// echo $result;

eval ('$data = ' . $result . ';');

echo ($data);


    // $serializedResult = file_get_contents('http://localhost:8983/solr/dtm_reports/select?q=title:burundi*&wt=phps');
    // $data = unserialize($serializedResult);
    // //var_dump($data);
    // // $code = file_get_contents('http://localhost:8983/solr/dtm_reports/select?q=*:*&wt=php');
    // // eval("\$data = " . $code . ";");
    
    // $response = array();
    // if(is_array($data)){
    //     foreach($data as $index => $row){
    //         if($index == 'response' ){
    //             if(is_array($row)){
    //                 foreach($row as $key => $value){
    //                     if($key = 'docs'){
    //                         if(is_array($value)){
    //                             $response = $value;
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //     }
    // }                            <a class="page-link" href="'.$_SERVER['PHP_SELF'].'?current_page='.$prevPage.'" tabindex="-1">Previous</a>


 


    // $numRows = sizeof($response); #echo 'Num Rows: ' . $numRows .'<br>';
    // $rowsPerPage = 10; # echo 'Rows Per Page: ' . $rowsPerPage .'<br>';
    // $totalPages = ceil($numRows / $rowsPerPage); # echo 'Total Pages: ' . $totalPages .'<br>';
    // if(isset($_GET['current_page']) && is_numeric($_GET['current_page'])){
    //     $currentPage = (int) $_GET['current_page'];
    // }else{
    //     $currentPage = 1;
    // }

    // if($currentPage > $totalPages){
    //     $currentPage = $totalPages;
    // }else if($currentPage < 1){
    //     $currentPage = 1;
    // }

    // $offset = ($currentPage - 1) * $rowsPerPage; #echo 'Offset: ' . $offset .'<br>';
    // $loopEnd = $currentPage != $totalPages? $offset + $rowsPerPage : $numRows ; #echo 'Loop End: ' . $loopEnd    .'<br>';
// echo URLIfy::filter($dtm_export_api);