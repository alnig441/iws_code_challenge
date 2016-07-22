<?php

session_id('iwscc');
session_start();

define(TS_URL, "https://radiant-cove-60089.herokuapp.com/api/v1/");

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

$app->get('/', function() use($app){
    
    session_unset();
    
    return $app['twig']->render('login.twig');

});

$app->post('/edit/{itemId}', function() use($app){
    
    echo 'get one:'.json_encode($_POST).'<br/>';
    
    return $app['twig']->render('editTicket.twig');
});

$app->post('/', function($loginUrl = 'login', $getItemsUrl = 'assignedItems/tickets/') use($app){
    
    session_start();
    
    $login = TS_URL.$login;
    
//    ENTER CODE TO DISTINGUISH BETWEEN AUTHENTICATED AND NOT-AUTHENTICATED USER ...
//    THEN SWITCH BETWEEN ADDING, EDITING OR DELETING
//    ALTERNATIVELY SETUP DB CALLS AS A SERVICE AND CALL RELEVANT SERVICE IN THEIR SEPARATE ROUTES (ADD, EDIT, DELETE) ....
    
    if(empty($_POST['username']) && !empty($_POST['password'])){
        
        return $app['twig']->render('login.twig');
        
    }
    
    else{
        
        $loginUrl = TS_URL.$loginUrl;

        $curl = curl_init();

        $postOptions = array(

            CURLOPT_URL => $loginUrl,
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
        
        if(!$user["success"]){
            
            return $app['twig']->render('login.twig').'wrong user/pw combo';
        
        }
        
        else {
//            
            if(!isset($_SESSION['userId'])){
                
                $_SESSION['userId'] = $user['userId'];
                
            }
            
            $getItemsUrl = TS_URL.$getItemsUrl.$_SESSION['userId'];

            preg_match_all('|Set-Cookie: (.*);|U', $data, $matches);   
            $cookies = implode('; ', $matches[1]);

            $curl = curl_init();

            $getTicketsOptions = array(

                CURLOPT_URL => $getItemsUrl,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_HEADER => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_COOKIE => $cookies,
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
            
//            BUILD ARRAY TO SEND TO TWIG TEMPLATE (EXISTING TIMESHEETS FROM TIMESHEET DB + ASSIGNED ITEMS FROM TICKET DB
            
            $con = mysqli_connect("localhost", "phpuser", "phpuserpw", "iws_cc");
            if(!$con){
                exit('Connect Error (' . mysqli_connect_errno() . ')'
                        . mysqli_connect_error() );
            }
            mysqli_set_charset($con, 'utf-8');

            $dbQuery = "SELECT * FROM timesheets WHERE userId = " . $_SESSION['userId'] ."";
            
            $iwsResult = mysqli_query($con, $dbQuery);
            
            if(mysqli_num_rows($iwsResult) < 1){

                $timesheets = array(
                    0 => array(
                        'created' => null,
                        'hours' => null,
                        'ticket' => null,
                        'comments' => null,
                        'billable' => null
                    )
                );
            }
            
            else {
                $timesheets = mysqli_fetch_all($iwsResult, MYSQLI_ASSOC);
            }

            $buildView = array(
                'tickets' => $tickets,
                'timesheets' => $timesheets,
             );
            
            mysqli_close($con);
            mysqli_free_result($timesheets);
            
            return $app['twig']->render('viewTickets.twig', $buildView);
        }
    }
});


$app->post('/addTimesheet', function() use($app){
    
    session_start();

    $_POST['userId'] = $_SESSION['userId'];
   
    echo 'adding ticket'.json_encode($_POST).'<br/>';
    
    $con = mysqli_connect("localhost", "phpuser", "phpuserpw", "iws_cc");
            if(!$con){
                exit('Connect Error (' . mysqli_connect_errno() . ')'
                        . mysqli_connect_error() );
            }
    mysqli_set_charset($con, 'utf-8');

    $values = "'". $_POST['created'] . "', " . $_POST['userId'] . ", " . $_POST['hours'] . ", '" .$_POST['ticket'] . "', '" . $_POST['comments'] . "', '" . $_POST['billable'] . "' ";
    
    $dbQuery = "INSERT INTO timesheets (created, userId, hours, ticket, comments, billable) VALUES (" . $values . ")";
            
    if($iwsResult = mysqli_query($con, $dbQuery)){
        
        echo 'updating ticket view ... <br/>';
        
    }
    
    else {
        
        echo 'check your form data';
        
    }
    
    return $app['twig']->render('viewTickets.twig', $tickets);
});

$app->post('/edit/update', function() use($app){
   
    echo 'editing ticket'.json_encode($_POST).'<br/>';
    
    return $app['twig']->render('viewTickets.twig', $tickets);
    
});

$app->post('/edit/delete', function() use($app){
   
    echo 'deleting ticket<br/>';
    
});

return $app;



/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

