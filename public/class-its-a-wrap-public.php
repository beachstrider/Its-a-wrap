<?php

class ItsaWrap_Public {
	private $plugin_name;
	private $version;
	private $wpdb;

	public function __construct( $plugin_name, $version ) {
		global $wpdb;
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->wpdb = $wpdb;
	}

	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/its-a-wrap-public.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/its-a-wrap-public.js', array( 'jquery' ), $this->version, false );
	}

	public function add_shortcodes(){
		add_shortcode('its-a-wrap-rss-list', function($atts = [], $content = null, $tag = ''){
			global $wpdb;
			$atts = array_change_key_case((array)$atts, CASE_LOWER);
			$wrapRssList_atts = shortcode_atts([
				'id' => null,
				'limit' => null
			], $atts, $tag);
			
			$feed_table_name = $wpdb->prefix . "itsaWrap_feeds";
			$podcast_table_name = $wpdb->prefix . "itsaWrap_podcasts";
			$wrl_id = $wrapRssList_atts['id'];
			$list_limit = $wrapRssList_atts['limit'];

			if($wrl_id != null){
				if($list_limit != null){
					$rows = $wpdb->get_results("
						SELECT 
							f.title AS title, 
							f.duration AS duration, 
							f.published_at AS published_at, 
							f.image AS feed_image, 
							p.image AS podcast_image, 
							p.detail_page 
						FROM $feed_table_name AS f 
						LEFT JOIN $podcast_table_name AS p 
						ON f.podcast_id = p.id 
						WHERE podcast_id = $wrl_id 
						ORDER BY published_at DESC 
						LIMIT ".$list_limit
					);
				}else{
					$rows = $wpdb->get_results("
						SELECT 
							f.title AS title, 
							f.duration AS duration, 
							f.published_at AS published_at, 
							f.image AS feed_image, 
							p.image AS podcast_image, 
							p.detail_page 
						FROM $feed_table_name AS f 
						LEFT JOIN $podcast_table_name AS p 
						ON f.podcast_id = p.id 
						WHERE podcast_id = $wrl_id 
						ORDER BY published_at DESC
					");
				}
			}else{
				if($list_limit != null){
					$rows = $wpdb->get_results("
						SELECT 
							f.title AS title, 
							f.duration AS duration, 
							f.published_at AS published_at, 
							f.image AS feed_image, 
							p.image AS podcast_image, 
							p.detail_page 
						FROM $feed_table_name AS f 
						LEFT JOIN $podcast_table_name AS p 
						ON f.podcast_id = p.id 
						ORDER BY published_at DESC 
						LIMIT ".$list_limit
					);
				}else{
					$rows = $wpdb->get_results("
						SELECT 
							f.title AS title, 
							f.duration AS duration, 
							f.published_at AS published_at, 
							f.image AS feed_image, 
							p.image AS podcast_image, 
							p.detail_page 
						FROM $feed_table_name AS f 
						LEFT JOIN $podcast_table_name AS p 
						ON f.podcast_id = p.id 
						ORDER BY published_at DESC
					");
				}
			}

			$content = "
				<div class='itsaWrap-front-wrapper'>
			";
			
			foreach($rows as $row){
				$feed_page_url = get_site_url()."/".$row->detail_page."/".$row->title;

				if($row->podcast_image != null && $row->podcast_image != ''){
					$feed_image = $row->podcast_image;
				}else{
					$feed_image = $row->feed_image;
				}

				$content .= "
					<div class='itsaWrap-front-item'>
						<a href='".$feed_page_url."' class='itsaWrap-list-image'>
							<img src='".$feed_image."' />
						</a>
						<div style='display: flex; flex-direction: column;' class='itsaWrap-list-details'>
							<div>
								<p class='date'>".date("M d, Y", strtotime($row->published_at))."&nbsp;&nbsp;·&nbsp;&nbsp;".floor($row->duration/60)." minutes</p>
								<a href='".$feed_page_url."'><h5 class='title'>".$row->title."</h5></a>
								<div class='action'>
									<a href='".$feed_page_url."'>
									    <span class='image-wrapper'><img src='".get_site_url()."/wp-content/plugins/its-a-wrap/public/img/listen.svg' /></span>
									    <span class='action-text'>Listen now →</span>
									</a>
								</div>
							</div>
							<div>
								<div class='itsaWrap-share-popup'>Share
									<span class='itsaWrap-share-popuptext'>
										<a href='https://www.facebook.com/sharer/sharer.php?u=".$feed_page_url."' target='_blank'>
											<img src='".get_site_url()."/wp-content/plugins/its-a-wrap/public/img/facebook.png' />
										</a>
										<a href='http://twitter.com/share?url=".$feed_page_url."' target='_blank'>
											<img src='".get_site_url()."/wp-content/plugins/its-a-wrap/public/img/twitter.png' />
										</a>
										<a href='https://www.linkedin.com/shareArticle?mini=true&url=".$feed_page_url."' target='_blank'>
											<img src='".get_site_url()."/wp-content/plugins/its-a-wrap/public/img/linkedin.png' />
										</a>
									</span>
								</div>
							</div>
						</div>
					</div>
				";
			}
	
			$content .= "
				</div>
			";
			return $content;
		});

		add_shortcode('its-a-wrap-rss-detail', function($atts = [], $content = null, $tag = ''){
			global $wp_query;
			$title = isset($wp_query->query_vars['feed_title']) ? urldecode($wp_query->query_vars['feed_title']) : false;

			if (!$title) {
				return "
					<div class='itsaWrap-front-wrapper'>
						<h4>Oops, Feed not found</h4>
					</div>
				";
			}

			$atts = array_change_key_case((array)$atts, CASE_LOWER);
			$wrapRssList_atts = shortcode_atts([
				'field' => null
			], $atts, $tag);

			$selectedField = $wrapRssList_atts['field'];

			global $wpdb;
			$feed_table_name = $wpdb->prefix . "itsaWrap_feeds";
			$podcast_table_name = $wpdb->prefix . "itsaWrap_podcasts";

			$feed_detail = $wpdb->get_row("
				SELECT
					f.id, 
					f.title AS title, 
					f.description AS description, 
					f.duration AS duration, 
					f.published_at AS published_at, 
					f.image AS feed_image, 
					f.banner AS feed_banner, 
					f.audio AS audio,
					p.image AS podcast_image, 
					p.banner AS podcast_banner, 
					p.detail_page 
				FROM $feed_table_name AS f 
				LEFT JOIN $podcast_table_name AS p 
				ON f.podcast_id = p.id 
				WHERE title = '".$title."'
			");

			if($feed_detail == null){
				return "
					<div class='itsaWrap-front-wrapper'>
						<h4>Oops, Feed not found</h4>
					</div>
				";
			}

			$feed_list = $wpdb->get_results("
				SELECT *  
				FROM $feed_table_name AS f 
				LEFT JOIN $podcast_table_name AS p 
				ON f.podcast_id = p.id 
				WHERE f.id > ".$feed_detail->id." 
				ORDER BY published_at DESC
				LIMIT 10 
			");

			if($feed_detail->podcast_image != null && $feed_detail->podcast_image != ''){
				$feed_image = $feed_detail->podcast_image;
			}else{
				$feed_image = $feed_detail->feed_image;
			}

			if($feed_detail->podcast_banner != null && $feed_detail->podcast_banner != ''){
				$feed_banner = $feed_detail->podcast_banner;
			}else{
				$feed_banner = $feed_detail->feed_banner;
			}

			$content = "";
			if ($selectedField) {
				switch ($selectedField) {
					case 'title':
						$content = $feed_detail->title;
						break;
					case 'date':
						$content = $feed_detail->published_at;
						break;
					case 'duration':
						$content = floor($feed_detail->duration/60);
						break;
					case 'description':
						$content = $feed_detail->description;
						break;
					case 'image':
						$content = $feed_image;
						break;
					case 'audio':
						$content = "
							<audio controls>
								<source src='".$feed_detail->audio."' type='audio/mpeg'>
								Your browser does not support the audio element.
							</audio>";
					break;
				}
			}else {
				$content = "
					<div class='itsaWrap-front-wrapper'>
						<div class='itsWrap-front-detail-banner' style='background: url(".$feed_banner.")'></div>
						<div class='itsaWrap-front-detail-header-box'>
							<div style='display: flex; justify-content: space-between; margin-bottom: 20px;'>
								<div>
									<p>".date("M d, Y", strtotime($feed_detail->published_at))."</p>
									<h5>".$feed_detail->title."</h5>
								</div>
								<div>
									<img src='$feed_image' />
								</div>
							</div>
							<audio controls>
								<source src='".$feed_detail->audio."' type='audio/mpeg'>
								Your browser does not support the audio element.
							</audio>
						</div>
						<div class='itsaWrap-front-detail-body'>
							<div class='main-content'>
								<h5>Information</h5>
								<p>".$feed_detail->description."</p>
							</div>
						</div>
					</div>
				";
			}

			return $content;
		});
	}

	public function add_custom_redirect_rules(){
		global $wp_rewrite;
		$podcast_table_name = $this->wpdb->prefix."itsawrap_podcasts";
		$podcasts = $this->wpdb->get_results("SELECT * FROM $podcast_table_name");
		add_rewrite_tag('%feed_title%','([^&]+)');

		foreach($podcasts as $podcast){
			$detail_page = $podcast->detail_page;
			add_rewrite_rule('^'.$detail_page.'/([^/]*)', 'index.php?pagename='.$detail_page.'&feed_title=$matches[1]', 'top');
		}
		
		$wp_rewrite->flush_rules(false);
	}
}