<?php
require_once(dirname(__FILE__).'/core/config.php');

$JSONResponse = callDTMExportAPI();
echo $JSONResponse;

 $response = buildJsonOutput($JSONResponse);
 createDTMImportJSONFile($response);
