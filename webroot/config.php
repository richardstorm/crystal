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
 * Set timezone and character encoding.
 *
 */
date_default_timezone_set('Europe/Stockholm');
mb_internal_encoding('UTF-8');


/**
 * Define Crystal paths.
 *
 */
define('CRYSTAL_INSTALL_PATH', __DIR__ . DIRECTORY_SEPARATOR . '..');
define('CRYSTAL_THEME_PATH', CRYSTAL_INSTALL_PATH . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . 'render.php');


/**
 * Include bootstrapping functions.
 *
 */
include(CRYSTAL_INSTALL_PATH . '/src/bootstrap.php');


/**
 * Start the session.
 *
 */
session_name(preg_replace('/[^a-z\d]/i', '', __DIR__));
session_start();


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
$crystal['title_append'] = ' | RM Rental Movies';

$loginRow = isset($_SESSION['user']->name) && isset($_SESSION['user']->id) ? "<a href='users.php?id={$_SESSION['user']->id}' title='Visa profil'>{$_SESSION['user']->name}</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='logout.php' title='Logga ut'>Logga ut</a>" : "<a href='login.php' title='Logga in'>Logga in / Skapa konto</a>";
$adminRow = isset($_SESSION['user']->name) && isset($_SESSION['user']->id) ? "<a href='admin.php' title='Adminsidan'>Adminsidan</a>&nbsp;&nbsp;|&nbsp;&nbsp;" : null;

$crystal['menu_top'] = <<<EOD
<span class='contents'>
  <span class='left'>
    Fri frakt om du hyr minst tre filmer
    &nbsp;&nbsp;|&nbsp;&nbsp;
    Beställningar innan 15:00 skickas nästa arbetsdag!
  </span>
  <span class='right'>
    {$adminRow}
    <a href='users.php' title='Användarsidor'>Användarsidor</a>
    &nbsp;&nbsp;|&nbsp;&nbsp;
    {$loginRow}
  </span>
</span>
EOD;

$crystal['header'] = <<<EOD
<span class='sitelogo'></span>
<span class='sitetitle'>RM Rental Movies</span>
<span class='siteslogan'>Din hyrfilmsbutik på nätet!</span>
<form action='movies.php' class='sitesearch'>
  <input type='search' name='search' placeholder='Sök film eller person' maxlength='32'/>
  <input type='submit' value='Sök'/>
</form>
EOD;

$thisYear = date('Y');
$crystal['footer'] = <<<EOD
<footer>
  <span class='sitefooter'>
    Copyright © {$thisYear} RM Rental Movies. All rights reserved.
    &nbsp;&nbsp;|&nbsp;&nbsp;
    <a href='http://validator.w3.org/unicorn/check?ucn_uri=referer&amp;ucn_task=conformance' title='Validera sidan med Unicorn'>Unicorn</a>
    &nbsp;&nbsp;|&nbsp;&nbsp;
    Org.nr. 123456-7890
    &nbsp;&nbsp;|&nbsp;&nbsp;
    E-post <a href='mailto:info@rmrental.com' title='Maila oss'>info@rmrental.com</a>
  </span>
</footer>
EOD;



/**
 * Settings for the database.
 *
 */
define('DB_USER', 'root'); // The database username
define('DB_PASSWORD', ''); // The database password
$crystal['database']['dsn']            = 'mysql:host=localhost;dbname=rist15;';
$crystal['database']['username']       = DB_USER;
$crystal['database']['password']       = DB_PASSWORD;
$crystal['database']['driver_options'] = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'");


/**
 * Define the menu as an array
 */
$crystal['navbar'] = array(
  // Use for styling the menu
  'class' => 'navbar',
 
  // Here comes the menu structure
  'items' => array(    
    // This is a menu item
    'start' => array(
      'text'  => 'Start',
      'url'   => 'start.php',
      'title' => 'Startsidan',
    ),
    
    // This is a menu item
    'movies' => array(
      'text'  => 'Filmer',
      'url'   => 'movies.php',
      'title' => 'Lista av tillgängliga filmer',
    ),
    
    // This is a menu item
    'news'    => array(
      'text'  => 'Nyheter',
      'url'   => 'news.php',
      'title' => 'Blogglista med nyheter',
    ),
    
    // This is a menu item
    'contest'  => array(
      'text'  => 'Tävling',
      'url'   => 'contest.php',
      'title' => 'Tävla och vinn gratis filmer'
    ),
    
    // This is a menu item
    'calendar'  => array(
      'text'  => 'Filmkalendern',
      'url'   => 'calendar.php',
      'title' => 'Filmkalendern med månadens film'
    ),
    
    // This is a menu item
    'about'  => array(
      'text'  => 'Om RM',
      'url'   => 'about.php',
      'title' => 'Information om RM Rental Movies'
    ),
    
    /*// This is a menu item
    'login'  => array(
      'text'  => 'Logga in',
      'url'   => 'login.php',
      'title' => 'Logga in'
    ),
    
    // This is a menu item
    'source'  => array(
      'text'  => 'Källkod',
      'url'   => 'source.php',
      'title' => 'Visa källkod'
    ),*/
  ),
 
  // This is the callback tracing the current selected menu item base on scriptname
  'callback' => function($url) {
    if(basename($_SERVER['SCRIPT_FILENAME']) == $url) {
      return true;
    }
  }
);




/**
 * Theme related settings.
 *
 */
//$crystal['stylesheet'] = 'css/style.css';
$crystal['stylesheets'] = array('css/style.css');
$crystal['stylesheets'][] = 'css/movie.css';
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
