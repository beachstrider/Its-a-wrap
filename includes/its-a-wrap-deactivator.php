<?php

class ItsaWrap_Deactivator{
  public static function deactivate(){
    global $wpdb;
    
    delete_option('itsaWrap_url');

    $wpdb->query("
      DROP TABLE IF EXISTS {$wpdb->prefix}itsaWrap_feeds;
    ");

    $wpdb->query("
      DROP TABLE IF EXISTS {$wpdb->prefix}itsaWrap_podcasts;
    ");
  }
}