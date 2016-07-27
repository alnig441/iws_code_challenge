<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->

<html>
    <head>
        <meta charset="UTF-8">
        <title>RS Timesheet</title>
        <style>
            html {font-family: "Courier New", sans-serif, monospace;}
            td{font-size: 14px;}
            body {widht: 75%; margin: auto;}
        </style>   
    </head>
    <body>
        <?php
            require_once __DIR__.'/../app/app.php';
            
            $app['debug']=true;
            
            $app->run();
        
        ?>

    </body>
</html>
