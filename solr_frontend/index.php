<?php

$resultsFound = false;
    if(isset($_GET['search'])){

        $searchQueryParams = array();
        $url = 'http://localhost:8983/solr/dtm_reports/select?q=';
        $start =  0 . '';//(isset($_GET['start'])) ? $_GET['start'] : 0;
        if(isset($_GET['start'])){
            //var_dump($_GET['start']);
        }
        foreach($_GET as $key => $value){
            #echo $key . ': ' . $value;
            if($value != ''){
                if($key == 'search' || $key == 'current_page'){
                    continue;
                }else if($key == 'regional_report'){
                    continue;
                }
                if($key == 'portal_report_file_content'){
                    $key = 'portal_report_file.content';
                }
                $searchQueryParams[$key] = urlencode(trim($value));

                //.= $key . ':' . $value . '*~100';
            }
        }

        $repostURL = '&search=search&';

        foreach($searchQueryParams as $key => $value){

            if($key == 'portal_report_file.content'){
                $url .= $key . ':' . '' . $value . ''. '*~1';
                $repostURL .= $key . ':' . '' . $value . ''. '*~1';
            }else{
                $url .= $key . ':' . $value . '*~5';
                $repostURL .= $key . ':' . $value . '*~5';
            }
            end($searchQueryParams);
            if($key != key($searchQueryParams)){
                $url .= '%20AND%20';
                $repostURL .= '%20AND%20';
            }
        }

        echo 'Repost:' . $repostURL;
        echo 'URL: ' . $url;
        

        $dtm_export_api = $url . '&rows=10&start='.$start.'&sort=published_date%20DESC&wt=php';

        //echo $dtm_export_api;
//        $dtm_export_api = 'http://localhost:8983/solr/dtm_reports/select?q=*:*&wt=php';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_URL, $dtm_export_api);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $result = curl_exec($curl);
        if(!$result){
            echo curl_error($curl);
        }

        curl_close($curl);

        eval('$data = ' . $result . ';');   

        $response = array();

        if(is_array($data)){
            foreach($data as $index => $row){
                if($index == 'response' ){
                    if(is_array($row)){
                        foreach($row as $key => $value){
                            if($key = 'docs'){
                                if(is_array($value)){
                                    $response = $value;
                                }
                            }
                        }
                    }
                }
            }
        }else{
            echo 'oops';
        }
    
        $numRows = $data['response']['numFound']; #echo 'Num Rows: ' . $numRows .'<br>';
        
        if($numRows > 0){
            $resultsFound = true;
            $rowsPerPage = 10; # echo 'Rows Per Page: ' . $rowsPerPage .'<br>';
            $totalPages = ceil($numRows / $rowsPerPage); # echo 'Total Pages: ' . $totalPages .'<br>';
            if(isset($_GET['current_page']) && is_numeric($_GET['current_page'])){
                $currentPage = (int) $_GET['current_page'];
            }else{
                $currentPage = 1;
            }
        
            if($currentPage > $totalPages){
                $currentPage = $totalPages;
            }else if($currentPage < 1){
                $currentPage = 1;
            }
        
            $offset = ($currentPage - 1) * $rowsPerPage; #echo 'Offset: ' . $offset .'<br>';
            $loopEnd = $currentPage != $totalPages? $offset + $rowsPerPage : $numRows ; #echo 'Loop End: ' . $loopEnd    .'<br>';
        }
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Solr Search</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-T8Gy5hrqNKT+hzMclPo118YTQO6cYprQmhrYwIiQ/3axmI1hQomh7Ud2hPOy8SP1" crossorigin="anonymous">
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet'>
    <link rel="stylesheet" href="css/custom.css" />
</head>
<body>
    <div class="breacrumb-area bg-light-grey-1 ">
        <div class="container ">
            <div class="row">
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <img src="images/logo.png" height="100px" alt="IOM Logo">
                </a>
            </div>
        </div>
    </div>
    <div class="top-filter-area bg-blue padding-tb-10">
        <div class="container">
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" class="form-control" name="title" id="title" aria-describedby="helpId" placeholder="">
                            <small id="helpId" class="form-text text-muted">Report Title</small>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="title">Content</label>
                            <input type="text" class="form-control" name="portal_report_file.content" id="title" aria-describedby="helpId" placeholder="">
                            <small id="helpId" class="form-text text-muted">Report File Content</small>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="region">Region</label>
                            <select id="region" name="portal_region_ids" class="form-control">
                                <option value="">--SELECT REGION--</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="country">Country</label>
                            <select id="country" name="portal_country_id" class="form-control">
                                <option value="">--SELECT COUNTRY--</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="operation">Operation</label>
                            <select id="operation" name="portal_operation_id" class="form-control">
                                <option value="">--SELECT OPERATION--</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="report">Regional Report</label>
                            <select id="report" name="regional_report" class="form-control">
                                <option value="1">YES</option>
                                <option value="0">NO</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <button type="submit" name='search' value="search" class="btn btn-primary">Search</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="search-result-title">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h5><?php if($resultsFound){echo $numRows . ' matches found for:' ;   } if(isset($_GET['title'])){echo $_GET['title'];}?></h5>
                    <hr>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
        <div class="col-md-3 ">
                <div class="list-group-hover sidebar-widget-1">
                    <ul class="list-unstyled">
                        <li><a href="dashboard.html" class="list-group-item  bg-active"><i class="fa fa-sort" aria-hidden="true"></i> Sort By </a></li>
                        <li>
                            <div class="list-group-item">
                                <select name="title_sort"  class="form-control" id="title_sort">
                                    <option>Title</option>
                                    <option>Region</option>
                                    <option>Country</option>
                                </select>
                            </div>
                        </li>
                        <li>
                            <div class="list-group-item">
                                <select name="title_sort"  class="form-control" id="title_sort">
                                    <option>ASC</option>
                                    <option>DESC</option>
                                </select>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-md-9">

                <?php
                    if($resultsFound){
                        $output = "";
                        for($i=$offset; $i<$loopEnd; $i++){
                            $output .= '
                            <div class="crane-grid-listing-block">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="crane-grid-listing clearfix">
                                            <div class="col-md-3 crane-image-block ">
                                                <div class="crane-list-img">
                                                    <img class="img-responsive"src="'.(array_key_exists('thumb', $response[$i]) ? $response[$i]['thumb'][0] : '').'">
                                                </div>
                                            </div>
                                            <div class="col-md-7">
                                                <div class="crane-list-content">
                                                    <div class="crane-title">
                                                        <h3>'.$response[$i]['title'][0].'</h3>
                                                    </div>
                                                    
                                                    <div class="crane-phone">
                                                        <ul class="list-inline">
                                                            <li><i class="fa fa-phone"></i> '.(array_key_exists('contact', $response[$i]) ? $response[$i]['contact'][0] : '').' </li>
                                                        </ul>
                                                    </div>
                                                    <div class="crane-address">
                                                        <ul class="list-inline">
                                                            <li><i class="fa fa-map-marker"></i> <a href="#" target="_blank" rel="noopener noreferrer">'.(array_key_exists('portal_country_id', $response[$i]) ? $response[$i]['portal_country_id'][0] : '').'</a> </li>
                                                            <li><i class="fa fa-language" aria-hidden="true"></i>'.(array_key_exists('lang', $response[$i]) ? $response[$i]['lang'][0] : '').'</li>
                                                            <li><i class="fa fa-calendar-check-o" aria-hidden="true"></i>'.date('Y-M-d', strtotime((array_key_exists('from', $response[$i]) ? $response[$i]['from'][0] : date("Y-M-d")))).' - '.date('Y-M-d', strtotime((array_key_exists('to', $response[$i]) ? $response[$i]['to'][0] : date(Y-M-d)))).' </li>
                                                        </ul>
                                                    </div>
                                                    <div class="crane-category">
                                                        <span>'.(array_key_exists('summary', $response[$i]) ? $response[$i]['summary'][0] : '').'</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2" style="padding-top: 240px">
                                                <a href="'.(array_key_exists('portal_report_file_trackable', $response[$i]) ? $response[$i]['portal_report_file_trackable'][0] : '#').'" target="_blank" class="btn btn-warning btn-block">Download</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            ';  
                        }
                        echo $output;
                    }
                    
                ?>
                
            </div>
        </div>
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-end">
                <?php
                if($resultsFound){
                    if($currentPage > 1){
                    
                        $prevPage = $currentPage - 1;
                        echo '                 
                        <li class="page-item">
                            <a class="page-link" href="'.$_SERVER['PHP_SELF'].'&current_page='.$prevPage.'&start='.($rowsPerPage*($currentPage-1)).$repostURL.'" tabindex="-1">Previous</a>
                        </li>';
                    }
                    
                    $range = 3;
                    
                    for($x = ($currentPage - $range); $x <(($currentPage + $range) + 1); $x++){
                        if($x > 0 && $x <= $totalPages){
                            if($x == $currentPage){
                                echo '<li class="page-item active"><a class="page-link" href="#">'.$x.'</a></li>';
                            }else{
                                echo '<li class="page-item"><a class="page-link" href="'.$_SERVER['PHP_SELF'].'?current_page='.$x.'&start='.($rowsPerPage*($currentPage-1)).$repostURL.'">'.$x.'</a></li>';
                            }
                        }
                    }
                    
                    if($currentPage != $totalPages){
                        $nextPage = $currentPage + 1;
                        echo '
                        <li class="page-item">
                            <a class="page-link" href="'.$_SERVER['PHP_SELF'].'?current_page='.$nextPage.'&start='.($rowsPerPage*($currentPage-1)).$repostURL.'">Next</a>
                        </li>
                        ';
                    }
                }
                ?>
            </ul>
        </nav>
    </div>
</body>
</html>

<?php

echo ($rowsPerPage*($currentPage-1));
?>