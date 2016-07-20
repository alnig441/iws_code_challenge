<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->

<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
        <style>
            html {font-family: "Courier New", sans-serif, monospace;}
            table {border: 1px; width: 75%; text-justify: distribute;}
            thead, th {font-weight: normal; border: 1px solid;}
            #comments {width: 50%;}
            #td_comments {text-align: left;}
            td {text-align: center;}
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
