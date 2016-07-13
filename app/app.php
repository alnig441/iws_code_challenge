<?php

define(TS_URL, "https://radiant-cove-60089.herokuapp.com/api/v1/");

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

$app->get('/', function() use($app){
    
    return $app['twig']->render('login.twig');

});

$app->post('/', function($url='login') use($app){
    
    
    $tickets = array(
        "success" => true,
        "error" => false,
        "items" => array(
            0 => array(
                "client" => "Acme",
                "itemId" => 1022,
                "summary" => "API Integration",
                "date" => "some date", 
                "billable" => "yes",
                "hours" => 10
            ),
            1 => array(
                "client" => "AceCo",
                "itemId" => 1092,
                "summary" => "Microsite frontend",
                "date" => "some date", 
                "billable" => "yes",
                "hours" => 10
            )
        ) 
    );
    
    $url = TS_URL.$url;
    
    if(!empty($_POST['username']) && !empty($_POST['password'])){
        
        session_start();
        
        $cookieJar =  tempnam("/tmp", "TestProjectCookie");
        
        $options = array(
          
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => http_build_query($_POST),
            CURLOPT_COOKIEJAR => $cookieJar
        );
        
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        
        if(!$result = curl_exec($ch)){
             die('Error: "'.curl_error($ch). '" Code: "'.curl_errno($ch));
        }
        
        curl_close($ch);
        
        $result = json_decode($result, true);
        
        if(!$result['success']){
            return $app['twig']->render('login.twig');
        }
        else{
            $_SESSION['username'] = $_POST['username'];
            $_SESSION['userId'] = $result['userId'];
            
//            CODE TO RETRIEVE ASSIGNED TICEKTS.
            
//            echo 'session: '.json_encode($_SESSION).'<br/>';
//            
//            $options1 = array(
//                CURLOPT_RETURNTRANSFER => 1,
//                CURLOPT_URL => TS_URL.'assigneditems/tickets/'.$_COOKIE['userId']
//            );
//            
//            $ch1 = curl_init();
//            curl_setopt_array($ch1, $options1);
//            
//            $result1 = curl_exec($ch1);
//            
//            echo 'get result: '.($result1);
//            
//            if(!$result1['success']){
//                return 'something went wrong';
//            }
//            else{
//                return $app['twig']->render('viewTickets.twig');
//            }
            
            
//            echo "print from array: ".($tickets[0]['client'])."<br/>";
            
//            COMMENT OUT FOLLOWING RETURN STATEMENT WHEN ASSIGNED TICKET API IS WORKING
            return $app['twig']->render('viewTickets.twig', $tickets);
        }

    }
    
    else{
            return $app['twig']->render('login.twig');
    }
    
});


return $app;



/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

