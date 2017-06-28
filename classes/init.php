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
				echo '<a href="#gismo-mobile-menu-wrapper" class="uk-hidden-large mobile-menu-icon" style="margin:0 auto;" data-uk-offcanvas><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="24px" height="24px" viewBox="0 0 24 24" style="enable-background:new 0 0 24 24;" xml:space="preserve"><circle style="fill:#FFFFFF;" cx="5" cy="12" r="2"/><circle style="fill:#FFFFFF;" cx="12" cy="12" r="2"/><circle style="fill:#FFFFFF;" cx="19" cy="12" r="2"/></svg></a>';
					
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
					'orientation'       => 'vertical', // vertical, vertical-center, horizontal
					'header_position'   => 'undocked',
					'header_config'     => 'left',
					'lazy_images'       => 0,
					'uikit'             => [
						'theme'          => 'default', // almost-flat, gradient
						'css_components' => [],
						'js_components'  => []
					],
					'blog'        => [
						'layout'  => 'stacked',
						'sidebar' => 'right',
						'paging'  => 'simple'
					]
				],
			];
			
		  	add_option('gismo_theme_settings', $settings);
			
			$menus = [];
			
			add_option('gismo_theme_menus', $menus);
			
			$sidebars = [];
			
			add_option('gismo_theme_sidebars', $sidebars);
			
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
						
						echo '<div class="'.$this->settings['sidebars'][$key]['class'].'"><div class="uk-grid">';
						
						dynamic_sidebar(strtolower(str_replace(' ','-',$this->settings['sidebars'][$key]['name'])) . '-' . $key);
						
						echo '</div></div>';
							
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
			
		}
		
		//*Adds a box to the main column on the Post and Page edit screens.

		public function gismo_custom_header_section() {
		
			$screens = array( 'page', 'post' );
		
			foreach ( $screens as $screen ) {
		
				add_meta_box(
		
					'custom_header_section_id',
		
					__( 'Custom Header Section', 'custom_header_section_textdomain' ),
		
					array($this,'gismo_custom_header_section_callback'),
		
					$screen
		
				);
		
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
		
			$page_id = get_queried_object_id();
		
			$custom_header = get_post_meta( $page_id, '_gismo_custom_header_section', true );
		
			$custom_header_image = get_post_meta( $page_id, '_gismo_custom_header_section_image', true );
			
			if(is_page()){
				add_filter('post_class', array($this,'remove_title'));
			}
			
			if(is_single()){
				//echo 'HI';
			}
			
			if(!empty($custom_header)){
		
				$header_content = '<div class="uk-width-1-1 custom-header-wrap'.(empty($custom_header_image) ? ' no-custom-header-image' : '').'" style="box-sizing:border-box;'.(!empty($custom_header_image) ? 'background:url('.$custom_header_image.') no-repeat center center; background-size:cover;' : 'display:block; min-height:100px;').'"><div class="custom-header-inner">' . $custom_header . '</div></div>';
				
				echo $header_content;
		
			}
			
			if(empty($custom_header) && !empty($custom_header_image)){
				
				$header_content = '<div class="uk-width-1-1 custom-header-wrap" style="width:100%; display:block; box-sizing:border-box; background:url('.$custom_header_image.') no-repeat center center; background-size:cover; min-height:150px;"></div>';
				
				echo $header_content;
					
			}
			
			if(empty($custom_header) && empty($custom_header_image)){
				
				$header_content = '<div class="uk-width-1-1 default-header-wrap" style="width:100%; display:block; box-sizing:border-box;"><div class="uk-container uk-container-center"><div class="uk-grid"><div class="uk-width-1-1"><h1>'.$this->wp_query->post->post_title.'</h1></div></div></div></div>';
				
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

			return $classes;
			
		}
		
	}
	
	$gismo = new Gismo();
	
}