<?php
/**
 * Config-file for Crystal. Change settings here to affect installation.
 *
 */

/**
 * Set the error reporting.
 *
 */
error_reporting(-1);              // Report all type of errors
ini_set('display_errors', 1);     // Display all errors 
ini_set('output_buffering', 0);   // Do not buffer outputs, write directly


/**
 * Start the session.
 *
 */
session_name(preg_replace('/[^a-z\d]/i', '', __DIR__));
session_start();


/**
 * Define Crystal paths.
 *
 */
define('CRYSTAL_INSTALL_PATH', __DIR__ . '/..');
define('CRYSTAL_THEME_PATH', CRYSTAL_INSTALL_PATH . '/theme/render.php');


/**
 * Include bootstrapping functions.
 *
 */
include(CRYSTAL_INSTALL_PATH . '/src/bootstrap.php');


/**
 * Create the Crystal variable.
 *
 */
$crystal = array();


/**
 * Site wide settings.
 *
 */
$crystal['lang']         = 'sv';
$crystal['title_append'] = ' | Crystal en webbtemplate';

$crystal['header'] = <<<EOD
<img class='sitelogo' src='img/crystal_logo.png' alt='Crystal Logo'/>
<span class='sitetitle'>Crystal webbtemplate</span>
<span class='siteslogan'>Återanvändbara moduler för webbutveckling med PHP</span>
EOD;

$crystal['footer'] = <<<EOD
<footer>
  <span class="sitefooter">
    Copyright © Richard Storm (<a href='mailto:richard.storm@live.se'>richard.storm@live.se</a>) 
    <!--| <a href='https://github.com/richardstorm/Crystal-base' target='_blank'>Crystal på GitHub</a> -->
    | <a href='http://validator.w3.org/unicorn/check?ucn_uri=referer&amp;ucn_task=conformance'>Unicorn</a>
    <!-- | <a href='cookies.php' title="Cookies">Cookies</a>-->
  </span>
</footer>
EOD;

$crystal['byline'] = <<<EOD
<footer class="byline">
  <figure class="right"><img src="img/me/me_by.jpg" alt="Richard Storm">
    <figcaption>Richard Storm</figcaption>
  </figure>
  <p>Richard Storm studerar i kursen Databaser och Webbprogrammering vid Blekinge Tekniska Högskola.</p>
  <p>Richards webbanknytna programmeringskunskaper är följande: PHP, HTML, CSS, SQL och JavaScript.</p>
  <p>Dessutom är denne man bekant med programmeringsspråken C och Lua.</p>

  <ul class='icons'>
    <li><a href='http://google.com/+RichardStorm' target='_blank' ><img src='img/glyphicons/png/glyphicons_362_google+_alt.png' alt='google+-icon' title='Richard Storm på Google+' width='24' height='24'/></a></li>
    <li><a href='http://www.facebook.com/storm.richard' target='_blank' ><img src='img/glyphicons/png/glyphicons_390_facebook.png' alt='facebook-icon' title='Richard Storm på Facebook' width='24' height='24'/></a></li>
    <li><a href='http://youtube.com/c/RichardStorm' target='_blank' ><img src='img/glyphicons/png/glyphicons_382_youtube.png' alt='youtube-icon' title='Richard Storm på YouTube' width='24' height='24'/></a></li>
    <!--<li><a href='http://github.com/richardstorm/' target='_blank' ><img src='img/glyphicons/png/glyphicons_381_github.png' alt='github-icon' title='Richard Storm på GitHub' width='24' height='24'/></a></li>-->
    <li><a href='http://instagram.com/richardstorm/' target='_blank' ><img src='img/glyphicons/png/glyphicons_412_instagram.png' alt='instagram-icon' title='Richard Storm på Instagram' width='24' height='24'/></a></li>
  </ul>

</footer>
EOD;



/**
 * The navbar
 *
 */
$crystal['navbar'] = null; // To skip the navbar
/*$crystal['navbar'] = array(
  'class' => 'navbar',
  'items' => array(
    'page1' => array('text'=>'Text',  'url'=>'page1.php',  'title' => 'Hovering text'),
    'page2' => array('text'=>'Text',  'url'=>'page2.php',  'title' => 'Hovering text'),
    'page3' => array('text'=>'Text',  'url'=>'page3.php',  'title' => 'Hovering text'),
  ),
  'callback_selected' => function($url) {
    if(basename($_SERVER['SCRIPT_FILENAME']) == $url) {
      return true;
    }
  }
);*/



/**
 * Theme related settings.
 *
 */
//$crystal['stylesheet'] = 'css/style.css';
$crystal['stylesheets'] = array('css/style.css');
$crystal['favicon']    = 'favicon.ico';



/**
 * Settings for JavaScript.
 *
 */
$crystal['modernizr']  = 'js/modernizr.js';
$crystal['jquery']     = null; // To disable jQuery
$crystal['jquery_src'] = '//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js';
$crystal['javascript_include'] = array();
//$crystal['javascript_include'] = array('js/main.js'); // To add extra javascript files



/**
 * Google analytics.
 *
 */
$crystal['google_analytics'] = null; //'UA-22093351-1'; // Set to null to disable google analytics
