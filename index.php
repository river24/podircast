<?php

  define('__ROOT__', dirname(__FILE__));
  require_once(__ROOT__.'/config.php');
  
  function unauthenticated() {
    header('WWW-Authenticate: Basic realm="Podcast"');
    header('HTTP/1.0 401 Unauthorized');
    exit;
  }

  if (isset($username) && (isset($password) || isset($passhash))) {
    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
      unauthenticated();
    }
    else {
      if ($_SERVER['PHP_AUTH_USER'] != $username) {
        unauthenticated();
      }
      if (isset($password)) {
        if ($password != $_SERVER['PHP_AUTH_PW']) {
          unauthenticated();
        }
      }
      elseif (isset($passhash)) {
        if (!password_verify($_SERVER['PHP_AUTH_PW'], $passhash)) {
          unauthenticated();
        }
      }
    }
  }

  $base_url = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

  $items = [];
  foreach(glob($data_dir . "/*") as $file){
    if(is_file($file)){
      $items[] = $file;
    }
  }
  rsort($items);

  header('Content-Type: application/rss+xml; charset=utf-8');
  
?><?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:googleplay="http://www.google.com/schemas/play-podcasts/1.0">
  <channel>
    <title><?php echo $title; ?></title>
    <itunes:author><?php echo $author; ?></itunes:author>
    <googleplay:author><?php echo $author; ?></googleplay:author>
    <description><?php echo $description; ?></description>
    <media:thumbnail url="<?php echo $base_url; ?><?php echo $image_file; ?>"/>
    <itunes:image href="<?php echo $base_url; ?><?php echo $image_file; ?>"/>
    <googleplay:image href="<?php echo $base_url; ?><?php echo $image_file; ?>"/>
    <language><?php echo $lang; ?></language>
    <link><?php echo $base_url; ?></link>
<?php

  foreach ($items as $index => $file) {
    if ($limit >= 0 && $index >= $limit) {
      break;
    }

    $item_title = basename($file);
    if (isset($item_title_pattern) && isset($item_title_replacement)) {
      $item_title = preg_replace($item_title_pattern, $item_title_replacement, basename($file));
    }

    $item_updated_at = new DateTime('@' . strval(filemtime($file)));
    $item_updated_at->setTimezone(new DateTimeZone($timezone));

?>
    <item>
      <title><?php echo $item_title; ?></title>
      <description><?php echo $item_title; ?></description>
      <pubDate><?php echo $item_updated_at->format(DateTime::RFC822); ?></pubDate>
      <enclosure url="<?php echo $base_url; ?><?php echo $file; ?>" type="audio/mpeg" length="<?php echo filesize($file); ?>"/>
    </item>
<?php

  }

?>
  </channel>
</rss>
