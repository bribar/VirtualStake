<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Gismo_Admin' ) ) {

	class Gismo_Admin {
		
		public $settings;
		
		public function __construct() {
			// Register action and filter hooks
			add_action('admin_menu', array($this, 'add_menu'));
			add_action('admin_init', array($this, 'admin_init'), 20);
			
			add_action('after_switch_theme', array($this,'gismo_switch_theme'));
			add_action('after_setup_theme', array($this,'gismo_setup'));
			add_action('after_setup_theme', array($this,'gismo_content_width'));
			
			add_action('wp_ajax_save_settings', array($this, 'gismo_save_settings'));
			add_action('wp_ajax_save_menus', array($this, 'gismo_save_settings'));			
			add_action('wp_ajax_save_sidebars', array($this, 'gismo_save_settings'));
			
			add_action('add_meta_boxes', array($this,'gismo_post_format_section'), -100);
			add_action('save_post', array($this,'gismo_post_format_section_save'), 10);
			
			$this->settings = get_option('gismo_theme_settings',false);
			$this->settings['menus'] = get_option('gismo_theme_menus',false);
			$this->settings['sidebars'] = get_option('gismo_theme_sidebars',false);
			
		}
		
		public function admin_init() {
			
			add_action('admin_enqueue_scripts', array($this, 'admin_styles'));
			add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
			
		}
		
		/**
		 * On theme install.
		 */
		public function gismo_switch_theme() {
			
			$settings = [
				'style' => [
					'logo'       => ['url' => '', 'width' => 300, 'height' => 100],
					'background' => ['url' => '', 'size' => 'cover'],
					'font'       => '', 
					'theme'      => 'minimal', // minimal, custom
					'color'      => '#1e73be'
				],
				'layout' => [
					'orientation'       => 'horizontal', // vertical, vertical-center, horizontal
					'header_position'   => 'undocked',
					'header_config'     => 'left',
					'page_title'        => 'default',
					'lazy_images'       => 0,
					'uikit'             => [
						'theme'          => 'default', // almost-flat, gradient
						'css_components' => ['slidenav','slideshow'],
						'js_components'  => ['lightbox','slideshow']
					],
					'blog'        => [
						'layout'  => 'grid',
						'sidebar' => 'none',
						'paging'  => 'infinite'
					]
				],
			];
			
		  	add_option('gismo_theme_settings', $settings);
			
			$menus = [];
			
			add_option('gismo_theme_menus', $menus);
			
			$sidebars = [];
			
			add_option('gismo_theme_sidebars', $sidebars);
			
			// BULK PLUGIN INSTALLS
			$install_pluggins = [
				['black-studio-tinymce-widget','black-studio-tinymce-widget',1],
				['visual-form-builder','',0],
				['disqus-comment-system','disqus',1],
				['envira-gallery-lite','',0],
				['addthis','addthis_social_widget',1],
				['simple-social-icons','simple-social-icons',1],
				['wordpress-seo','',0],
				['the-events-calendar','',0],
				['mailChimp-forms-by-mailmunch','',0]
			];
			
			$url = 'http://api.wordpress.org/plugins/info/1.0/';
			$path = WP_PLUGIN_DIR . '/';
			
			$active_plugins = [];
			foreach($install_pluggins as $plugin){
				
				if(!is_dir($path . $plugin[0])){
					
					$args = (object) array( 'slug' => $plugin[0] );
					
					$request = array( 'action' => 'plugin_information', 'timeout' => 15, 'request' => serialize( $args) );
					
					$response = wp_remote_post( $url, array( 'body' => $request ) );
					
					$plugin_info = unserialize( $response['body'] );
					
					$file = $path . $plugin[0] . '.zip';
					file_put_contents($file, fopen($plugin_info->download_link, 'r'));
					
					if(filesize($file) > 0){
						
						$zip = new ZipArchive();
						$res = $zip->open($file);
						if ($res === TRUE) {
							
							$extract = $zip->extractTo(WP_PLUGIN_DIR);
							if($extract){
								if($plugin[2]){
									$active_plugins[] = $plugin[0] . '/' . $plugin[1] . '.php';
								}
								unlink($file);
								
								//activate_plugin($path . $plugin[0] . '/' . $plugin[1] . '.php');
							}
							
							$zip->close();
							
						}
						
					}
					
				}
				
			}
			
			if(!is_dir(WP_PLUGIN_DIR . '/github-updater-master')){
				// INSTALL GITHUB UPDATER TO KEEP THEME UP-TO-DATE
				
				$data = file_get_contents('https://github.com/afragen/github-updater/archive/master.zip');
				
				$destination = WP_PLUGIN_DIR . '/github-updater-master.zip'; // NEW FILE LOCATION
				$file = fopen($destination, 'w+');
				fputs($file, $data);
				fclose($file);
				
				$zip = new ZipArchive();
				$res = $zip->open($destination);
				if ($res === TRUE) {

					$extract = $zip->extractTo(WP_PLUGIN_DIR);
					if($extract){
						unlink($destination);
						$active_plugins[] = 'github-updater-master/github-updater.php';
						//activate_plugin(WP_PLUGIN_DIR . '/github-updater-master/github-updater.php');
					}
					
					$zip->close();

				}
				
			}
			
			update_option('gismo_theme_sidebars',array (
				1 => array (
					'name' => 'After Navigation',
					'hook' => 'gismo_after_primary_navigation',
					'class' => '',
					'description' => '',
					'title_class' => '',
					'widget_class' => ''
				),
			));
			
			update_option('active_plugins',$active_plugins);
			
			update_option('widget_search',array (
				1 => array (
					'title' => 'Search Site',
				),
				'_multiwidget' => 1
			));
			
			update_option('widget_simple-social-icons',array (
				2 => array (
					'title' => 'Connect',
					'size' => '36',
					'border_radius' => '3',
					'border_width' => '0',
					'alignment' => 'alignleft',
					'icon_color' => '#ffffff',
					'icon_color_hover' => '#ffffff',
					'background_color' => '#999999',
					'background_color_hover' => '#666666',
					'border_color' => '#ffffff',
					'border_color_hover' => '#ffffff',
					'behance' => '',
					'bloglovin' => '',
					'dribbble' => '',
					'email' => '',
					'facebook' => '#',
					'flickr' => '',
					'github' => '',
					'gplus' => '',
					'instagram' => '#',
					'linkedin' => '',
					'medium' => '',
					'periscope' => '',
					'phone' => '',
					'pinterest' => '',
					'rss' => '',
					'snapchat' => '',
					'stumbleupon' => '',
					'tumblr' => '',
					'twitter' => '',
					'vimeo' => '',
					'xing' => '',
					'youtube' => '',
				),
				'_multiwidget' => 1
			));
			
			update_option('sidebars_widgets',array (
				'wp_inactive_widgets' => array (
					0 => 'archives-2',
					1 => 'meta-2',
					2 => 'categories-2',
					3 => 'recent-posts-2',
					4 => 'recent-comments-2'
				),
				'after-navigation-1' => array (
					0 => 'search-1',
					1 => 'simple-social-icons-2',
				),
				'array_version' => 3
			));
			
			$pages = get_posts(array('numberposts' => 1, 'post_type' => 'page', 'name' => 'sample-page'));
			
			if(!empty($pages)){
				
				wp_delete_post(2,TRUE);
				
				wp_insert_post(array(
					'post_title' => 'Beliefs',
					'post_status' => 'publish',
					'post_type' => 'page',
					'post_content' => 'Add information about beliefs here.'
				));
				
				wp_insert_post(array(
					'post_title' => 'Family History',
					'post_status' => 'publish',
					'post_type' => 'page',
					'post_content' => 'Add information about family history here.'
				));
				
				wp_insert_post(array(
					'post_title' => 'Children',
					'post_status' => 'publish',
					'post_type' => 'page',
					'post_content' => 'Add information about what is offer for children.'
				));
				
				wp_insert_post(array(
					'post_title' => 'Sundays',
					'post_status' => 'publish',
					'post_type' => 'page',
					'post_content' => 'Add information about the sunday schedule and what to expect.'
				));
				
				wp_insert_post(array(
					'post_title' => 'Contact Us',
					'post_status' => 'publish',
					'post_type' => 'page',
					'post_content' => 'Add information about meeting times, location, and contact form.'
				));
				
			}
			
			$posts = get_posts(array('numberposts' => 1, 'post_type' => 'post', 'post_status' => 'publish', 'name' => 'hello-world'));
			
			if(!empty($posts)){
				
				wp_delete_post(1,TRUE);
				
				$standard = wp_insert_post(array(
					'post_title' => 'Standard Post',
					'post_status' => 'publish',
					'post_type' => 'post',
					'post_content' => 'Lorem Ipsum is <a href="http://www.google.com">simply dummy text</a> of the printing and typesetting industry. Lorem Ipsum has been the industry\'s <strong>standard dummy text</strong> ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. <em>It has survived not only five centuries</em>, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
					<ul>
						<li>List Item #1</li>
						<li>List Item #2</li>
						<li>List Item #3</li>
					</ul>
					<ol>
						<li>Numbered Item #1</li>
						<li>Numbered Item #2</li>
						<li>Numbered Item #3</li>
					</ol>
					<blockquote>This is quoted text</blockquote>'
				));
				
				$quote_id = wp_insert_post(array(
					'post_title' => 'Quote Post',
					'post_status' => 'publish',
					'post_type' => 'post',
					'post_content' => 'Do not dwell in the past, do not dream of the future, concentrate the mind on the present moment.'
				));
				
				wp_set_post_terms( $quote_id, 'post-format-quote', 'post_format' );
				
				
				$video_id = wp_insert_post(array(
					'post_title' => 'Video Post',
					'post_status' => 'publish',
					'post_type' => 'post',
					'post_content' => ''
				));
				
				wp_set_post_terms( $video_id, 'post-format-video', 'post_format' );
				add_post_meta($video_id, '_gismo_post_format_section_links', array (
				  	0 => 'https://vimeo.com/10602957'
				));
				
				
				$image_id = wp_insert_post(array(
					'post_title' => 'Single Image Post',
					'post_status' => 'publish',
					'post_type' => 'post',
					'post_content' => ''
				));
				
				wp_set_post_terms( $image_id, 'post-format-image', 'post_format' );
				add_post_meta($image_id, '_gismo_post_format_section_links', array (
				  	0 => 'https://media.ldscdn.org/images/media-library/gospel-art/church-history/orson-hyde-dedicates-palestine-37726-gallery.jpg'
				));
				
				
				$gallery_id = wp_insert_post(array(
					'post_title' => 'Image Gallery Post',
					'post_status' => 'publish',
					'post_type' => 'post',
					'post_content' => ''
				));
				
				wp_set_post_terms( $gallery_id, 'post-format-gallery', 'post_format' );
				add_post_meta($gallery_id, '_gismo_post_format_section_links', array (
					  0 => 'https://media.ldscdn.org/images/media-library/bible-images-the-life-of-jesus-christ/nearer-my-god-to-thee/jesus-miracle-healing-1617366-gallery.jpg',
					  1 => 'https://media.ldscdn.org/images/media-library/bible-images-the-life-of-jesus-christ/acts-of-the-apostles/peter-baptizes-cornelius-gentiles-1426793-gallery.jpg',
					  2 => 'https://media.ldscdn.org/images/media-library/bible-images-the-life-of-jesus-christ/acts-of-the-apostles/bible-videos-peter-1426810-gallery.jpg',
					  3 => 'https://media.ldscdn.org/images/media-library/bible-images-the-life-of-jesus-christ/teachings/pictures-of-jesus-1138494-gallery.jpg',
					  4 => 'https://media.ldscdn.org/images/media-library/bible-images-the-life-of-jesus-christ/teachings/jesus-christ-baptism-1402597-gallery.jpg'
				));
				
			}
			
		}
		
		/**
		 * Sets up theme defaults and registers support for various WordPress features.
		 *
		 * Note that this function is hooked into the after_setup_theme hook, which
		 * runs before the init hook. The init hook is too late for some features, such
		 * as indicating support for post thumbnails.
		 */
		public function gismo_setup() {
			/*
			 * Make theme available for translation.
			 * Translations can be filed in the /languages/ directory.
			 * If you're building a theme based on gismo, use a find and replace
			 * to change 'gismo' to the name of your theme in all the template files.
			 */
			load_theme_textdomain( 'gismo', get_template_directory() . '/languages' );
		
			// Add default posts and comments RSS feed links to head.
			add_theme_support( 'automatic-feed-links' );
		
			/*
			 * Let WordPress manage the document title.
			 * By adding theme support, we declare that this theme does not use a
			 * hard-coded <title> tag in the document head, and expect WordPress to
			 * provide it for us.
			 */
			add_theme_support( 'title-tag' );
		
			/*
			 * Enable support for Post Thumbnails on posts and pages.
			 *
			 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
			 */
			add_theme_support( 'post-thumbnails' );
		
			// This theme uses custom wp_nav_menu() menus.
			if(!empty($this->settings['menus'])){
				
				foreach($this->settings['menus'] as $key => $value){
					
					register_nav_menus( array(
						strtolower(str_replace(' ','-',$value['location'])) => esc_html__( $value['location'], 'gismo' ),
					) );	
				
				}
				
			}else{
				
				register_nav_menus( array(
					'primary' => esc_html__( 'Primary Location', 'gismo' ),
				) );	
				
			}
			
			register_nav_menus( array(
				'_gismo_mobile_menu' => esc_html__( 'Mobile Location', 'gismo' ),
			) );
		
			/*
			 * Switch default core markup for search form, comment form, and comments
			 * to output valid HTML5.
			 */
			add_theme_support( 'html5', array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
			) );
		
			// Set up the WordPress core custom background feature.
			add_theme_support( 'custom-background', apply_filters( 'gismo_custom_background_args', array(
				'default-color' => 'ffffff',
				'default-image' => '',
			) ) );
			
			add_theme_support( 'post-formats', array('image','quote','gallery','video'));
			
		}
		
		/**
		 * Set the content width in pixels, based on the theme's design and stylesheet.
		 *
		 * Priority 0 to make it available to lower priority callbacks.
		 *
		 * @global int $content_width
		 */
		public function gismo_content_width() {
			$GLOBALS['content_width'] = apply_filters( 'gismo_content_width', 640 );
		}
		
		public function gismo_save_settings() {
			
			$post = $_REQUEST;
			
			$nonce = $post['_nonce'];
			
			try{
				
				if(!wp_verify_nonce( $nonce, $post['action'] )){
					throw new Exceptio('You aren\'t who you said you were.');
				}
				//print_r($post['settings']);
				switch($post['action']){
					case 'save_menus':
						if(!isset($post['menus'])){
							$post['menus'] = [];
						}
						$msg = 'Menus updated.';
						$update = update_option('gismo_theme_menus',$post['menus']);
						break;
					case 'save_sidebars':
						if(!isset($post['sidebars'])){
							$post['sidebars'] = [];
						}
						$msg = 'Sidebars updated.';
						$update = update_option('gismo_theme_sidebars',$post['sidebars']);
						break;
					default:
						
						if(!isset($post['settings']['layout']['uikit']['js_components'])){
							$post['settings']['layout']['uikit']['js_components'] = [];
						}
						
						if(!isset($post['settings']['layout']['uikit']['css_components'])){
							$post['settings']['layout']['uikit']['css_components'] = [];
						}
						
						if(!isset($post['settings']['layout']['lazy_images'])){
							$post['settings']['layout']['lazy_images'] = 0;
						}
						$msg = 'Settings updated.';
						$update = update_option('gismo_theme_settings',$post['settings']);
						break;
				}
				
				if(!$update){
					throw new Exception('Failed to save.');
				}
			
				$result['error'] = false;
				$result['msg'] = '<span class="dashicons-before dashicons-yes"></span> ' . $msg;
				echo json_encode($result);
			
			}catch(Exception $err){
				
				$result['error'] = true;
				$result['msg'] = '<span class="dashicons-before dashicons-warning"></span> ' . $err->getMessage();
				echo json_encode($result);
				
			}
			
			die();
			
		}
		
		public function gismo_settings() {
			
			/** LAYOUT **/
			// FULL WIDTH OR CENTERED
			// VERTICAL SITE OPTION
			// LEFT PANEL SITE OPTION
			// MULTIPLE HOOKS
			// MULTIPLE NAV MENU OPTIONS AND PLACEMENT
			// UIKIT COMPONENTS AND THEME
			// FIXED SCROLLING HEADER
			// USE PERFECT SCROLLBAR OPTION
			// IMAGE SIZE OPTIONS
			// SIDEBAR LEFT OR RIGHT
			
			/** STYLE **/
			// LOGO
			// BACKGROUND
			// GOOGLE FONT OPTIONS
			// COLOR OPTIONS
			
			$menu_locations = get_registered_nav_menus();
			
			?>
            
            <div id="gismo-admin-panel">
            
            	<div class="uk-grid">
            		
                    <div class="uk-width-1-1">
                    
                        <nav class="uk-navbar">
                        	
                            <div class="uk-navbar-brand dashicons-before dashicons-welcome-widgets-menus"></div>
                            
                            <ul class="uk-navbar-nav main-navbar" data-uk-switcher="{connect:'#gismo-switcher'}">
                                <li><a href="" class="uk-navbar-nav uk-active">Layout</a></li>
                                <li><a href="" class="uk-navbar-nav">Menus</a></li>
                                <li><a href="" class="uk-navbar-nav">Sidebars</a></li>
                                <li><a href="" class="uk-navbar-nav">Integrations</a></li>
                            </ul>
                            
                        </nav>
                    
                    </div>
                    
                    <div class="uk-width-1-1 uk-margin-top">
                    
                        <ul id="gismo-switcher" class="uk-switcher">
                        	
                            <li class="uk-panel">
                            
                            	<form action="<?php echo esc_url( admin_url('admin-post.php') );?>" method="post">
                            	
                                <div class="uk-margin-bottom rd5 pad-1x" style="background-color:#f5f5f5;">
                                
                                    <div class="uk-grid uk-grid-width-1-3">
                                        
                                        <div class="media">
                                        
                                        	<h3>Site Logo</h3>
                                			
                                            <div class="hide-if-no-js">
                                                <a class="upload-media uk-button" href="#">Select Image</a>
                                                <a class="delete-media uk-button" href="#">Remove</a>
                                            </div>
                                            
                                            
                                            <div class="media-container uk-margin-top uk-margin-bottom" style="<?php echo(!empty($this->settings['style']['logo']['url']) && !empty($this->settings['style']['logo']['width']) ? 'width:' . $this->settings['style']['logo']['width'] . 'px;' : '');?><?php echo(!empty($this->settings['style']['logo']['url']) && !empty($this->settings['style']['logo']['height']) ? 'height:' . $this->settings['style']['logo']['height'] . 'px;' : '');?>">
                                                <?php echo(!empty($this->settings['style']['logo']['url']) ? '<img src="' . $this->settings['style']['logo']['url'] . '"/>' : '<div style="background-color:#fff; display:block; width:100%; height:100%; border:2px solid #d9d9d9; text-align:center;" class="rd5"><a href="'.admin_url('options-general.php').'"><h3 style="margin-top:20px; margin-bottom:0;">'.get_bloginfo('name').'</h3><h5 style="margin-top:0; font-size:12px;">'.get_bloginfo('description').'</h5></a><p>{No Logo Set}</p></div>');?>
                                            </div>
											
                                            
                                            <input class="media-url" name="settings[style][logo][url]" type="hidden" value="<?php echo(!empty($this->settings['style']['logo']['url']) ? $this->settings['style']['logo']['url'] : '');?>"/>
                                            
                                            <div class="uk-grid uk-grid-width-1-2">
                                                <div>
                                                	<label>Width</label>
                                                	<input name="settings[style][logo][width]" type="text" value="<?php echo(!empty($this->settings['style']['logo']['width']) ? $this->settings['style']['logo']['width'] : '300');?>"/>
                                                </div>
                                            	
                                                <div>
                                                	<label>Height</label>
                                            		<input name="settings[style][logo][height]" type="text" value="<?php echo(!empty($this->settings['style']['logo']['height']) ? $this->settings['style']['logo']['height'] : '100');?>"/>
                                            	</div>
                                            </div>
                                    
                                        </div>
                                        
                                        <div class="media">
                                        	
                                            <h3>Site Background</h3>
                                            
                                        	<div class="hide-if-no-js">
                                                <a class="upload-media uk-button" href="#">Select Image</a>
                                                <a class="delete-media uk-button" href="#">Remove</a>
                                            </div>
                                        
                                            <div class="media-container uk-margin-top site-background">
                                                <?php echo(!empty($this->settings['style']['background']['url']) ? '<div class="rd5" style="display:block; width:100%; height:170px; background:url(' . $this->settings['style']['background']['url'] . ') no-repeat center center; background-size:cover;"></div>' : '<div class="rd5" style="display:block; width:100%; height:165px; line-height:165px; background-color:#fff; text-align:center; font-size:18px; border:2px solid #d9d9d9;">{No Background Set}</div>');?>
                                            </div>
                                            
                                            <input class="media-url" name="settings[style][background][url]" type="hidden" value="<?php echo(!empty($this->settings['style']['background']['url']) ? $this->settings['style']['background']['url'] : '');?>"/>
                                            
                                        </div>
                                        	
                                        <div>
                                        	
                                            <h3>Site Theme</h3>
                                            
                                            <div>
												
												<label class="option2">
													<input type="radio" name="settings[style][theme]" value="default"<?php echo($this->settings['style']['theme'] == 'default' ? ' checked' : '');?>/>
													<span class="radio">Plain</span>
												</label>

												<label class="option2">
													<input type="radio" name="settings[style][theme]" value="minimal"<?php echo($this->settings['style']['theme'] == 'minimal' ? ' checked' : '');?>/>
													<span class="radio">Minimal</span>
												</label>

												<label class="option2">
													<input type="radio" name="settings[style][theme]" value="corporate"<?php echo($this->settings['style']['theme'] == 'corporate' ? ' checked' : '');?>/>
													<span class="radio">Corporate</span>
												</label>

												<label class="option2">
													<input type="radio" name="settings[style][theme]" value="zesty"<?php echo($this->settings['style']['theme'] == 'zesty' ? ' checked' : '');?>/>
													<span class="radio">Zesty</span>
												</label>

												<label class="option2">
													<input type="radio" name="settings[style][theme]" value="dark"<?php echo($this->settings['style']['theme'] == 'dark' ? ' checked' : '');?>/>
													<span class="radio">Dark</span>
												</label>

												<label class="option2">
													<input type="radio" name="settings[style][theme]" value="custom"<?php echo($this->settings['style']['theme'] == 'custom' ? ' checked' : '');?>/>
													<span class="radio">Custom</span>
												</label>
                                            
                                            </div>
											
											<h3>Site Color</h3>
                                            
                                            <div>
												
												<input type="text" name="settings[style][color]" value="<?php echo $this->settings['style']['color'];?>" class="color-field" >
                                            
                                            </div>
                                            
                                        </div>
										
                                    </div>
                                
                                </div>
                                
                                <div class="uk-margin-bottom rd5 pad-1x" style="background-color:#f5f5f5; display: none;">
                                	
                                	<div class="uk-grid uk-grid-width-1-2">
                                    
                                    	<div>
                                        	
                                			<h3>Site Type</h3>
                                            <div>
                                            <label class="option2">
                                                <input type="radio" name="settings[layout][orientation]" value="vertical"<?php echo($this->settings['layout']['orientation'] == 'vertical' ? ' checked' : '');?>/>
                                                <span class="radio">Vertical</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="radio" name="settings[layout][orientation]" value="vertical-center"<?php echo($this->settings['layout']['orientation'] == 'vertical-center' ? ' checked' : '');?>/>
                                                <span class="radio">Vertical Center</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="radio" name="settings[layout][orientation]" value="horizontal"<?php echo($this->settings['layout']['orientation'] == 'horizontal' ? ' checked' : '');?>/>
                                                <span class="radio">Horizontal</span>
                                            </label>
                                            </div>
                                            
                                            <h3>Header Position</h3>
                                            <div>
                                            <label class="option2">
                                                <input type="radio" name="settings[layout][header_position]" value="undocked"<?php echo($this->settings['layout']['header_position'] == 'undocked' ? ' checked' : '');?>/>
                                                <span class="radio">Undocked</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="radio" name="settings[layout][header_position]" value="docked"<?php echo($this->settings['layout']['header_position'] == 'docked' ? ' checked' : '');?>/>
                                                <span class="radio">Docked</span>
                                            </label>
                                            </div>
                                            
                                            <div id="header-config" style="margin-top:25px;<?php echo($this->settings['layout']['orientation'] == 'horizontal' ? 'display:none;' : '');?>">
                                            
                                            <h3>Header Configuration</h3>
                                            <div>
                                            <label class="option2">
                                                <input type="radio" name="settings[layout][header_config]" value="left"<?php echo($this->settings['layout']['header_config'] == 'left' ? ' checked' : '');?>/>
                                                <span class="radio">Left</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="radio" name="settings[layout][header_config]" value="center"<?php echo($this->settings['layout']['header_config'] == 'center' ? ' checked' : '');?>/>
                                                <span class="radio">Center</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="radio" name="settings[layout][header_config]" value="right"<?php echo($this->settings['layout']['header_config'] == 'right' ? ' checked' : '');?>/>
                                                <span class="radio">Right</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="radio" name="settings[layout][header_config]" value="stacked"<?php echo($this->settings['layout']['header_config'] == 'stacked' ? ' checked' : '');?>/>
                                                <span class="radio">Stacked</span>
                                            </label>
                                            </div>
                                            
                                            <div id="left-layout" class="header-layout uk-grid uk-margin-top"<?php echo($this->settings['layout']['header_config'] == 'left' ? '' : ' style="display:none;"');?>>
                                            
                                            	<div class="uk-width-1-4">
                                                	
                                                    <div class="logo-placeholder">Your Logo</div>
                                                    
                                                </div>
                                                
                                                <div class="uk-width-3-4">
                                                	
                                                    <div class="menu-placeholder" align="right">
                                                    	<ul><li>Home</li><li>Services</li><li>About</li><li>Pricing</li><li>Contact</li></ul>
                                                    </div>
                                                    
                                                </div>
                                            
                                            </div>
                                            
                                            <div id="center-layout" class="header-layout uk-grid uk-margin-top"<?php echo($this->settings['layout']['header_config'] == 'center' ? '' : ' style="display:none;"');?>>
                                            
                                            	<div class="uk-width-4-10">
                                                	
                                                    <div class="menu-placeholder">
                                                    	<ul><li>Home</li><li>Services</li><li>Pricing</li></ul>
                                                    </div>
                                                    
                                                </div>
                                                
                                                <div class="uk-width-2-10">
                                                	
                                                    <div class="logo-placeholder"></div>
                                                    
                                                </div>
                                                
                                                <div class="uk-width-4-10">
                                                
                                                	<div class="menu-placeholder">
                                                    	<ul><li>About</li><li>Work</li><li>Contact</li></ul>
                                                    </div>
                                                
                                                </div>
                                            
                                            </div>
                                            
                                            <div id="right-layout" class="header-layout uk-grid uk-margin-top"<?php echo($this->settings['layout']['header_config'] == 'right' ? '' : ' style="display:none;"');?>>
                                            
                                            	<div class="uk-width-3-4">
                                                	
                                                    <div class="menu-placeholder">
                                                    	<ul><li>Home</li><li>Services</li><li>About</li><li>Pricing</li><li>Contact</li></ul>
                                                    </div>
                                                    
                                                </div>
                                                
                                                <div class="uk-width-1-4">
                                                
                                                	<div class="logo-placeholder">Your Logo</div>
                                                
                                                </div>
                                            
                                            </div>
                                            
                                            <div id="stacked-layout" class="header-layout uk-grid uk-margin-top"<?php echo($this->settings['layout']['header_config'] == 'stacked' ? '' : ' style="display:none;"');?>>
                                            	
                                                <div class="uk-width-1-1">
                                                
                                                	<div class="logo-placeholder">Your Logo</div>
                                                
                                                </div>
                                                
                                            	<div class="uk-width-1-1">
                                                	
                                                    <div class="menu-placeholder">
                                                    	<ul><li>Home</li><li>Services</li><li>About</li><li>Pricing</li><li>Contact</li></ul>
                                                    </div>
                                                    
                                                </div>
                                                
                                            </div>
                                            
                                            </div>
                                            
                                        </div>
                                        
                                        <div>
                                        	
                                            <h3>Page Title</h3>
                                            <div>
                                            <label class="option2">
                                                <input type="radio" name="settings[layout][page_title]" value="default"<?php echo($this->settings['layout']['page_title'] == 'default' ? ' checked' : '');?>/>
                                                <span class="radio">Default</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="radio" name="settings[layout][page_title]" value="custom"<?php echo($this->settings['layout']['page_title'] == 'custom' ? ' checked' : '');?>/>
                                                <span class="radio">Custom</span>
                                            </label>
                                            </div>
                                           
                                            <h3>Blog Article Layout</h3>
                                            <div>
                                            <label class="option2">
                                                <input type="radio" name="settings[layout][blog][layout]" value="stacked"<?php echo($this->settings['layout']['blog']['layout'] == 'stacked' ? ' checked' : '');?>/>
                                                <span class="radio">Stacked</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="radio" name="settings[layout][blog][layout]" value="grid"<?php echo($this->settings['layout']['blog']['layout'] == 'grid' ? ' checked' : '');?>/>
                                                <span class="radio">Grid</span>
                                            </label>
                                            </div>
                                            
                                            <h3>Sidebar</h3>
                                            <div>
                                            <label class="option2">
                                                <input type="radio" name="settings[layout][blog][sidebar]" value="left"<?php echo($this->settings['layout']['blog']['sidebar'] == 'left' ? ' checked' : '');?>/>
                                                <span class="radio">Left</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="radio" name="settings[layout][blog][sidebar]" value="right"<?php echo($this->settings['layout']['blog']['sidebar'] == 'right' ? ' checked' : '');?>/>
                                                <span class="radio">Right</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="radio" name="settings[layout][blog][sidebar]" value="none"<?php echo($this->settings['layout']['blog']['sidebar'] == 'none' ? ' checked' : '');?>/>
                                                <span class="radio">None</span>
                                            </label>
                                            </div>
                                            
                                            <h3>Pagination</h3>
                                            <div>
                                            <label class="option2">
                                                <input type="radio" name="settings[layout][blog][paging]" value="simple"<?php echo($this->settings['layout']['blog']['paging'] == 'simple' ? ' checked' : '');?>/>
                                                <span class="radio">Simple</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="radio" name="settings[layout][blog][paging]" value="numbered"<?php echo($this->settings['layout']['blog']['paging'] == 'numbered' ? ' checked' : '');?>/>
                                                <span class="radio">Numbered</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="radio" name="settings[layout][blog][paging]" value="infinite"<?php echo($this->settings['layout']['blog']['paging'] == 'infinite' ? ' checked' : '');?>/>
                                                <span class="radio">Infinite</span>
                                            </label>
                                            </div>
                                        
                                        </div>
                                        
                                            
                                    </div>
                                            
                                </div>
                                
                                <div class="uk-margin-bottom rd5 pad-1x" style="background-color:#f5f5f5;">
                                	
                                	<div class="uk-grid uk-grid-width-1-3">
                                    
                                    	<div>
                                        
                                        	<h3>UIKit Theme</h3>
                                        	
                                            <div>
                                            <label class="option2">
                                                <input type="radio" name="settings[layout][uikit][theme]" value="default"<?php echo($this->settings['layout']['uikit']['theme'] == 'default' ? ' checked' : '');?>/>
                                                <span class="radio">Default</span>
                                            </label>
                                            </div>
                                            
                                            <div>
                                            <label class="option2">
                                                <input type="radio" name="settings[layout][uikit][theme]" value="almost-flat"<?php echo($this->settings['layout']['uikit']['theme'] == 'almost-flat' ? ' checked' : '');?>/>
                                                <span class="radio">Almost Flat</span>
                                            </label>
                                            </div>
                                            
                                            <div>
                                            <label class="option2">
                                                <input type="radio" name="settings[layout][uikit][theme]" value="gradient"<?php echo($this->settings['layout']['uikit']['theme'] == 'gradient' ? ' checked' : '');?>/>
                                                <span class="radio">Gradient</span>
                                            </label>
                                            </div> 
                                            
                                            <div class="uk-margin-top"><a href="https://getuikit.com/docs/customizer.html" target="_blank">https://getuikit.com/docs/customizer.html</a></div>
                                            
                                        </div>
                                        
                                        <div>
                                        
                                        	<h3>UIKit JS Components</h3>
                                        	
                                           <div class="group">
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][js_components][]" value="accordion"<?php echo(in_array('accordion',$this->settings['layout']['uikit']['js_components']) ? ' checked' : '');?>/>
                                                <span class="checkbox">Accordion</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][js_components][]" value="autocomplete"<?php echo(in_array('autocomplete',$this->settings['layout']['uikit']['js_components']) ? ' checked' : '');?>>
                                                <span class="checkbox">Auto Complete</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][js_components][]" value="datepicker"<?php echo(in_array('datepicker',$this->settings['layout']['uikit']['js_components']) ? ' checked' : '');?>>
                                                <span class="checkbox">Datepicker</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][js_components][]" value="form-password"<?php echo(in_array('form-password',$this->settings['layout']['uikit']['js_components']) ? ' checked' : '');?>>
                                                <span class="checkbox">Form Password</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][js_components][]" value="form-select"<?php echo(in_array('form-select',$this->settings['layout']['uikit']['js_components']) ? ' checked' : '');?>>
                                                <span class="checkbox">Form Select</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][js_components][]" value="grid-parallax"<?php echo(in_array('grid-parallax',$this->settings['layout']['uikit']['js_components']) ? ' checked' : '');?>>
                                                <span class="checkbox">Grid Parallax</span>
                                            </label>
                                            
                                            <label class="option2 grid-option" style="display:<?php echo($this->settings['layout']['blog']['layout'] != 'grid' ? 'inline-block' : 'none');?>;">
                                                <input type="checkbox" name="settings[layout][uikit][js_components][]" value="grid"<?php echo(in_array('grid',$this->settings['layout']['uikit']['js_components']) ? ' checked' : '');?>>
                                                <span class="checkbox">Grid</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][js_components][]" value="htmleditor"<?php echo(in_array('htmleditor',$this->settings['layout']['uikit']['js_components']) ? ' checked' : '');?>>
                                                <span class="checkbox">HTML Editor</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][js_components][]" value="lightbox"<?php echo(in_array('lightbox',$this->settings['layout']['uikit']['js_components']) ? ' checked' : '');?>>
                                                <span class="checkbox">Light Box</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][js_components][]" value="nestable"<?php echo(in_array('nestable',$this->settings['layout']['uikit']['js_components']) ? ' checked' : '');?>>
                                                <span class="checkbox">Nestable</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][js_components][]" value="notify"<?php echo(in_array('notify',$this->settings['layout']['uikit']['js_components']) ? ' checked' : '');?>>
                                                <span class="checkbox">Notify</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][js_components][]" value="pagination"<?php echo(in_array('pagination',$this->settings['layout']['uikit']['js_components']) ? ' checked' : '');?>>
                                                <span class="checkbox">Pagination</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][js_components][]" value="parallax"<?php echo(in_array('parallax',$this->settings['layout']['uikit']['js_components']) ? ' checked' : '');?>>
                                                <span class="checkbox">Parallax</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][js_components][]" value="search"<?php echo(in_array('search',$this->settings['layout']['uikit']['js_components']) ? ' checked' : '');?>>
                                                <span class="checkbox">Search</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][js_components][]" value="slider"<?php echo(in_array('slider',$this->settings['layout']['uikit']['js_components']) ? ' checked' : '');?>>
                                                <span class="checkbox">Slider</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][js_components][]" value="slideset"<?php echo(in_array('slideset',$this->settings['layout']['uikit']['js_components']) ? ' checked' : '');?>>
                                                <span class="checkbox">Slideset</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][js_components][]" value="slideshow"<?php echo(in_array('slideshow',$this->settings['layout']['uikit']['js_components']) ? ' checked' : '');?>>
                                                <span class="checkbox">Slideshow</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][js_components][]" value="sortable"<?php echo(in_array('sortable',$this->settings['layout']['uikit']['js_components']) ? ' checked' : '');?>>
                                                <span class="checkbox">Sortable</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][js_components][]" value="sticky"<?php echo(in_array('sticky',$this->settings['layout']['uikit']['js_components']) ? ' checked' : '');?>>
                                                <span class="checkbox">Sticky</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][js_components][]" value="timepicker"<?php echo(in_array('timepicker',$this->settings['layout']['uikit']['js_components']) ? ' checked' : '');?>>
                                                <span class="checkbox">Timepicker</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][js_components][]" value="tooltip"<?php echo(in_array('tooltip',$this->settings['layout']['uikit']['js_components']) ? ' checked' : '');?>>
                                                <span class="checkbox">Tooltip</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][js_components][]" value="upload"<?php echo(in_array('upload',$this->settings['layout']['uikit']['js_components']) ? ' checked' : '');?>>
                                                <span class="checkbox">Upload</span>
                                            </label>
                                            </div>
                                            
                                            <div class="uk-margin-top"><a href="https://getuikit.com/docs/components.html" target="_blank">https://getuikit.com/docs/components.html</a></div>
                                        	
                                        </div>
                                        
                                        <div>
                                        
                                        	<h3>UIKit CSS Components</h3>
                                        	
                                            <div class="group">
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][css_components][]" value="accordion"<?php echo(in_array('accordion',$this->settings['layout']['uikit']['css_components']) ? ' checked' : '');?>/>
                                                <span class="checkbox">Accordion</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][css_components][]" value="autocomplete"<?php echo(in_array('autocomplete',$this->settings['layout']['uikit']['css_components']) ? ' checked' : '');?>/>
                                                <span class="checkbox">Auto Complete</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][css_components][]" value="datepicker"<?php echo(in_array('datepicker',$this->settings['layout']['uikit']['css_components']) ? ' checked' : '');?>/>
                                                <span class="checkbox">Datepicker</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][css_components][]" value="dotnav"<?php echo(in_array('dotnav',$this->settings['layout']['uikit']['css_components']) ? ' checked' : '');?>/>
                                                <span class="checkbox">Dot Navigation</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][css_components][]" value="form-advanced"<?php echo(in_array('form-advanced',$this->settings['layout']['uikit']['css_components']) ? ' checked' : '');?>/>
                                                <span class="checkbox">Form Advanced</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][css_components][]" value="form-file"<?php echo(in_array('form-file',$this->settings['layout']['uikit']['css_components']) ? ' checked' : '');?>/>
                                                <span class="checkbox">Form File</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][css_components][]" value="form-password"<?php echo(in_array('form-password',$this->settings['layout']['uikit']['css_components']) ? ' checked' : '');?>/>
                                                <span class="checkbox">Form Password</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][css_components][]" value="form-select"<?php echo(in_array('form-select',$this->settings['layout']['uikit']['css_components']) ? ' checked' : '');?>/>
                                                <span class="checkbox">Form Select</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][css_components][]" value="htmleditor"<?php echo(in_array('htmleditor',$this->settings['layout']['uikit']['css_components']) ? ' checked' : '');?>/>
                                                <span class="checkbox">HTML Editor</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][css_components][]" value="nestable"<?php echo(in_array('nestable',$this->settings['layout']['uikit']['css_components']) ? ' checked' : '');?>/>
                                                <span class="checkbox">Nestable</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][css_components][]" value="notify"<?php echo(in_array('notify',$this->settings['layout']['uikit']['css_components']) ? ' checked' : '');?>/>
                                                <span class="checkbox">Notify</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][css_components][]" value="placeholder"<?php echo(in_array('placeholder',$this->settings['layout']['uikit']['css_components']) ? ' checked' : '');?>/>
                                                <span class="checkbox">Placeholder</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][css_components][]" value="progress"<?php echo(in_array('progress',$this->settings['layout']['uikit']['css_components']) ? ' checked' : '');?>/>
                                                <span class="checkbox">Progress</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][css_components][]" value="search"<?php echo(in_array('search',$this->settings['layout']['uikit']['css_components']) ? ' checked' : '');?>/>
                                                <span class="checkbox">Search</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][css_components][]" value="slider"<?php echo(in_array('slider',$this->settings['layout']['uikit']['css_components']) ? ' checked' : '');?>/>
                                                <span class="checkbox">Slider</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][css_components][]" value="slidenav"<?php echo(in_array('slidenav',$this->settings['layout']['uikit']['css_components']) ? ' checked' : '');?>/>
                                                <span class="checkbox">Slide Navigation</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][css_components][]" value="slideshow"<?php echo(in_array('slideshow',$this->settings['layout']['uikit']['css_components']) ? ' checked' : '');?>/>
                                                <span class="checkbox">Slideshow</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][css_components][]" value="sortable"<?php echo(in_array('sortable',$this->settings['layout']['uikit']['css_components']) ? ' checked' : '');?>/>
                                                <span class="checkbox">Sortable</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][css_components][]" value="sticky"<?php echo(in_array('sticky',$this->settings['layout']['uikit']['css_components']) ? ' checked' : '');?>/>
                                                <span class="checkbox">Sticky</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][css_components][]" value="tooltip"<?php echo(in_array('tooltip',$this->settings['layout']['uikit']['css_components']) ? ' checked' : '');?>/>
                                                <span class="checkbox">Tooltip</span>
                                            </label>
                                            
                                            <label class="option2">
                                                <input type="checkbox" name="settings[layout][uikit][js_components][]" value="upload"<?php echo(in_array('upload',$this->settings['layout']['uikit']['css_components']) ? ' checked' : '');?>/>
                                                <span class="checkbox">Upload</span>
                                            </label>
                                            </div>
                                            
                                        </div>
                                        
                                    </div>
                                
                                </div>
                                
                                <div>
                                    
                                    <a class="uk-button uk-button-large uk-button-primary save-btn">Save</a>
                                
                                </div>
                                
                                <input type="hidden" name="action" value="save_settings"/>
                            	<input type="hidden" name="_nonce" value="<?php echo wp_create_nonce('save_settings');?>"/>
                                
                                </form>
                                
                            </li>
                                  
                            <li>
                            	
                                <?php $size = (sizeof($this->settings['menus']) + 1);?>
                                
                                <div class="add-wrapper add-theme-location uk-margin-bottom rd5 pad-1x" style="background-color:#f5f5f5;">
                                
                                	<h3>New Theme Menu Location</h3>
                                
                                    <div class="uk-grid uk-grid-width-1-4">
                                        
                                        <div>
                                            
                                            <label>Name</label>
                                            <input type="text" class="input" name="menus[<?php echo $size;?>][location]" value=""/>
                                            
                                        </div>
                                        
                                        <div>
                                            
                                            <label>Theme Hook</label>
                                            <div class="select">
                                                <select class="input" name="menus[<?php echo $size;?>][hook]">
                                                    <option value="">Select…</option>
                                                    <option value="gismo_before_header">Before Header</option>
                                                    <option value="gismo_after_header">After Header</option>
                                                    <option value="gismo_before_logo">Before Logo</option>
                                                    <option value="gismo_after_logo">After Logo</option>
                                                    <option value="gismo_primary_navigation">Primary Navigation</option>
                                                    <option value="gismo_after_primary_navigation">After Primary Navigation</option>
                                                    <option value="gismo_secondary_navigation">Secondary Navigation</option>
                                                    <option value="gismo_sidebar">Sidebar</option>
													<option value="gismo_before_blog">Before Blog</option>
													<option value="gismo_before_content">Before Content</option>
													<option value="gismo_after_content">After Content</option>
                                                    <option value="gismo_before_footer">Before Footer</option>
                                                    <option value="gismo_after_footer">After Footer</option>
                                                </select>
                                            </div>
                                            
                                        </div>
                                        
                                        <div>
                                            
                                            <label>Column Width</label>
                                            <div class="select">
                                                <select class="input" name="menus[<?php echo $size;?>][column]">
                                                    <option value="">Select…</option>
                                                    <option value="1-1">100%</option>
                                                    <option value="5-6">84%</option>
                                                    <option value="4-5">80%</option>
                                                    <option value="3-4">75%</option>
                                                    <option value="2-3">67%</option>
                                                    <option value="1-2">50%</option>
                                                    <option value="1-3">33%</option>
                                                    <option value="1-4">25%</option>
                                                    <option value="1-5">20%</option>
                                                    <option value="1-6">16%</option>
                                                </select>
                                            </div>
                                            
                                        </div>
                                        
                                        <div>
                                            
                                            <label>Menu Container Class</label>
                                            <input type="text" class="input" name="menus[<?php echo $size;?>][class]" value=""/>
                                            
                                        </div>
                                        
                                        <div class="uk-margin-top">
                                            
                                            <a class="uk-button add-btn">Add Menu</a>
                                            
                                        </div>
                                        
                                    </div>
                                    
                                </div>
                                
                                <form id="menus-form" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
                                
                                <?php if(!empty($this->settings['menus'])):?>
                                
									<?php foreach($this->settings['menus'] as $key => $value):?>
                                    
                                    <div class="item uk-margin-bottom pad-1x rd5" style="border:2px solid #eee;">
                                        
                                        <div class="uk-grid uk-grid-width-1-4">
                                            
                                            <div>
                                                
                                                <label>Name</label>
                                                <input type="text" class="input" name="menus[<?php echo $key;?>][location]" value="<?php echo ucwords(str_replace('-',' ',$value['location']));?>"/>
                                                
                                            </div>
                                            
                                            <div>
                                                
                                                <label>Theme Hook</label>
                                                <div class="select">
                                                    <select class="input" name="menus[<?php echo $key;?>][hook]">
                                                        <option value="">Select…</option>
                                                        <option value="gismo_before_header"<?php echo($value['hook'] == 'gismo_before_header' ? ' selected' : '');?>>Before Header</option>
                                                        <option value="gismo_after_header"<?php echo($value['hook'] == 'gismo_after_header' ? ' selected' : '');?>>After Header</option>
                                                        <option value="gismo_before_logo"<?php echo($value['hook'] == 'gismo_before_logo' ? ' selected' : '');?>>Before Logo</option>
                                                        <option value="gismo_after_logo"<?php echo($value['hook'] == 'gismo_after_logo' ? ' selected' : '');?>>After Logo</option>
                                                        <option value="gismo_primary_navigation"<?php echo($value['hook'] == 'gismo_primary_navigation' ? ' selected' : '');?>>Primary Navigation</option>
                                                        <option value="gismo_after_primary_navigation"<?php echo($value['hook'] == 'gismo_after_primary_navigation' ? ' selected' : '');?>>After Primary Navigation</option>
                                                        <option value="gismo_secondary_navigation"<?php echo($value['hook'] == 'gismo_secondary_navigation' ? ' selected' : '');?>>Secondary Navigation</option>
                                                        <option value="gismo_sidebar"<?php echo($value['hook'] == 'gismo_sidebar' ? ' selected' : '');?>>Sidebar</option>
														<option value="gismo_before_blog"<?php echo($value['hook'] == 'gismo_before_blog' ? ' selected' : '');?>>Before Blog</option>
														<option value="gismo_before_content"<?php echo($value['hook'] == 'gismo_before_content' ? ' selected' : '');?>>Before Content</option>
														<option value="gismo_after_content"<?php echo($value['hook'] == 'gismo_after_content' ? ' selected' : '');?>>After Content</option>
                                                        <option value="gismo_before_footer"<?php echo($value['hook'] == 'gismo_before_footer' ? ' selected' : '');?>>Before Footer</option>
                                                        <option value="gismo_after_footer"<?php echo($value['hook'] == 'gismo_after_footer' ? ' selected' : '');?>>After Footer</option>
                                                    </select>
                                                </div>
                                                
                                            </div>
                                            
                                            <div>
                                                
                                                <label>Column Width</label>
                                                <div class="select">
                                                    <select class="input" name="menus[<?php echo $key;?>][column]">
                                                        <option value="">Select…</option>
                                                        <option value="1-1"<?php echo($value['column'] == '1-1' ? ' selected' : '');?>>100%</option>
                                                        <option value="5-6"<?php echo($value['column'] == '5-6' ? ' selected' : '');?>>84%</option>
                                                        <option value="4-5"<?php echo($value['column'] == '4-5' ? ' selected' : '');?>>80%</option>
                                                        <option value="3-4"<?php echo($value['column'] == '3-4' ? ' selected' : '');?>>75%</option>
                                                        <option value="2-3"<?php echo($value['column'] == '2-3' ? ' selected' : '');?>>67%</option>
                                                        <option value="1-2"<?php echo($value['column'] == '1-2' ? ' selected' : '');?>>50%</option>
                                                        <option value="1-3"<?php echo($value['column'] == '1-3' ? ' selected' : '');?>>33%</option>
                                                        <option value="1-4"<?php echo($value['column'] == '1-4' ? ' selected' : '');?>>25%</option>
                                                        <option value="1-5"<?php echo($value['column'] == '1-5' ? ' selected' : '');?>>20%</option>
                                                        <option value="1-6"<?php echo($value['column'] == '1-6' ? ' selected' : '');?>>16%</option>
                                                    </select>
                                                </div>
                                                
                                            </div>
                                            
                                            <div>
                                                
                                                <label>Menu Container Class</label>
                                                <input type="text" class="input" name="menus[<?php echo $key;?>][class]" value="<?php echo $value['class'];?>"/>
                                                
                                            </div>
                                            
                                            <div class="uk-push-2-3 uk-margin-top" align="right">
                                                
                                                <a class="uk-button remove-btn">Remove</a>
                                                
                                            </div>
                                            
                                        </div>
                                        
                                    </div>
                                    
                                    <?php endforeach;?>
                                    
                                <?php endif;?>
                                
                                <input type="hidden" name="action" value="save_menus"/>
                                <input type="hidden" name="_nonce" value="<?php echo wp_create_nonce('save_menus');?>"/>
                                <a class="uk-button uk-button-large uk-button-primary save-btn" style="display:<?php echo(!empty($this->settings['menus']) ? 'inline-block' : 'none');?>;">Save</a>
                                
                                </form>
                                
                            </li>
                            <li>
                            
                            	<?php $size = (sizeof($this->settings['sidebars']) + 1);?>
                            	
                                <div class="add-wrapper add-theme-sidebar uk-margin-bottom rd5 pad-1x" style="background-color:#f5f5f5;">
                                
                                	<h3>New Sidebar</h3>
                                
                                    <div class="uk-grid uk-grid-width-1-3">
                                        
                                        <div>
                                            
                                            <label>Name</label>
                                            <input type="text" class="input" name="sidebars[<?php echo $size;?>][name]" value=""/>
                                            
                                        </div>
                                        
                                        <div>
                                            
                                            <label>Theme Hook</label>
                                            <div class="select">
                                                <select class="input" name="sidebars[<?php echo $size;?>][hook]">
                                                    <option value="">Select…</option>
                                                    <option value="gismo_before_header">Before Header</option>
                                                    <option value="gismo_after_header">After Header</option>
                                                    <option value="gismo_before_logo">Before Logo</option>
                                                    <option value="gismo_after_logo">After Logo</option>
                                                    <option value="gismo_primary_navigation">Primary Navigation</option>
                                                    <option value="gismo_after_primary_navigation">After Primary Navigation</option>
                                                    <option value="gismo_secondary_navigation">Secondary Navigation</option>
                                                    <option value="gismo_sidebar">Sidebar</option>
													<option value="gismo_before_blog">Before Blog</option>
													<option value="gismo_before_content">Before Content</option>
													<option value="gismo_after_content">After Content</option>
                                                    <option value="gismo_before_footer">Before Footer</option>
                                                    <option value="gismo_after_footer">After Footer</option>
                                                </select>
                                            </div>
                                            
                                        </div>
                                        
                                        <div>
                                            
                                            <label>Sidebar Class</label>
                                            <input type="text" class="input" name="sidebars[<?php echo $size;?>][class]" value=""/>
                                            
                                        </div>
                                        
                                        <div class="uk-margin-top">
                                            
                                            <label>Description</label>
                                            <input type="text" class="input" name="sidebars[<?php echo $size;?>][description]" value=""/>
                                            
                                        </div>
                                        
                                        <div class="uk-margin-top">
                                            
                                            <label>Title Class</label>
                                            <input type="text" class="input" name="sidebars[<?php echo $size;?>][title_class]" value=""/>
                                            
                                        </div>
                                        
                                        <div class="uk-margin-top">
                                            
                                            <label>Widget Class</label>
                                            <input type="text" class="input" name="sidebars[<?php echo $size;?>][widget_class]" value=""/>
                                            
                                        </div>
                                        
                                        <div class="uk-margin-top">
                                            
                                            <a class="uk-button add-btn">Add Sidebar</a>
                                            
                                        </div>
                                        
                                    </div>
                                    
                                </div>
                                
                                <form id="sidebars-form" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
                                
                                <?php if(!empty($this->settings['sidebars'])):?>
                                
									<?php foreach($this->settings['sidebars'] as $key => $value):?>
                                    
                                    <div class="item uk-margin-bottom pad-1x rd5" style="border:2px solid #eee;">
                                        
                                        <div class="uk-grid uk-grid-width-1-3">
                                            
                                            <div>
                                                
                                                <label>Name</label>
                                                <input type="text" class="input" name="sidebars[<?php echo $key;?>][name]" value="<?php echo ucwords(str_replace('-',' ',$value['name']));?>"/>
                                                
                                            </div>
                                            
                                            <div>
                                                
                                                <label>Theme Hook</label>
                                                <div class="select">
                                                    <select class="input" name="sidebars[<?php echo $key;?>][hook]">
                                                        <option value="">Select…</option>
                                                        <option value="gismo_before_header"<?php echo($value['hook'] == 'gismo_before_header' ? ' selected' : '');?>>Before Header</option>
                                                        <option value="gismo_after_header"<?php echo($value['hook'] == 'gismo_after_header' ? ' selected' : '');?>>After Header</option>
                                                        <option value="gismo_before_logo"<?php echo($value['hook'] == 'gismo_before_logo' ? ' selected' : '');?>>Before Logo</option>
                                                        <option value="gismo_after_logo"<?php echo($value['hook'] == 'gismo_after_logo' ? ' selected' : '');?>>After Logo</option>
                                                        <option value="gismo_primary_navigation"<?php echo($value['hook'] == 'gismo_primary_navigation' ? ' selected' : '');?>>Primary Navigation</option>
                                                        <option value="gismo_after_primary_navigation"<?php echo($value['hook'] == 'gismo_after_primary_navigation' ? ' selected' : '');?>>After Primary Navigation</option>
                                                        <option value="gismo_secondary_navigation"<?php echo($value['hook'] == 'gismo_secondary_navigation' ? ' selected' : '');?>>Secondary Navigation</option>
                                                        <option value="gismo_sidebar"<?php echo($value['hook'] == 'gismo_sidebar' ? ' selected' : '');?>>Sidebar</option>
														<option value="gismo_before_blog"<?php echo($value['hook'] == 'gismo_before_blog' ? ' selected' : '');?>>Before Blog</option>
														<option value="gismo_before_content"<?php echo($value['hook'] == 'gismo_before_content' ? ' selected' : '');?>>Before Content</option>
														<option value="gismo_after_content"<?php echo($value['hook'] == 'gismo_after_content' ? ' selected' : '');?>>After Content</option>
                                                        <option value="gismo_before_footer"<?php echo($value['hook'] == 'gismo_before_footer' ? ' selected' : '');?>>Before Footer</option>
                                                        <option value="gismo_after_footer"<?php echo($value['hook'] == 'gismo_after_footer' ? ' selected' : '');?>>After Footer</option>
                                                    </select>
                                                </div>
                                                
                                            </div>
                                            
                                            <div>
                                                
                                                <label>Sidebar Class</label>
                                                <input type="text" class="input" name="sidebars[<?php echo $key;?>][class]" value="<?php echo $value['class'];?>"/>
                                                
                                            </div>
                                            
                                            <div class="uk-margin-top">
                                                
                                                <label>Description</label>
                                                <input type="text" class="input" name="sidebars[<?php echo $key;?>][description]" value="<?php echo ucwords(str_replace('-',' ',$value['description']));?>"/>
                                                
                                            </div>
                                            
                                            <div class="uk-margin-top">
                                                
                                                <label>Title Class</label>
                                                <input type="text" class="input" name="sidebars[<?php echo $key;?>][title_class]" value="<?php echo $value['title_class'];?>"/>
                                                
                                            </div>
                                            
                                            <div class="uk-margin-top">
                                                
                                                <label>Widget Class</label>
                                                <input type="text" class="input" name="sidebars[<?php echo $key;?>][widget_class]" value="<?php echo $value['widget_class'];?>"/>
                                                
                                            </div>
                                            
                                            <div class="uk-push-2-3 uk-margin-top" align="right">
                                                
                                                <a class="uk-button remove-btn">Remove</a>
                                                
                                            </div>
                                            
                                        </div>
                                        
                                    </div>
                                    
                                    <?php endforeach;?>
                                    
                                <?php endif;?>
                             
                                    <input type="hidden" name="action" value="save_sidebars">
                                	<input type="hidden" name="_nonce" value="<?php echo wp_create_nonce('save_sidebars');?>"/>
                                    <a class="uk-button uk-button-large uk-button-primary save-btn" style="display:<?php echo(!empty($this->settings['sidebars']) ? 'inline-block' : 'none');?>;">Save</a>
                                
                                </form>
                                
                            </li>
                            <li>Integrations Details</li>
                        </ul>
                    
                    </div>
                
                </div>
            
            </div>
            
            <?php
			
		}
		
		public function add_menu() {
			
			add_menu_page('Theme Settings', 'Theme Settings', 'manage_options', 'gismo-settings', array($this, 'gismo_settings'), 'dashicons-welcome-widgets-menus', 61);
			
		}
		
		public function admin_styles() {
			
			$current_page = (isset($_GET['page']) ? $_GET['page'] : false);
			$screen = get_current_screen();
			
			if($current_page == 'gismo-settings'){
				
				wp_enqueue_style('wp-color-picker');
				wp_enqueue_style('gismo-uikit-style', get_template_directory_uri() . '/js/uikit-2.27.2/css/uikit.gradient.min.css');
				wp_enqueue_style('gismo-uikit-notify-style', get_template_directory_uri() . '/js/uikit-2.27.2/css/components/notify.gradient.min.css');
				wp_enqueue_style('gismo-admin-style', get_template_directory_uri() . '/css/admin.css');
				
			}
			
		}
		
		public function admin_scripts() {
			
			$current_page = (isset($_GET['page']) ? $_GET['page'] : false);
			$screen = get_current_screen();

			// Gonna need jQuery
			wp_enqueue_media();
			wp_enqueue_script('jquery');
			wp_enqueue_script('jquery-ui-sortable');
			wp_enqueue_script('wp-color-picker');
			
			wp_enqueue_script('gismo-uikit', get_template_directory_uri() . '/js/uikit-2.27.2/js/uikit.min.js', array('jquery'), '2.27.2', true);
			wp_enqueue_script('gismo-uikit-notify', get_template_directory_uri() . '/js/uikit-2.27.2/js/components/notify.min.js', array('gismo-uikit'), '2.27.2', true);
			wp_enqueue_script('admin-style', get_template_directory_uri() . '/js/admin.js', array('jquery'), '2016.11.24', true);
			
			
		}
		
		//*Adds a box to the main column on the Post and Page edit screens.

		public function gismo_post_format_section() {
		
			$screens = array( 'post' );
			
			foreach ( $screens as $screen ) {

				add_meta_box(

					'post_format_section_id',

					__( 'Post Format Section', 'post_format_section_textdomain' ),

					array($this,'gismo_post_format_section_callback'),

					$screen

				);

			}
			
		}
		
		public function gismo_post_format_section_callback( $post ) {
		
			wp_nonce_field( 'post_format_section_data', 'post_format_section_nonce' );
			
			$links = get_post_meta( $post->ID, '_gismo_post_format_section_links', true );
		
			ob_start();
			
			?>
			
			<?php if(!empty($links)):?>

				<?php foreach($links as $link):?>
				<div class="custom-image-select" style="display: flex;">
					<div style="width: 85%; min-width: 200px;">
						<input style="width:80%; margin-top:10px;" type="text" name="post_format_section_links[]" value="<?php echo $link;?>" placeholder="Paste Image URL or Select Image"/>
						<a class="remove-custom-image" style="display: none; cursor: pointer;">Remove</a>
					</div>
					<div>
						<button style="margin:9px 0 0 5px;" class="set_custom_image button">Select Image</button>
					</div>
				</div>
				<?php endforeach;?>

			<?php else:?>
				
				<div class="custom-image-select" style="display: flex;">
					<div style="width: 85%; min-width: 200px;">
						<input style="width:80%; margin-top:10px;" type="text" name="post_format_section_links[]" value="" placeholder="Paste Image URL or Select Image"/>
						<a class="remove-custom-image" style="display: none; cursor: pointer;">Remove</a>
					</div>
					<div>
						<button style="margin:9px 0 0 5px;" class="set_custom_image button">Select Image</button>
					</div>
				</div>

			<?php endif;?>

			<div class="add-custom-image" style="display: none; margin-top: 20px;">
				<a style="cursor: pointer;">+ Add Image</a>	
			</div>
				
			<script type="text/javascript">

			jQuery(function($){
				
				$('#post_format_section_id').prependTo('#advanced-sortables');
				
				$('#advanced-sortables').prependTo('#postbox-container-2');
				
				if($('#side-sortables > #formatdiv').is(':visible')){
					
					switch($('input[name="post_format"]:checked').val()){
						case 'image':
						case 'video':
							$('#wp-content-wrap').addClass('block-editor');
							$('#postdivrich').find('textarea').prop('disabled',true);
							break;
						case 'gallery':
							$('#wp-content-wrap').addClass('block-editor');
							$('#postdivrich').find('textarea').prop('disabled',true);
							$('.add-custom-image').show();
							break;
						default:
							$('#post_format_section_id').hide();
							break;
					}
					
				}
				
				$('input[name="post_format"]').on('change',function(){
					val = $(this).val();
					switch(val){
						case 'image':
						case 'video':
							$('.add-custom-image').hide();
							$('#wp-content-wrap').addClass('block-editor');
							$('#postdivrich').find('textarea').prop('disabled',true);
							$('#post_format_section_id').show();
							break;
						case 'gallery':
							$('.add-custom-image').show();
							$('#wp-content-wrap').addClass('block-editor');
							$('#postdivrich').find('textarea').prop('disabled',true);
							$('#post_format_section_id').show();
							break;
						default:
							$('.add-custom-image').hide();
							$('#wp-content-wrap').removeClass('block-editor');
							$('#postdivrich').find('textarea').removeAttr('disabled');
							$('#post_format_section_id').hide();
					}
				});
				
				$('.add-custom-image > a').on('click',function(){
					
					var $this = $(this);
					
					var clone = $('.custom-image-select').last().clone();
					
					clone.find('input').val('');
					
					$this.parent().before(clone);
					
				});
				
				$('#post_format_section_id').on('click','.remove-custom-image',function(){
					
					var $this = $(this);
					
					if($('.custom-image-select').length > 1){
						$this.closest('.custom-image-select').detach();
					}
					
				});
				
				if ($('.set_custom_image').length > 0) {

					if ( typeof wp !== "undefined" && wp.media && wp.media.editor) {

						$('#post_format_section_id').on('click', '.set_custom_image', function(e) {

							e.preventDefault();

							var button = $(this);

							var id = button.closest('.custom-image-select').find('input');

							wp.media.editor.send.attachment = function(props, attachment) {

								id.val(attachment.url);

							};

							wp.media.editor.open(button);

							return false;

						});

					}

				};
				
				$('#post_format_section_id .inside').sortable({
					items : '.custom-image-select'
				});

			});

			</script>

			<style type="text/css">
				.block-editor:before{
					content:'Editor has been disabled, please use the Post Format Section.';
					width: 100%;
					height: 100%;
					display: block;
					position: absolute;
					top: 0;
					left: 0;
					background: rgb(0, 0, 0) !important;
					background: rgba(0, 0, 0, 0.8) !important;
					filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=#99000000, endColorstr=#99000000);
					-ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr=#99000000, endColorstr=#99000000)";
					z-index: 999999;
					color: #fff;
					text-align: center;
					vertical-align: middle;
					font-size: 18px;
					padding-top: 25%;
					box-sizing: border-box;
				}
				
				.custom-image-select > div{
					position: relative;
				}
				
				.custom-image-select + .custom-image-select > div:hover .remove-custom-image{
					display: block !important;
					position: absolute;
					top: 14px;
					right: 5px;
				}
			</style>

			<?php
			
			ob_end_flush();
		
		}
		
		public function gismo_post_format_section_save( $post_id ) {
		//print_r($_POST);
			if ( ! isset( $_POST['post_format_section_nonce'] ) ) {
		
				return;
		
			}
		
			if ( ! wp_verify_nonce( $_POST['post_format_section_nonce'], 'post_format_section_data' ) ) {
		
				return;
		
			}
		
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		
				return;
		
			}
		
			if ( isset( $_POST['post_type'] ) && 'post' == $_POST['post_type'] ) {
		
				if ( ! current_user_can( 'edit_page', $post_id ) ) {
		
					return;
		
				}
		
			} else {
		
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
		
					return;
		
				}
		
			}
		
			if ( ! isset( $_POST['post_format_section_links'] ) ) {
		
				return;
		
			}
			//print_r($_POST['post_format_section_links']);
			update_post_meta( $post_id, '_gismo_post_format_section_links', $_POST['post_format_section_links'] );
		
		}
		
	}
	
}