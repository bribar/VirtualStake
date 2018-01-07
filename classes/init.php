<?php

/** HOOKS **/
// gismo_before_header
// gismo_after_header
// gismo_before_logo
// gismo_after_logo
// gismo_primary_navigation
// gismo_secondary_navigation
// gismo_sidebar
// gismo_before_footer
// gismo_after_footer

/** FILTERS **/
// gismo_site_header_classes
// gismo_content_classes
// gismo_footer_classes

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Gismo' ) ) {

	class Gismo{
		
		public $settings;
		public $wp_query;
		
		public function __construct() {
			
			$this->settings = get_option('gismo_theme_settings',false);
			$this->settings['menus'] = get_option('gismo_theme_menus',false);
			$this->settings['sidebars'] = get_option('gismo_theme_sidebars',false);
			$GLOBALS['gismo_theme_settings'] = $this->settings;
			
			// Register action and filter hooks
			
			add_action('wp', array($this,'gismo_wp'));
			add_action('widgets_init', array($this,'gismo_widgets_init'));
			add_action('after_switch_theme', array($this,'gismo_switch_theme'));
			add_action('after_setup_theme', array($this,'gismo_setup'));
			add_action('after_setup_theme', array($this,'gismo_content_width'));
			add_action('wp_enqueue_scripts', array($this,'gismo_scripts'));
			
			add_action('wp_ajax_gismo_ajax_load_more', array($this,'gismo_ajax_load_more'));
			add_action('wp_ajax_nopriv_gismo_ajax_load_more', array($this,'gismo_ajax_load_more'));
			
			add_action('add_meta_boxes', array($this,'gismo_custom_header_section'), 10);
			add_action('save_post', array($this,'gismo_custom_header_section_save'), 10);
			add_action('gismo_after_header', array($this,'gismo_add_custom_header_section'), 10);
			
			add_action('add_meta_boxes', array($this,'gismo_page_elements'), 10);
			add_action('save_post', array($this,'gismo_page_elements_save'), 10);
			add_action('wp_head', array($this,'gismo_add_page_elements_header'), 10);
			add_action('wp_footer', array($this,'gismo_add_page_elements_footer'), 10);
			add_filter('body_class', array($this,'gismo_body_classes'));
			add_filter('excerpt_length', array($this,'gismo_custom_excerpt_length'), 999);
			add_filter('excerpt_more', array($this,'gismo_new_excerpt_more'));
			
			remove_shortcode('gallery');
			add_shortcode('gallery', array($this,'gismo_gallery'));
 		
			if(!empty($this->settings['menus'])){
				
				foreach($this->settings['menus'] as $key => $value){
					
					add_action($value['hook'], function() use ($key){
						
						$location = strtolower(str_replace(' ','-',$this->settings['menus'][$key]['location']));
						$menu_id = $location;
						wp_nav_menu( array('theme_location' => $location, 'menu_id' => 'gismo-' . $menu_id, 'container_class' => $this->settings['menus'][$key]['class']));
							
					});
				
				}
				
			}else{
				
				add_action('gismo_primary_navigation', function(){
					
					wp_nav_menu( array('theme_location' => 'primary', 'menu_id' => 'gismo-primary-menu', 'container_class' => ($this->settings['layout']['orientation'] == 'horizontal' ? '' : 'inline-menu')));
						
				});
				
			}
			
			add_action('gismo_primary_navigation', function(){
				
				echo '<div id="gismo-mobile-menu-wrapper" class="uk-offcanvas"><div class="uk-offcanvas-bar">';
				
				wp_nav_menu( array('theme_location' => '_gismo_mobile_menu', 'menu_id' => 'gismo-mobile-menu', 'container_class' => ''));
				
				echo '</div></div>';
				echo '<a href="#gismo-mobile-menu-wrapper" class="mobile-menu-icon" style="margin:0 auto;" data-uk-offcanvas><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 14 14" width="16px" height="16px"><rect y="10.5" width="14" height="3" style="fill:#757575"/><rect y="5.5" width="14" height="3" style="fill:#757575"/><rect y="0.5" width="14" height="3" style="fill:#757575"/></svg></a>';
					
			});
			
		}
		
		/**
		 * Init.
		 */
		public function gismo_wp() {
			
			global $wp_query;
			$this->wp_query = $wp_query;
			//print_r($wp_query);
			
		}
		
		public function gismo_new_excerpt_more( $more ) {
			return '...';
		}
		
		public function gismo_custom_excerpt_length( $length ) {
			return 28;
		}
		
		public function gismo_ajax_load_more() {
			
		    check_ajax_referer( 'gismo-load-more-nonce', 'nonce' );
    
			$args = isset( $_POST['query'] ) ? array_map( 'esc_attr', $_POST['query'] ) : array();
			$args['post_type'] = isset( $args['post_type'] ) ? esc_attr( $args['post_type'] ) : 'post';
			$args['paged'] = esc_attr( $_POST['page'] );
			$args['post_status'] = 'publish';
			$next_page = $args['paged'] + 1;
			
			ob_start();
			
			$loop = new WP_Query($args);
			if($loop->have_posts()): 
			
				while($loop->have_posts()): $loop->the_post();
			
					get_template_part( 'template-parts/content', get_post_format() );
			
				endwhile;
				
			endif;
			
			wp_reset_postdata();
			
			$return['success'] = true;
			if($next_page <= $loop->max_num_pages):
				$return['fetcher'] = '<div class="fetch-more" data-page="'.($args['paged'] + 1).'"/>';
			else:
				$return['fetcher'] = '';
			endif;
			
			$return['data'] = ob_get_clean();
		  
		    wp_send_json($return);
		    wp_die();
		  
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
					'theme'      => 'minimal' // minimal, custom
				],
				'layout' => [
					'orientation'       => 'horizontal', // vertical, vertical-center, horizontal
					'header_position'   => 'undocked',
					'header_config'     => 'left',
					'page_title'        => 'default',
					'lazy_images'       => 0,
					'uikit'             => [
						'theme'          => 'default', // almost-flat, gradient
						'css_components' => [],
						'js_components'  => []
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
				'black-studio-tinymce-widget',
				'visual-form-builder',
				'disqus-comment-system',
				'envira-gallery-lite',
				'addthis',
				'simple-social-icons',
				'wordpress-seo',
				'the-events-calendar',
				'mailChimp-forms-by-mailmunch'
			];
			
			$url = 'http://api.wordpress.org/plugins/info/1.0/';
			$path = WP_PLUGIN_DIR . '/';
			
			foreach($install_pluggins as $plugin){
				
				if(!is_dir($path . $plugin)){
					
					$args = (object) array( 'slug' => $plugin );
					
					$request = array( 'action' => 'plugin_information', 'timeout' => 15, 'request' => serialize( $args) );
					
					$response = wp_remote_post( $url, array( 'body' => $request ) );
					
					$plugin_info = unserialize( $response['body'] );
					
					$file = $path . $plugin . '.zip';
					file_put_contents($file, fopen($plugin_info->download_link, 'r'));
					
					if(filesize($file) > 0){
						
						$zip = new ZipArchive();
						$res = $zip->open($file);
						if ($res === TRUE) {
							
							$extract = $zip->extractTo(WP_PLUGIN_DIR);
							if($extract){
								unlink($file);
							}
							$zip->close();
							
							activate_plugin($path . $plugin . '/' . $plugin . '.php');
							
						}
						
					}
					
				}
				
			}
			
			if(!is_dir(WP_PLUGIN_DIR . '/github-updater-master')){
				
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
					}
					$zip->close();

					activate_plugin(WP_PLUGIN_DIR . '/github-updater-master/github-updater.php');

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
			
			update_option('active_plugins',array (
			  0 => 'addthis/addthis_social_widget.php',
			  1 => 'black-studio-tinymce-widget/black-studio-tinymce-widget.php',
			  2 => 'disqus-comment-system/disqus.php',
			  3 => 'simple-social-icons/simple-social-icons.php'
			));
			
			update_option('widget_search',array (
				1 => array (
					'title' => 'Search Site',
				),
				'_multiwidget' => 1
			));
			
			if(get_option('widget_simple-social-icons') !== FALSE){
				
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
				
			}
			
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
			
			$pages = get_posts(array('numberposts' => 5, 'post_type' => 'page'));
			
			if(empty($pages)){
				
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
			
			$posts = get_posts(array('numberposts' => 5, 'post_type' => 'post'));
			
			if(sizeof($posts) == 1){
				
				wp_delete_posts(1);
				
				$standard = wp_insert_post(array(
					'post_title' => 'Standard Post',
					'post_status' => 'publish',
					'post_type' => 'post',
					'post_content' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.'
				));
				
				$quote = wp_insert_post(array(
					'post_title' => 'Quote Post',
					'post_status' => 'publish',
					'post_type' => 'post',
					'post_content' => 'Do not dwell in the past, do not dream of the future, concentrate the mind on the present moment.'
				));
				
				$term = get_term_by('slug','post-format-quote','post_format');
				wp_set_post_terms( $quote, $term->term_id, 'post_format' );
				
				
				$video = wp_insert_post(array(
					'post_title' => 'Video Post',
					'post_status' => 'publish',
					'post_type' => 'post',
					'post_content' => ''
				));
				
				$term = get_term_by('slug','post-format-video','post_format');
				wp_set_post_terms( $video, $term->term_id, 'post_format' );
				add_post_meta($video, '_gismo_post_format_section_links', array (
				  	0 => 'https://vimeo.com/10602957'
				));
				
				
				$image = wp_insert_post(array(
					'post_title' => 'Single Image Post',
					'post_status' => 'publish',
					'post_type' => 'post',
					'post_content' => ''
				));
				
				$term = get_term_by('slug','post-format-image','post_format');
				wp_set_post_terms( $image, $term->term_id, 'post_format' );
				add_post_meta($image, '_gismo_post_format_section_links', array (
				  	0 => 'https://media.ldscdn.org/images/media-library/gospel-art/church-history/orson-hyde-dedicates-palestine-37726-gallery.jpg'
				));
				
				
				$gallery = wp_insert_post(array(
					'post_title' => 'Image Gallery Post',
					'post_status' => 'publish',
					'post_type' => 'post',
					'post_content' => ''
				));
				
				$term = get_term_by('slug','post-format-gallery','post_format');
				wp_set_post_terms( $gallery, $term->term_id, 'post_format' );
				add_post_meta($gallery, '_gismo_post_format_section_links', array (
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
		
		/**
		 * Register widget area.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
		 */
		public function gismo_widgets_init() {
			
			if(!empty($this->settings['sidebars'])){
				
				foreach($this->settings['sidebars'] as $key => $value){
					
					register_sidebar( array(
						'name'          => esc_html__( $value['name'], 'gismo' ),
						'id'            => strtolower(str_replace(' ','-',$value['name'])) . '-' . $key,
						'description'   => esc_html__( $value['description'], 'gismo' ),
						'before_widget' => '<section id="%1$s" class="widget %2$s '.(empty($value['widget_class']) ? 'uk-width-1-1' : $value['widget_class']).'">',
						'after_widget'  => '</section>',
						'before_title'  => '<h2 class="widget-title'.$value['title_class'].'">',
						'after_title'   => '</h2>',
					) );
				
				}
				
			}else{
				
				register_sidebar( array(
					'name'          => esc_html__( 'Sidebar', 'gismo' ),
					'id'            => 'sidebar-1',
					'description'   => esc_html__( 'Add widgets here. Add your own sidebars through the Gismo Theme Settings.', 'gismo' ),
					'before_widget' => '<section id="%1$s" class="widget %2$s">',
					'after_widget'  => '</section>',
					'before_title'  => '<h2 class="widget-title">',
					'after_title'   => '</h2>',
				) );	
				
			}
			
			if(!empty($this->settings['sidebars'])){
				
				foreach($this->settings['sidebars'] as $key => $value){
					
					add_action($value['hook'], function() use ($key){
						
						echo '<div class="'.$this->settings['sidebars'][$key]['class'].'">';
						
						dynamic_sidebar(strtolower(str_replace(' ','-',$this->settings['sidebars'][$key]['name'])) . '-' . $key);
						
						echo '</div>';
							
					});
					
				}
				
			}else{
				
				add_action('gismo_sidebar', function(){
						
					dynamic_sidebar('sidebar-1');
						
				});
					
			}
			
		}
		
		/**
		 * Enqueue scripts and styles.
		 */
		public function gismo_scripts() {
			
			$gismo_ts = $this->settings;
			$layout = $gismo_ts['layout'];
			$style = $gismo_ts['style'];
			
			$uikit_theme = $layout['uikit']['theme'];
			
			if($layout['blog']['paging'] == 'infinite'){
				
				$args = array(
					'nonce' => wp_create_nonce('gismo-load-more-nonce'),
					'url'   => admin_url('admin-ajax.php'),
					'query' => ''//$this->wp_query->query,
				);
				
				wp_localize_script('gismo-load-more', 'gismoloadmore', $args);
			
			}
			
			wp_enqueue_style( 'gismo-uikit' . ($uikit_theme != 'default' ? '-' . $uikit_theme : '') . '-style', get_template_directory_uri() . '/js/uikit-2.27.2/css/uikit' . ($uikit_theme != 'default' ? '.' . $uikit_theme : '') . '.min.css' );
			
			if(!empty($layout['uikit']['css_components'])){
				
				foreach($layout['uikit']['css_components'] as $component){
					wp_enqueue_style( 'gismo-uikit-' . $component . '-style', get_template_directory_uri() . '/js/uikit-2.27.2/css/components/' . $component . (!empty($uikit_theme) && $uikit_theme != 'default' ? '.' . $uikit_theme : '') . '.min.css' );
				}
			
			}
			
			wp_enqueue_style( 'gismo-menu', get_template_directory_uri() . '/js/menu/css/superfish.css' );
			wp_enqueue_style( 'gismo-style', get_stylesheet_uri() );
			
			if($style['theme'] != 'default'){
				wp_enqueue_style( 'gismo-'.$style['theme'].'-style', get_template_directory_uri() . '/css/themes/'.$style['theme'].'.css' );	
			}
		
			wp_enqueue_script( 'gismo-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '20151215', true );
		
			wp_enqueue_script( 'gismo-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20151215', true );
		
			if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
				wp_enqueue_script( 'comment-reply' );
			}
			
			wp_enqueue_script( 'gismo-uikit', get_template_directory_uri() . '/js/uikit-2.27.2/js/uikit.min.js', array('jquery'), '2.27.2', true );
			wp_enqueue_script( 'gismo-hoverintent', get_template_directory_uri() . '/js/menu/js/hoverIntent.js', array('jquery'), '', true );
			wp_enqueue_script( 'gismo-menu', get_template_directory_uri() . '/js/menu/js/superfish.min.js', array('jquery'), '1.7.9', true );
			wp_enqueue_script( 'gismo-front', get_template_directory_uri() . '/js/front.js', array('jquery'), '12.1.16', true );
			
			if($layout['blog']['paging'] == 'infinite'){
				
				$args = array(
					'nonce' => wp_create_nonce('gismo-load-more-nonce'),
					'url'   => admin_url('admin-ajax.php'),
					'query' => $this->wp_query->query,
				);
				
				wp_localize_script('gismo-front', 'gismo_loadmore', $args);
			
			}
			
			if($layout['blog']['layout'] == 'grid'){
				wp_enqueue_script( 'gismo-uikit-grid', get_template_directory_uri() . '/js/uikit-2.27.2/js/components/grid.min.js', array('gismo-uikit'), '2.27.2', true );
			}
			
			if(!empty($layout['uikit']['js_components'])){
				
				foreach($layout['uikit']['js_components'] as $component){
					wp_enqueue_script( 'gismo-uikit-' . $component, get_template_directory_uri() . '/js/uikit-2.27.2/js/components/'.$component.'.min.js', array('gismo-uikit'), '2.27.2', true );
				}
			
			}
			
			wp_enqueue_style( 'dashicons' );
			
		}
		
		//*Adds a box to the main column on the Post and Page edit screens.

		public function gismo_custom_header_section() {
		
			$screens = array( 'page', 'post' );
			
			if($this->settings['layout']['page_title'] == 'custom'){
				
				foreach ( $screens as $screen ) {

					add_meta_box(

						'custom_header_section_id',

						__( 'Custom Header Section', 'custom_header_section_textdomain' ),

						array($this,'gismo_custom_header_section_callback'),

						$screen

					);

				}
				
			}
		
		}
		
		public function gismo_custom_header_section_callback( $post ) {
		
			wp_nonce_field( 'custom_header_section_data', 'custom_header_section_nonce' );
		
			$content = get_post_meta( $post->ID, '_gismo_custom_header_section', true );
		
			wp_editor( $content, 'custom-header-section-editor', array('textarea_name' => 'custom_header_section') );
		
			echo '<input style="width:80%; margin-top:10px;" type="text" name="custom_header_section_image" value="'.get_post_meta( $post->ID, '_gismo_custom_header_section_image', true ).'" placeholder="Custom Header Background Image URL"/><button style="margin:9px 0 0 5px;" class="set_custom_image button">Select Image</button>
		
					<script type="text/javascript">
					
					jQuery(function($){
					
						if ($(".set_custom_image").length > 0) {
					
							if ( typeof wp !== "undefined" && wp.media && wp.media.editor) {
					
								$(".wrap").on("click", ".set_custom_image", function(e) {
					
									e.preventDefault();
					
									var button = $(this);
					
									var id = button.prev();
					
									wp.media.editor.send.attachment = function(props, attachment) {
					
										id.val(attachment.url);
					
									};
					
									wp.media.editor.open(button);
					
									return false;
					
								});
					
							}
					
						};
					
					});
					
					</script>';
		
		}
		
		public function gismo_custom_header_section_save( $post_id ) {
		
			if ( ! isset( $_POST['custom_header_section_nonce'] ) ) {
		
				return;
		
			}
		
			if ( ! wp_verify_nonce( $_POST['custom_header_section_nonce'], 'custom_header_section_data' ) ) {
		
				return;
		
			}
		
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		
				return;
		
			}
		
			if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
		
				if ( ! current_user_can( 'edit_page', $post_id ) ) {
		
					return;
		
				}
		
			} else {
		
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
		
					return;
		
				}
		
			}
		
			if ( ! isset( $_POST['custom_header_section'] ) ) {
		
				return;
		
			}
		
			update_post_meta( $post_id, '_gismo_custom_header_section', $_POST['custom_header_section'] );
		
			update_post_meta( $post_id, '_gismo_custom_header_section_image', $_POST['custom_header_section_image'] );
		
		}
		
		public function gismo_add_custom_header_section() {
			
			$settings = get_option('gismo_theme_settings',false);
		
			$page_id = get_queried_object_id();
		
			$custom_header = get_post_meta( $page_id, '_gismo_custom_header_section', true );
		
			$custom_header_image = get_post_meta( $page_id, '_gismo_custom_header_section_image', true );
			
			$header_content = '';
			
			if(!empty($custom_header)){
		
				$header_content = '<div class="uk-width-1-1 custom-header-wrap'.(empty($custom_header_image) ? ' no-custom-header-image' : '').'" style="box-sizing:border-box;'.(!empty($custom_header_image) ? 'background:url('.$custom_header_image.') no-repeat center center; background-size:cover;' : 'display:block; min-height:100px;').'"><div class="custom-header-inner">' . $custom_header . '</div></div>';
				
			}
			
			if(empty($custom_header) && !empty($custom_header_image)){
				
				$header_content = '<div class="uk-width-1-1 custom-header-wrap" style="width:100%; display:block; box-sizing:border-box; background:url('.$custom_header_image.') no-repeat center center; background-size:cover; min-height:150px;"></div>';
				
			}
			
			if(empty($custom_header) && empty($custom_header_image)){
				
				$header_content = '<div class="uk-width-1-1 default-header-wrap" style="width:100%; display:block; box-sizing:border-box;"><div class="uk-container uk-container-center"><div class="uk-grid"><div class="uk-width-1-1"><h1>'.$this->wp_query->post->post_title.'</h1></div></div></div></div>';
			
			}
			
			if(!is_single() && !is_home() && $this->settings['layout']['page_title'] == 'custom'){
				echo $header_content;
			}
			
		}
		
		public function remove_title($classes) {
			
			$classes[] = 'no-title';
			return $classes;
			
		}
		
		//*Adds page elements box to the main column on the Post and Page edit screens.

		public function gismo_page_elements() {
		
			$screens = array( 'page', 'post' );
		
			foreach ( $screens as $screen ) {
		
				add_meta_box(
		
					'page_elements_id',
		
					__( 'Page Elements', 'page_elements_textdomain' ),
		
					array($this,'gismo_page_elements_callback'),
		
					$screen
		
				);
		
			}
		
		}
		
		public function gismo_page_elements_callback( $post ) {
		
			wp_nonce_field( 'page_elements_data', 'page_elements_nonce' );
		
			$content = get_post_meta( $post->ID, '_gismo_page_elements', true );
			
			echo '
			<table width="100%">
				<tr>
					<td>Body Class</td>
					<td><input style="width:100%; margin-top:10px;" type="text" name="page_elements[body_class]" value="'.(!empty($content) ? $content['body_class'] : '').'" placeholder="Optional body classes"/></td>
				</tr>
				<tr>
					<td valign="top">Header Scripts</td>
					<td><textarea style="width:100%; min-height:250px;" name="page_elements[header_scripts]">'.(!empty($content) ? $content['header_scripts'] : '').'</textarea></td>
				</tr>
				<tr>
					<td valign="top">Footer Scripts</td>
					<td><textarea style="width:100%; min-height:250px;" name="page_elements[footer_scripts]">'.(!empty($content) ? $content['footer_scripts'] : '').'</textarea></td>
				</tr>
			</table>
			';
		
		}
		
		public function gismo_page_elements_save( $post_id ) {
		
			if ( ! isset( $_POST['page_elements_nonce'] ) ) {
		
				return;
		
			}
		
			if ( ! wp_verify_nonce( $_POST['page_elements_nonce'], 'page_elements_data' ) ) {
		
				return;
		
			}
		
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		
				return;
		
			}
		
			if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
		
				if ( ! current_user_can( 'edit_page', $post_id ) ) {
		
					return;
		
				}
		
			} else {
		
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
		
					return;
		
				}
		
			}
		
			if ( ! isset( $_POST['page_elements'] ) ) {
		
				return;
		
			}
		
			update_post_meta( $post_id, '_gismo_page_elements', $_POST['page_elements'] );
		
		}
		
		public function gismo_add_page_elements_header() {
		
			$page_id = get_queried_object_id();
		
			$content = get_post_meta( $page_id, '_gismo_page_elements', true );
			
			if(!empty($content) && !empty($content['header_scripts'])){
				echo '<script type="text/javascript">';
				echo wp_unslash( $content['header_scripts'] );
				echo '</script>';
			}
			
		}
		
		public function gismo_add_page_elements_footer() {
		
			$page_id = get_queried_object_id();
		
			$content = get_post_meta( $page_id, '_gismo_page_elements', true );
			
			if(!empty($content) && !empty($content['footer_scripts'])){
				echo '<script type="text/javascript">';
				echo wp_unslash( $content['footer_scripts'] );
				echo '</script>';
			}
			
		}
		
		public function gismo_body_classes($classes) {
			
			$page_id = get_queried_object_id();
			
			$content = get_post_meta( $page_id, '_gismo_page_elements', true );
			
			if(!empty($content)){
				$classes[] = $content['body_class'];
			}
			
			/* using mobile browser */
			if ( wp_is_mobile() ){
				$classes[] = 'wp-is-mobile';
			}
			else{
				$classes[] = 'wp-is-not-mobile';
			}
			
			return $classes;
			
		}
		
		public function gismo_gallery($atts) {
	
			global $post;
			$pid = $post->ID;
			$gallery = '';

			if (empty($pid)) {$pid = $post['ID'];}

			if (!empty( $atts['ids'] ) ) {
				$atts['orderby'] = 'post__in';
				$atts['include'] = $atts['ids'];
			}

			extract(shortcode_atts(array('orderby' => 'menu_order ASC, ID ASC', 'include' => '', 'id' => $pid, 'itemtag' => 'dl', 'icontag' => 'dt', 'captiontag' => 'dd', 'columns' => 3, 'size' => 'large', 'link' => 'file'), $atts));

			$args = array('post_type' => 'attachment', 'post_status' => 'inherit', 'post_mime_type' => 'image', 'orderby' => $orderby);

			if (!empty($include)) {$args['include'] = $include;}
			else {
				$args['post_parent'] = $id;
				$args['numberposts'] = -1;
			}

			if ($args['include'] == "") { $args['orderby'] = 'date'; $args['order'] = 'asc';}

			$images = get_posts($args);
			
			$slides = '';
			foreach ( $images as $image ) {
				//print_r($image); /*see available fields*/
				$thumbnail = wp_get_attachment_image_src($image->ID, 'large');
				$thumbnail = $thumbnail[0];
				$slides .= '
					<li>
						<img src="'.$thumbnail.'">
					</li>
					';
			}
			
			$gallery .= '
				<div class="uk-slidenav-position" data-uk-slideshow>
					<ul class="uk-slideshow">
						'.$slides.'
					</ul>
					<a href="" class="uk-slidenav uk-slidenav-contrast uk-slidenav-previous" data-uk-slideshow-item="previous"></a>
					<a href="" class="uk-slidenav uk-slidenav-contrast uk-slidenav-next" data-uk-slideshow-item="next"></a>
				</div>
			';

			return $gallery;
		}
		
	}
	
	$gismo = new Gismo();
	
}