<?php 
/**
 * This is a Crystal pagecontroller.
 *
 */
// Include the essential config-file which also creates the $crystal variable with its defaults.
include(__DIR__.'/config.php'); 



// Do it and store it all in variables in the Crystal container.
$crystal['title'] = "404";
$crystal['main'] = "<h1>Fel</h1><h2>404 - sidan kan inte hittas</h2><p>Sidan du söker finns inte här.</p>";
//$crystal['footer'] = "";

// Send the 404 header 
header("HTTP/1.0 404 Not Found");


// Finally, leave it all to the rendering phase of Crystal.
include(CRYSTAL_THEME_PATH);
