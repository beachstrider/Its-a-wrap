<?php

class ItsaWrap_Activator {
	public static function activate() {
		global $wpdb;
		
		add_option('itsaWrap_url', '');

		$table_name1 = $wpdb->prefix . "itsaWrap_feeds";
		$table_name2 = $wpdb->prefix . "itsaWrap_podcasts";
		$charset_collate = $wpdb->get_charset_collate();

		$sql1 = "
			CREATE TABLE $table_name1 (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				title tinytext NOT NULL,
				description text NOT NULL,
				duration int,
				image varchar(255) DEFAULT '' NOT NULL,
				banner varchar(255),
				audio varchar(255) DEFAULT '' NOT NULL,
				published_at TIMESTAMP,
				podcast_id int(9) NOT NULL,
				PRIMARY KEY  (id)
			) $charset_collate;
		";
			
		$sql2 = "
			CREATE TABLE $table_name2 (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				url varchar(255) NOT NULL,
				image varchar(255),
				banner varchar(255),
				detail_page varchar(55),
				PRIMARY KEY  (id)
			) $charset_collate;
		";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta([$sql1, $sql2]);
	}
}