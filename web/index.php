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
        <link href="styles.css" rel="stylesheet">
    </head>
    <body>
        <?php
            require_once __DIR__.'/../app/app.php';
            
            $app['debug']=true;
            
            $app->run();
        
        ?>

    </body>
</html>
