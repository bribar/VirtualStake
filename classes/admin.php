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