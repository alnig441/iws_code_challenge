<?php

//include dirname(__FILE__).'/../src/services/getTimesheets.php';
//include dirname(__FILE__).'/../src/services/getItems.php';

function buildView ($userId) {
    
    $buildView = array(
        
        'tickets' => getItems($userId),
        'timesheets' => getTimesheets($userId)
        
    );
    
    return $buildView;
}

function getTimesheets($userId){
    
    $con = mysqli_connect("localhost", "phpuser", "phpuserpw", "iws_cc");
    if(!$con){
        exit('Connect Error (' . mysqli_connect_errno() . ')'
                . mysqli_connect_error() );
    }
    mysqli_set_charset($con, 'utf-8');

    $dbQuery = "SELECT * FROM timesheets WHERE userId = " . $userId ."";

    $iwsResult = mysqli_query($con, $dbQuery);

    if(mysqli_num_rows($iwsResult) < 1){

        $timesheets = array(
            0 => array(
                'created' => null,
                'hours' => null,
                'ticket' => null,
                'comments' => null,
                'billable' => null,
                'checked' => null,
                'id' => null
            )
        );
    }

    else {
        
        $timesheets = mysqli_fetch_all($iwsResult, MYSQLI_ASSOC);

         for( $i = 0; $i < sizeof($timesheets); $i++ ) {

             if($timesheets[$i]['billable'] == 1){
                $timesheets[$i]['checked'] = 'checked'; 
             }else{
             $timesheets[$i]['checked'] = null;
             }

         }
        
    }
    
    mysqli_close($con);
    mysqli_free_result($iwsResult);

    return $timesheets;
   
}

function getItems($userId) {

    $curl = curl_init();

    $getTicketsOptions = array(

        CURLOPT_URL => BASE_URL.GET_ITEMS.$userId,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIE => $_SESSION['cookie'],
        
    );

    curl_setopt_array($curl, $getTicketsOptions);

    $data = curl_exec($curl);

    $split  = preg_split('|{|', $data);
    $ticketStr = '';

    foreach($split as $key => $value){
        if($key != 0){
            $ticketStr = $ticketStr.'{'.$value;
        }
    }

    $tickets = json_decode($ticketStr, true);

    curl_close($curl);
    
    return $tickets;
}
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

