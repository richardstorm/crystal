<?php 
/**
 * This is a Crystal pagecontroller.
 *
 */
// Include the essential config-file which also creates the $crystal variable with its defaults.
include(__DIR__.'/config.php'); 


// Do it and store it all in variables in the Crystal container.
$crystal['title'] = "Hello World";

$crystal['header'] = <<<EOD
<img class='sitelogo' src='img/crystal_logo.png' alt='Crystal Logo'/>
<span class='sitetitle'>Crystal webbtemplate</span>
<span class='siteslogan'>Återanvändbara moduler för webbutveckling med PHP</span>
EOD;

$crystal['main'] = <<<EOD
<h1>Hej Världen</h1>
<p>Detta är en exempelsida som visar hur Crystal ser ut och fungerar.</p>
EOD;

$crystal['footer'] = <<<EOD
<footer><span class='sitefooter'>Copyright © Richard Storm (<a href='mailto:richard.storm@live.se'>richard.storm@live.se</a>) | <a href='https://github.com/mosbth/Crystal-base' target='_blank'>Crystal på GitHub</a> | <a href='http://validator.w3.org/unicorn/check?ucn_uri=referer&amp;ucn_task=conformance'>Unicorn</a></span></footer>
EOD;


// Finally, leave it all to the rendering phase of Crystal.
include(CRYSTAL_THEME_PATH);
