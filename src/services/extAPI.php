<?php

function getUser () {
    
    $curl = curl_init();

    $postOptions = array(

        CURLOPT_URL => BASE_URL.LOGIN,
        CURLOPT_HEADER => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($_POST),
    );

    curl_setopt_array($curl, $postOptions);

    $data = curl_exec($curl);

    $split = preg_split('|{|', $data);
    $user = json_decode('{'.$split[1], true);

    curl_close($curl);

    if($user['success']){

        $_SESSION['userId'] = $user['userId'];
        preg_match_all('|Set-Cookie: (.*);|U', $data, $matches);   
        $_SESSION['cookie'] = implode('; ', $matches[1]);

    }               

    return $user;
}

function getTickets () {
    
    $curl = curl_init();
    
    $getTicketsOptions = array(

        CURLOPT_URL => BASE_URL.GET_ITEMS.$_SESSION['userId'],
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
    
    $_SESSION['items'] = $tickets;
    
    curl_close($curl);
    
    return $tickets;
        
}