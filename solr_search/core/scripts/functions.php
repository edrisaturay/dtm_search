<?php

require_once (dirname(__FILE__).'/variables.php');


function indexDTMExportAPI(){
    
}

function createDTMImportJSONFile($JSONData){
  $fileName = 'api/dtm_import.json';
  $fileHandle = fopen($fileName, 'w+');
  fwrite($fileHandle, $JSONData);
  fclose($fileHandle);
}

function buildJsonOutput($JSONData){
  if(sizeof(json_decode($JSONData)) < 1){
    return json_encode(array());
    echo 'oops';
  }else{
    echo 'yay';
    $updateLastId = false;
    $inputArray = json_decode($JSONData);
    $output = array();
    foreach($inputArray as $item => $data){
      $placeholderArray = array();
      $data = (array) $data;
      foreach($data as $key => $value){
        if($key == 'portal_report_file'){
          try{
            $placeholderArray[$key] = getPDFContentAndMetadata($value);
            $updateLastId = true;
          }catch(Exception $e){
            echo 'Exception Caught: ' . $e;
            $updateLastId = false;
          }
        }else{
          $placeholderArray[$key] = $value;
        }
        if($updateLastId && $key == 'id'){
          insertLastPortalReportNid($value);
        }
      }
      $output[$item] = $placeholderArray;
    }
    return json_encode($output);
  }
}

function getDataFromResponse($response){
  if(is_array($response)){
      foreach($response as $index => $row){
          if($index == 'response'){
              if(is_array($row)){
                  foreach($row as $index => $value){
                      if($index == 'docs'){
                          if(is_array($value)){
                              return $value;
                          }
                      }
                  }
              }
          }
      }
  }    
}

/*****************************************************************/
/*      Used to get the content and metadata from pdf url        */
/* It receive a url and rerurn an array[2] content and metadata  */
/*****************************************************************/
function getPDFContentAndMetadata($pdfURL){
  global $parser;
  $outputArray = array();
  $pdf = $parser->parseFile($pdfURL);
  $PDFContent = $pdf->getText();
  $outputArray['content'] = utf8_encode($PDFContent);
  $details = $pdf->getDetails();
  foreach($details as $key => $value){
    if(is_array($value)){
      $value = implode(', ', $value);
    }
    $outputArray[$key] = utf8_encode($value);
  }
  return $outputArray;
}
/******************************************/
/*      Used to call DTM_Export API       */
/******************************************/
function callDTMExportAPI(){
  $dtm_export_api = 'https://displacement.iom.int/dtm_export?entity=report&';
  $lastID = getLastPortalReportNid();
  $key='jv6vJVoQoM4wwe7H0BnHumgbFgAQG9fxwnvWe8NncwACdbu51Jqs0UwkcQlQakp6LzXvYklBbqlSJ6AeSjm49aXQKa3bTqlEwdPPjtFGn5f7y7aqfRQ52dmvfZRchl7e';
  $dtm_export_api_url = $dtm_export_api . 'last_id=' . $lastID . '&key=' . $key;

  $curl = curl_init();
  curl_setopt($curl, CURLOPT_POST, 1);
  curl_setopt($curl, CURLOPT_URL, $dtm_export_api_url);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'Content-Length: 0'
  ));
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

  $result = curl_exec($curl);
  if(!$result){
      echo curl_error($curl);
  }
  curl_close($curl);
  return $result;
}

function indexToSolr($reports){
    global $solr;
    $solrReports = array();
    foreach($reports as $key => $fields){
        $report = new Apache_Solr_Document();
        foreach($fields as $key => $value){
            if(is_array($value)){
                foreach($value as $data){
                    $report->setMultiValue($key, $data);
                }
            }else{
                //$report->$key = $value;
            }
        }
        $solrReports[] = $report;
    }

    try{
        $solr -> addDocuments($solrReports);
        $solr -> commit();
        $colr -> optimize();
    }catch(Exceptoin $e){
        echo $e->getMesssage();
    }
}

/*Used for getting last report nid*/

function getLastPortalReportNid(){
  global $conn;
  $lastID = 0;
  $query = 'SELECT download_track_portal_report_nid FROM download_track LIMIT 1';
  $result = $conn->query($query);
  if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
      $lastID = $row['download_track_portal_report_nid'];
    }
  }
  return $lastID; 
}

function insertLastPortalReportNid($id){
  global $conn;
  $query = 'REPLACE INTO download_track (download_track_id, download_track_portal_report_nid) VALUES(?, ?)';
  $stmt = $conn->prepare($query);
  $stmt->bind_param('ii', $trackId, $id);
  $trackId = 1;
  $stmt->execute();
}
