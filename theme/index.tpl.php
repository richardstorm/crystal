<!doctype html>
<html class='no-js' lang='<?=$lang?>'>
<head>
<meta charset='utf-8'/>
<title><?=get_title($title)?></title>
<?php if(isset($favicon)): ?><link rel='shortcut icon' href='<?=$favicon?>'/><?php endif; ?>
<?php foreach($stylesheets as $val): ?>
<link rel='stylesheet' type='text/css' href='<?=$val?>'/>
<?php endforeach; ?>
<script src='<?=$modernizr?>'></script>
</head>
<body>
  <?php if (isset($menu_top)):?><div id='menu_top'><?=$menu_top?></div><?php endif; ?>
  <div id='wrapper'>
    <div id='header'><?=$header?></div>
    <?php if(isset($navbar)): ?><div id='navbar'><?=get_navbar($navbar)?></div><?php endif; ?>
    <div id='content'>
      <?php if(!isset($sidebar)): ?><div id='main'><?=$main?></div>
      <?php else: ?><div id='mainsplit'><?=$main?></div><div id='sidebar'><?=$sidebar?></div><?php endif; ?>
      <div id='footer'><?=$footer?></div>
    </div>
  </div>

<?php if(isset($jquery) && isset($jquery_src)):?><script src='<?=$jquery_src?>'></script><?php endif; ?>

<?php if(isset($javascript_include)): foreach($javascript_include as $val): ?>
<script src='<?=$val?>'></script>
<?php endforeach; endif; ?>

<?php if(isset($google_analytics)): ?>
<script>
  var _gaq=[['_setAccount','<?=$google_analytics?>'],['_trackPageview']];
  (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
  g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
  s.parentNode.insertBefore(g,s)}(document,'script'));
</script>
<?php endif; ?>

</body>
</html>
