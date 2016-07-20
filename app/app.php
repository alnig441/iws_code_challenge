<?php

session_id('TimesheetSession');
session_start();

define(TS_URL, "https://radiant-cove-60089.herokuapp.com/api/v1/");

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

$app->get('/', function() use($app){
    
    return $app['twig']->render('login.twig');

});

$app->post('/edit/{itemId}', function() use($app){
    
    echo 'get one:'.json_encode($_POST).'<br/>';
    
    return $app['twig']->render('editTicket.twig');
});

$app->post('/', function($loginUrl = 'login', $getItemsUrl = 'assignedItems/tickets/') use($app){
    
    $login = TS_URL.$login;
    
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
            
            return $app['twig']->render('viewTickets.twig', $tickets);
        }
    }
});


$app->post('/addTicket', function() use($app){

    
    $_POST['userId'] = $_SESSION['userId'];
   
    echo 'adding ticket'.json_encode($_POST).'<br/>';
    
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

