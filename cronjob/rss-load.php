<?php

require_once "../../../../wp-config.php";

$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

$feeds_table = $table_prefix."itsaWrap_feeds";
$podcasts_table = $table_prefix."itsaWrap_podcasts";
$podcasts = $conn->query("SELECT * FROM $podcasts_table");
$success_load_podcast = false;
$conn->query("TRUNCATE TABLE ".$feeds_table);

foreach($podcasts as $podcast){
  $xml = @simplexml_load_file($podcast['url']);
  
  if($xml) {
    $success_load_podcast = true;
    foreach($xml->channel->item as $item){
      $namespace = $item->getNameSpaces(true);
      $itunes = $item->children($namespace['itunes']);
      $title = strip_tags($item->title);
      $description = strip_tags($item->description);
      $duration = strip_tags($itunes->duration);
      $image = '';
      if(isset($itunes->image)){
        $image = strip_tags($itunes->image->attributes()['href']);
      }
      $audio = strip_tags($item->enclosure['url']);
      $pubDate = date('Y-m-d h:i:s', strtotime($item->pubDate));

      $conn->query("
        INSERT INTO $feeds_table 
        SET
          title = '$title', 
          description = '$description',
          duration = '$duration',
          image = '$image',
          audio = '$audio',
          published_at = '$pubDate',
          podcast_id = '".$podcast['id']."'
      ");
    }
  }
}