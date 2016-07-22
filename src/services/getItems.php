<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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