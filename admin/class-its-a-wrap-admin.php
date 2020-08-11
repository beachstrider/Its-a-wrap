<?php

class ItsaWrap_Admin {

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
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/its-a-wrap-admin.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/its-a-wrap-admin.js', array( 'jquery' ), $this->version, false );
	}

	public function add_itsaWrap_menus(){
		add_menu_page(
			'It\'s a wrap Settings', 
			'It\'s a wrap',
			'manage_options',
			'itsaWrap_menu_main',
			array($this, 'itsaWrap_settings_page_html'),
			plugin_dir_url(__FILE__) . 'its-a-wrap-tiny.png',
			NULL
		);

		add_submenu_page(
			'itsaWrap_menu_main',
			'RSS Feed List', 
			'RSS Feed List',
			'manage_options',
			'itsaWrap_list_page',
			array($this, 'itsaWrap_list_page_html')
		);

		add_submenu_page(
			NULL,
			'RSS Feed Add', 
			'RSS Feed Add',
			'manage_options',
			'itsaWrap_add_page',
			array($this, 'itsaWrap_add_page_html')
		);

		add_submenu_page(
			NULL,
			'RSS Feed Update', 
			'RSS Feed Update',
			'manage_options',
			'itsaWrap_update_page',
			array($this, 'itsaWrap_update_page_html')
		);

		add_submenu_page(
			NULL,
			'Add Podcast', 
			'Add Podcast',
			'manage_options',
			'itsaWrap_add_podcast',
			array($this, 'itsaWrap_add_podcast_html')
		);

		add_submenu_page(
			NULL,
			'Edit Podcast', 
			'Edit Podcast',
			'manage_options',
			'itsaWrap_edit_podcast',
			array($this, 'itsaWrap_edit_podcast_html')
		);
	}

	public function itsaWrap_settings_page_html() {
		?>
		<div class="wrap">
			<h2>Feed Extractor Settings</h2>
			
			<?php
			if (isset($_POST['itsaWrap_delete_podcast'])) {
				$table_name = $this->wpdb->prefix."itsaWrap_podcasts";
				$id = $_POST['id'];
				$this->wpdb->query("DELETE FROM $table_name WHERE id = $id");

			?>
			<div class="notice notice-success"><p>Podcast has been deleted</p></div>
			<?php
			}
			?>
			<br>
			<table class='wp-list-table widefat fixed' style="padding: 10px;">
				<tr>
					<td>
						<div>
							<h3>Main options</h3>
							ITS A WRAP works for pulling and displaying external RSS feeds. <br>
							Use below short code formats on your pages. <br>
							<!-- <b>* This plugin only works on pretty permal link mode. Please set it if you haven't done!</b><br> -->
							<br>
							&nbsp;&nbsp; RSS feed list short code: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-weight: 600;">[its-a-wrap-rss-list id="1" limit="10"]</span> <br>
							&nbsp;&nbsp; RSS feed detail short code: &nbsp;&nbsp; <span style="font-weight: 600;">[its-a-wrap-rss-detail]</span> <br>
						</div>
					</td>
				</tr>
			</table>

			<?php
				$table_name = $this->wpdb->prefix."itsaWrap_podcasts";
				$podcasts = $this->wpdb->get_results("SELECT * FROM $table_name");
			?>
			<table class='wp-list-table widefat fixed' style="margin-top: 20px; padding: 10px;">
				<tr>
					<td>
						<h3 style="display: inline-block; margin-right: 10px;">Subscribed podcasts</h3>
						<a href="<?php echo admin_url('admin.php?page=itsaWrap_add_podcast'); ?>" class="page-title-action">Add New</a>
					</td>
				</tr>
				<theader>
					<tr>
						<th>#</th>
						<th>Podcast RSS URL</th>
						<th>Short code</th>
						<th></th>
					</tr>
				</theader>
				<tbody>
				<?php
					foreach($podcasts as $pc_index => $podcast){
					?>						
						<tr>
							<td><?php echo $pc_index + 1;?></td>
							<td><a href="<?php echo $podcast->url;?>" target="_blank"><?php echo $podcast->url;?></a></td>
							<td><b>[its-a-wrap-rss-list id="<?php echo $podcast->id;?>"]</b></td>
							<td>
								<a href="<?php echo admin_url('admin.php?page=itsaWrap_edit_podcast&id=' . $podcast->id); ?>">Edit</a>
								&nbsp;&nbsp;
								<form class="itsaWrap_delete_podcast_form" method="POST" action="#" style="display: inline-block;" onsubmit="return confirm('Do you really want to delete this rss feed?');">
									<input name="id" type="hidden" value="<?php echo $podcast->id; ?>" />
									<button name="itsaWrap_delete_podcast" type="submit" style="color: #dc3232; background: none; border: none; cursor: pointer;">Delete</button>
								</form>
							</td>
						</tr>
					<?php
					}
				?>
				</tbody>
			</table>
		</div>
		<?php
	}

	public function itsaWrap_list_page_html(){
		?>
			<div class="wrap">
				<h2 style="display: inline-block; margin-right: 5px;">All Feeds</h2>
				<?php
					$table_name = $this->wpdb->prefix . "itsaWrap_feeds";
					if(isset($_POST['itsaWrap_feed_delete'])){
						$id = $_POST['id'];

						$this->wpdb->query("
							DELETE FROM $table_name
							WHERE id = $id
						");

						?>
							<div class="notice notice-success"><p>Feed has been deleted.</p></div>
						<?php
					}
					
					if(isset($_POST['itsaWrap_feed_reload'])){
						$podcasts_table = $this->wpdb->prefix."itsaWrap_podcasts";
						$podcasts = $this->wpdb->get_results("SELECT * FROM $podcasts_table");
						$success_load_podcast = false;
						$this->wpdb->query("TRUNCATE TABLE ".$table_name);
						
						foreach($podcasts as $podcast){
							$xml = @simplexml_load_file($podcast->url);
							
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
									
									$this->wpdb->insert(
										$table_name,
										array(
											'title' => $title,
											'description' => $description,
											'duration' => $duration,
											'image' => $image,
											'audio' => $audio,
											'published_at' => $pubDate,
											'podcast_id' => $podcast->id
										)
									);
								}
							}
						}

						if($success_load_podcast){
						?>
							<div class="notice notice-success"><p>RSS has been loaded</p></div>
						<?php 
						}else{
						?>
							<div class="notice notice-error"><p>RSS loading is failure -  Check the podcast URL(s)</p></div>
						<?php 
						}
					}
			
					$rows = $this->wpdb->get_results("SELECT * FROM $table_name ORDER BY published_at DESC");
				?>
				<a href="<?php echo admin_url('admin.php?page=itsaWrap_add_page');?>" class="page-title-action">Add New</a>
				<form method="POST" action="#" style="display: inline-block;">
					<button name="itsaWrap_feed_reload" class="page-title-action" style="background-color: #007cba; color: #fff;"> &nbsp;Load RSS &nbsp;</button>
				</form>
				<br><br>
				<table class='wp-list-table widefat fixed striped posts itsaWrap-list-table'>
					<tr>
						<th class="manage-column ss-list-width">#</th>
						<th class="manage-column ss-list-width"></th>
						<th class="manage-column ss-list-width">Title</th>
						<th class="manage-column ss-list-width">Description</th>
						<th class="manage-column ss-list-width">Duration</th>
						<th class="manage-column ss-list-width">Published At</th>
						<th>&nbsp;</th>
					</tr>
					<?php foreach ($rows as $index => $row) { ?>
						<tr>
							<td class="manage-column ss-list-width"><?php echo $index + 1; ?></td>
							<td class="manage-column ss-list-width">
								<img class="img-thumb" src="<?php echo $row->image; ?>" />
							</td>
							<td class="manage-column ss-list-width"><?php echo $row->title; ?></td>
							<td class="manage-column ss-list-width row-description"><span><?php echo $row->description; ?></span></td>
							<td class="manage-column ss-list-width"><?php echo floor($row->duration/60); ?> mins</td>
							<td class="manage-column ss-list-width"><?php echo $row->published_at; ?></td>
							<td>
								<a href="<?php echo admin_url('admin.php?page=itsaWrap_update_page&id=' . $row->id); ?>">Edit</a>
								&nbsp;&nbsp;
								<form class="itsaWrap_feed_delete_form" method="POST" action="#" style="display: inline-block;" onsubmit="return confirm('Do you really want to delete this rss feed?');">
									<input name="id" type="hidden" value="<?php echo $row->id; ?>" />
									<button name="itsaWrap_feed_delete" type="submit" href="#" style="color: #dc3232; background: none; border: none; cursor: pointer;">Delete</button>
								</form>
							</td>
						</tr>
					<?php } ?>
				</table>
			</div>
		<?php
	}

	public function itsaWrap_add_page_html(){
		$table_name = $this->wpdb->prefix."itsaWrap_feeds";

		if (isset($_POST['itsaWrap_feed_add'])) {
			$image_url = "";
			$banner_url = "";

			if($_FILES['image']['name'] != ''){
				$uploadedfile = $_FILES['image'];
				$upload_overrides = array( 'test_form' => false );
		
				$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
				if ( $movefile && ! isset( $movefile['error'] ) ) {
					$image_url = $movefile['url'];
				}
			}

			if($_FILES['banner']['name'] != ''){
				$uploadedfile = $_FILES['banner'];
				$upload_overrides = array( 'test_form' => false );
		
				$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
				if ( $movefile && ! isset( $movefile['error'] ) ) {
					$banner_url = $movefile['url'];
				}
			}

			$pubDate = date('Y-m-d h:i:s');

			if($banner_url == ''){
				$this->wpdb->insert(
					$table_name,
					array(
						'title' => $_POST['title'],
						'description' => $_POST['description'],
						'audio' => $_POST['audio'],
						'image' => $image_url,
						'published_at' => $pubDate
					)
				);
			}else{
				$this->wpdb->insert(
					$table_name,
					array(
						'title' => $_POST['title'],
						'description' => $_POST['description'],
						'audio' => $_POST['audio'],
						'image' => $image_url,
						'banner' => $banner_url,
						'published_at' => $pubDate
					)
				);
			}
		}
		?>
		<div class="wrap">
			<h2>Add Feed</h2>
			
			<?php
			if (isset($_POST['itsaWrap_feed_add'])) { 
			?>
				<br>
				<div class="updated"><p>Feed has been created</p></div>
				<a href="<?php echo admin_url('admin.php?page=itsaWrap_list_page') ?>">&laquo; Back to feed list</a>

			<?php } else { ?>
				<br>
				<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" enctype="multipart/form-data">
					<table class='wp-list-table widefat fixed' style="padding: 10px;">
						<tr>
							<td></td>
							<td style="width: 50%;">
								<div style="display: flex;">
									<img src="<?php echo $feed->image;?>" style="width: 160px; height: 160px; margin-right: 30px;" />
									<div style="width: 100%;">
										<div style="display: flex; margin-bottom: 16px;">
											<label style="min-width: 80px; line-height: 28px;">Title</label>
											<input class="regular-text" type="text" name="title" style="width: 100%;" required />
										</div>
										<div style="display: flex; margin-bottom: 16px;">
											<label style="min-width: 80px; line-height: 28px;">Audio</label>
											<input class="regular-text" type="text" name="audio" style="width: 100%;" required />
										</div>
										<div style="display: flex; margin-bottom: 16px;">
											<label style="min-width: 80px; line-height: 28px;">Image</label>
											<input class="regular-text" type="file" name="image" style="width: 100%;" required />
										</div>
										<div style="display: flex; margin-bottom: 16px;">
											<label style="min-width: 80px; line-height: 28px;">Banner</label>
											<input class="regular-text" type="file" name="banner" style="width: 100%;" />
										</div>
									</div>
								</div>
							</td>
							<td></td>
						</tr>
						<tr>
							<td>
								<label style="line-height: 28px; float: right;">Description</label>
							</td>
							<td>
								<textarea class="large-text code" name="description" rows="10" required></textarea>
							</td>
							<td></td>
						</tr>
					</table>

					<br>
					<button type='submit' name="itsaWrap_feed_add" class='button button-primary'>&nbsp;&nbsp;Save&nbsp;&nbsp;</button>
				</form>
			<?php } ?>
		</div>
		<?php
	}

	public function itsaWrap_update_page_html(){
		$table_name = $this->wpdb->prefix."itsaWrap_feeds";
		$id = $_GET["id"];

		if (isset($_POST['itsaWrap_feed_update'])) {
			$image_url = "";
			$banner_url = "";

			if($_FILES['image']['name'] != ''){
				$uploadedfile = $_FILES['image'];
				$upload_overrides = array( 'test_form' => false );
		
				$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
				if ( $movefile && ! isset( $movefile['error'] ) ) {
					$image_url = $movefile['url'];
					
					$this->wpdb->update(
						$table_name,
						array(
							'image' => $image_url
						),
						array('id' => $id)
					);
				}
			}

			if($_FILES['banner']['name'] != ''){
				$uploadedfile = $_FILES['banner'];
				$upload_overrides = array( 'test_form' => false );
		
				$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
				if ( $movefile && ! isset( $movefile['error'] ) ) {
					$banner_url = $movefile['url'];
					
					$this->wpdb->update(
						$table_name,
						array(
							'banner' => $banner_url
						),
						array('id' => $id)
					);
				}
			}

			$this->wpdb->update(
				$table_name,
				array(
					'title' => $_POST['title'],
					'description' => $_POST['description'],
					'audio' => $_POST['audio'],
				),
				array('id' => $id)
			);
		}else {
			$feed = $this->wpdb->get_row("SELECT * from $table_name where id = $id");
		}
		?>
		<div class="wrap">
			<h2>Update Feed</h2>
			
			<?php
			if (isset($_POST['itsaWrap_feed_update'])) { 
			?>
				<br>
				<div class="updated"><p>Feed has been updated</p></div>
				<a href="<?php echo admin_url('admin.php?page=itsaWrap_list_page') ?>">&laquo; Back to feed list</a>

			<?php } else { ?>
				<br>
				<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" enctype="multipart/form-data">
					<table class='wp-list-table widefat fixed' style="padding: 10px;">
						<tr>
							<td></td>
							<td style="width: 50%;">
								<div style="display: flex;">
									<img src="<?php echo $feed->image;?>" style="width: 160px; height: 160px; margin-right: 30px;" />
									<div style="width: 100%;">
										<div style="display: flex; margin-bottom: 16px;">
											<label style="min-width: 80px; line-height: 28px;">Title</label>
											<input class="regular-text" type="text" name="title" value="<?php echo $feed->title; ?>" style="width: 100%;" required />
										</div>
										<div style="display: flex; margin-bottom: 16px;">
											<label style="min-width: 80px; line-height: 28px;">Audio</label>
											<input class="regular-text" type="text" name="audio" value="<?php echo $feed->audio; ?>" style="width: 100%;" required />
										</div>
										<div style="display: flex; margin-bottom: 16px;">
											<label style="min-width: 80px; line-height: 28px;">Image</label>
											<input class="regular-text" type="file" name="image" style="width: 100%;" />
										</div>
										<div style="display: flex; margin-bottom: 16px;">
											<label style="min-width: 80px; line-height: 28px;">Banner</label>
											<input class="regular-text" type="file" name="banner" style="width: 100%;" />
										</div>
									</div>
								</div>
							</td>
							<td></td>
						</tr>
						<tr>
							<td>
								<label style="line-height: 28px; float: right;">Description</label>
							</td>
							<td>
								<textarea class="large-text code" name="description" rows="10" required><?php echo $feed->description; ?></textarea>
							</td>
							<td></td>
						</tr>
					</table>

					<br>
					<button type='submit' name="itsaWrap_feed_update" class='button button-primary'>&nbsp;&nbsp;Save&nbsp;&nbsp;</button>
				</form>
			<?php } ?>
		</div>
		<?php
	}

	public function itsaWrap_add_podcast_html(){
		$table_name = $this->wpdb->prefix."itsaWrap_podcasts";

		if (isset($_POST['itsaWrap_add_podcast'])) {
			$image_url = null;
			$banner_url = null;

			if($_FILES['image']['name'] != ''){
				$uploadedfile = $_FILES['image'];
				$upload_overrides = array( 'test_form' => false );
		
				$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
				if ( $movefile && ! isset( $movefile['error'] ) ) {
					$image_url = $movefile['url'];
				}
			}

			if($_FILES['banner']['name'] != ''){
				$uploadedfile = $_FILES['banner'];
				$upload_overrides = array( 'test_form' => false );
		
				$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
				if ( $movefile && ! isset( $movefile['error'] ) ) {
					$banner_url = $movefile['url'];
				}
			}

			if($image_url != null){
				if($banner_url != null){
					$this->wpdb->insert(
						$table_name,
						array(
							'url' => $_POST['url'],
							'detail_page' => $_POST['detail_page'],
							'image' => $image_url,
							'banner' => $banner_url
						)
					);
				}else{
					$this->wpdb->insert(
						$table_name,
						array(
							'url' => $_POST['url'],
							'detail_page' => $_POST['detail_page'],
							'image' => $image_url,
						)
					);
				}
			}else{
				if($banner_url != null){
					$this->wpdb->insert(
						$table_name,
						array(
							'url' => $_POST['url'],
							'detail_page' => $_POST['detail_page'],
							'banner' => $banner_url
						)
					);
				}else{
					$this->wpdb->insert(
						$table_name,
						array(
							'url' => $_POST['url'],
							'detail_page' => $_POST['detail_page']
						)
					);
				}
			}
		}
		?>
		<div class="wrap">
			<h2>Add Podcast</h2>
			
			<?php
			if (isset($_POST['itsaWrap_add_podcast'])) { 
			?>
				<br>
				<div class="updated"><p>Podcast has been created</p></div>
				<a href="<?php echo admin_url('admin.php?page=itsaWrap_menu_main') ?>">&laquo; Back to settings page</a>

			<?php } else { ?>
				<br>
				<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" enctype="multipart/form-data">
					<table class='wp-list-table widefat fixed' style="padding: 10px;">
						<tr>
							<td>
								<div>
									<img src="" style="width: 200px; height: 200px; float: right;" />
								</div>
							</td>
							<td>
								<div>
									<img src="" style="width: 100%; height: 200px;" />
								</div>
							</td>
							<td></td>
						</tr>
						<tr>
							<td>
								<label style="line-height: 28px; float: right;">Podcast RSS URL</label>
							</td>
							<td>
								<input class="regular-text" type="text" name="url" placeholder="https://example.com/rss" style="width: 100%;" required />
							</td>
							<td></td>
						</tr>
						<tr>
							<td>
								<label style="line-height: 28px; float: right;">Detail page URI</label>
							</td>
							<td>
								<input class="regular-text" type="text" name="detail_page" placeholder="rss-detail" style="width: 100%;" required />
							</td>
							<td></td>
						</tr>
						<tr>
							<td>
								<label style="line-height: 28px; float: right;">Image</label>
							</td>
							<td>
								<input class="regular-text" type="file" name="image" style="width: 100%;" />
							</td>
							<td></td>
						</tr>
						<tr>
							<td>
								<label style="line-height: 28px; float: right;">Banner</label>
							</td>
							<td>
								<input class="regular-text" type="file" name="banner" style="width: 100%;" />
							</td>
							<td></td>
						</tr>
					</table>

					<br>
					<button type='submit' name="itsaWrap_add_podcast" class='button button-primary'>&nbsp;&nbsp;Save&nbsp;&nbsp;</button>
				</form>
			<?php } ?>
		</div>
		<?php
	}

	public function itsaWrap_edit_podcast_html(){
		$id = $_GET["id"];
		$table_name = $this->wpdb->prefix."itsaWrap_podcasts";

		if (isset($_POST['itsaWrap_add_podcast'])) {
			$image_url = "";
			$banner_url = "";

			if($_FILES['image']['name'] != ''){
				$uploadedfile = $_FILES['image'];
				$upload_overrides = array( 'test_form' => false );
		
				$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
				if ( $movefile && ! isset( $movefile['error'] ) ) {
					$image_url = $movefile['url'];
					
					$this->wpdb->update(
						$table_name,
						array(
							'image' => $image_url
						),
						array('id' => $id)
					);
				}
			}

			if($_FILES['banner']['name'] != ''){
				$uploadedfile = $_FILES['banner'];
				$upload_overrides = array( 'test_form' => false );
		
				$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
				if ( $movefile && ! isset( $movefile['error'] ) ) {
					$banner_url = $movefile['url'];
					
					$this->wpdb->update(
						$table_name,
						array(
							'banner' => $banner_url
						),
						array('id' => $id)
					);
				}
			}

			if(isset($_POST['delete_image'])){
				$this->wpdb->update(
					$table_name,
					array(
						'image' => null,
					),
					array('id' => $id)
				);
			}

			if(isset($_POST['delete_banner'])){
				$this->wpdb->update(
					$table_name,
					array(
						'banner' => null,
					),
					array('id' => $id)
				);
			}

			$this->wpdb->update(
				$table_name,
				array(
					'url' => $_POST['url'],
					'detail_page' => $_POST['detail_page']
				),
				array('id' => $id)
			);
		}else {
			$podcast = $this->wpdb->get_row("SELECT * from $table_name where id = $id");
		}
		?>
		<div class="wrap">
			<h2>Update Podcast</h2>
			
			<?php
			if (isset($_POST['itsaWrap_add_podcast'])) { 
			?>
				<br>
				<div class="updated"><p>Podcast has been updated</p></div>
				<a href="<?php echo admin_url('admin.php?page=itsaWrap_menu_main') ?>">&laquo; Back to settings page</a>

			<?php } else { ?>
				<br>
				<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" enctype="multipart/form-data">
					<table class='wp-list-table widefat fixed' style="padding: 10px;">
						<tr>
							<td>
								<div>
									<img src="<?php echo $podcast->image;?>" style="width: 200px; height: 200px; float: right;" />
								</div>
							</td>
							<td>
								<div>
									<img src="<?php echo $podcast->banner;?>" style="width: 100%; height: 200px;" />
								</div>
							</td>
							<td></td>
						</tr>
						<tr>
							<td>
								<label style="line-height: 28px; float: right;">Podcast RSS URL</label>
							</td>
							<td>
								<input class="regular-text" type="text" name="url" value="<?php echo $podcast->url; ?>" placeholder="https://example.com/rss" style="width: 100%;" required />
							</td>
							<td></td>
						</tr>
						<tr>
							<td>
								<label style="line-height: 28px; float: right;">Detail page URI</label>
							</td>
							<td>
								<input class="regular-text" type="text" name="detail_page" value="<?php echo $podcast->detail_page; ?>" placeholder="rss-detail" style="width: 100%;" required />
							</td>
							<td></td>
						</tr>
						<tr>
							<td>
								<label style="line-height: 28px; float: right;">Image</label>
							</td>
							<td>
								<input class="regular-text" type="file" name="image" style="width: 100%;" />
							</td>
							<td></td>
						</tr>
						<tr>
							<td>
								<label style="line-height: 28px; float: right;">Delete image</label>
							</td>
							<td>			
								<label class="screen-reader-text" for="cb-select-54"></label>
								<input id="cb-select-54" type="checkbox" name="delete_image">
							</td>
							<td></td>
						</tr>
						<tr>
							<td>
								<label style="line-height: 28px; float: right;">Banner</label>
							</td>
							<td>
								<input class="regular-text" type="file" name="banner" style="width: 100%;" />
							</td>
							<td></td>
						</tr>
						<tr>
							<td>
								<label style="line-height: 28px; float: right;">Delete image</label>
							</td>
							<td>			
								<label class="screen-reader-text" for="cb-select-55"></label>
								<input id="cb-select-55" type="checkbox" name="delete_banner">
							</td>
							<td></td>
						</tr>
					</table>

					<br>
					<button type='submit' name="itsaWrap_add_podcast" class='button button-primary'>&nbsp;&nbsp;Save&nbsp;&nbsp;</button>
				</form>
			<?php } ?>
		</div>
		<?php
	}
}