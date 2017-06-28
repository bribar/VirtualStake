<?php
/**
 * The header for our theme.
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package gismo
 */

$gismo_ts = $GLOBALS['gismo_theme_settings'];

?><!DOCTYPE html>
<html <?php language_attributes();?>>
<head>
<meta charset="<?php bloginfo('charset');?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">

<?php wp_head();?>
</head>

<body <?php body_class();?>>

<?php echo(!empty($gismo_ts['style']['background']['url']) ? '<div id="background-img" style="background:url('.$gismo_ts['style']['background']['url'].') no-repeat center center;"></div>' : '')?>

<?php
	
	$style = '';
	
	if($gismo_ts['layout']['orientation'] != 'horizontal' && $gismo_ts['layout']['header_position'] == 'docked'){
		$style = ' style="padding-top:'.$gismo_ts['style']['logo']['height'].'px;"';
	}
	
	if($gismo_ts['layout']['orientation'] != 'horizontal' && $gismo_ts['layout']['header_position'] == 'docked'){
		$style = ' style="padding-top:'.$gismo_ts['style']['logo']['height'].'px;"';
	}
	
?>

<div id="page" class="site<?php echo($gismo_ts['layout']['orientation'] == 'vertical-center' ? ' uk-container uk-container-center':'');?>"<?php echo $style;?>>

	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e('Skip to content', 'gismo'); ?></a>
	
    <?php if($gismo_ts['layout']['orientation'] == 'horizontal'):?>
    
    <div class="uk-grid">
    
    	<div class="uk-width-1-4<?php echo($gismo_ts['layout']['orientation'] == 'horizontal' && $gismo_ts['layout']['header_position'] == 'docked' ? ' header-horizontal-fixed' : '');?>">
    
    <?php endif;?>
    
    <?php if(has_action('gismo_before_header')):?>
    <div class="uk-grid before-header-wrapper">
	<?php do_action('gismo_before_header');?>
    </div>
    <?php endif;?>

	<header id="masthead" class="<?php echo apply_filters('gismo_site_header_classes', 'site-header' . ($gismo_ts['layout']['orientation'] != 'horizontal' && $gismo_ts['layout']['header_position'] == 'docked' ? ' header-vertical-fixed' : ''));?>" role="banner">
    	
        <?php echo($gismo_ts['layout']['header_position'] == 'docked' && $gismo_ts['layout']['orientation'] == 'vertical-center' ? '<div class="uk-container uk-container-center">' : '');?>
        
    	<div class="uk-container uk-container-center<?php echo($gismo_ts['layout']['header_config'] == 'center' ? ' logo-centered' : '');?>">
        	
            <div class="uk-grid">
        	<?php
			switch($gismo_ts['layout']['header_config']){
				case 'left';
					$col_left_class = 'uk-width-small-3-4 uk-width-medium-1-4';
					$col_right_class = 'uk-width-small-1-4 uk-width-medium-3-4';
					break;	
				case 'center';
					$col_left_class = 'uk-width-4-10';
					$col_right_class = 'uk-width-4-10';
					break;
				case 'right';
					$col_left_class = 'uk-width-small-1-4 uk-width-medium-3-4';
					$col_right_class = 'uk-width-small-3-4 uk-width-medium-1-4';
					break;
				case 'stacked';
					$col_left_class = 'uk-width-1-1';
					$col_right_class = 'uk-width-1-1';
					break;
			}
			?>
            
            <?php if($gismo_ts['layout']['orientation'] == 'horizontal'):?>
            
                <div class="uk-width-1-1">
                	
                    <?php if(has_action('gismo_before_logo')):?>
                    <div class="uk-grid before-logo-wrapper">
                    	<?php do_action('gismo_before_logo');?>
                    </div>
                    <?php endif;?>
                    
                    <h1 id="logo">
                       
					  <?php if(empty($gismo_ts['style']['logo']['url'])):?>
                      
                      <a href="<?php echo site_url();?>" style="width:<?php echo $gismo_ts['style']['logo']['width'];?>px; height:<?php echo $gismo_ts['style']['logo']['height'];?>px;" id="logo-img">
                      	<img src="<?php echo get_template_directory_uri();?>/images/gismo-logo.svg"/>
                      </a>
                      
                      <?php else:?>
                      
                      <a href="<?php echo site_url();?>" style="width:<?php echo $gismo_ts['style']['logo']['width'];?>px; height:<?php echo $gismo_ts['style']['logo']['height'];?>px;" id="logo-img">
                      	<img src="<?php echo $gismo_ts['style']['logo']['url'];?>"/>
                      </a>
                      
                      <?php endif;?> 
                       
                    </h1>
                    
                    <?php if(has_action('gismo_after_logo')):?>
                    <div class="uk-grid after-logo-wrapper">
                    	<?php do_action('gismo_after_logo');?>
                    </div>
                    <?php endif;?>
                    
                    <?php if(has_action('gismo_primary_navigation')):?>
                    <div class="<?php echo !has_action('gismo_after_primary_navigation') ? 'uk-grid ' : '';?>primary-navigation-wrapper">
                    <?php do_action('gismo_primary_navigation');?>
                    </div>
                    <?php endif;?>
                    
                    <?php if(has_action('gismo_after_primary_navigation')):?>
                    <div class="<?php echo !has_action('gismo_primary_navigation') ? 'uk-grid ' : '';?>after-primary-navigation-wrapper">
                    <?php do_action('gismo_after_primary_navigation');?>
                    </div>
                    <?php endif;?>
                    
                    <?php if(has_action('gismo_secondary_navigation')):?>
                    <div class="uk-grid secondary-navigation-wrapper">
                    <?php do_action('gismo_secondary_navigation');?>
                    </div>
                    <?php endif;?>
                    
                </div>
            
            <?php else:?>
            
            <div class="<?php echo $col_left_class;?>">
            
            	<?php if($gismo_ts['layout']['header_config'] == 'left' || $gismo_ts['layout']['header_config'] == 'stacked'):?>
                
					<?php if(has_action('gismo_before_logo')):?>
                    <div class="uk-grid before-logo-wrapper">
                    	<?php do_action('gismo_before_logo');?>
                    </div>
                    <?php endif;?>
                    
                    <h1 id="logo">
                           
                      <?php if(empty($gismo_ts['style']['logo']['url'])):?>
                      
                      <a href="<?php echo site_url();?>" style="width:<?php echo $gismo_ts['style']['logo']['width'];?>px; height:<?php echo $gismo_ts['style']['logo']['height'];?>px;" id="logo-img">
                        <img src="<?php echo get_template_directory_uri();?>/images/gismo-logo.svg"/>
                      </a>
                      
                      <?php else:?>
                      
                      <a href="<?php echo site_url();?>" style="width:<?php echo $gismo_ts['style']['logo']['width'];?>px; height:<?php echo $gismo_ts['style']['logo']['height'];?>px;" id="logo-img">
                        <img src="<?php echo $gismo_ts['style']['logo']['url'];?>"/>
                      </a>
                      
                      <?php endif;?> 
                       
                    </h1>
                    
                    <?php if(has_action('gismo_after_logo')):?>
                    <div class="uk-grid after-logo-wrapper">
                    	<?php do_action('gismo_after_logo');?>
                    </div>
                    <?php endif;?>
                
                <?php endif;?>
                
                <?php if($gismo_ts['layout']['header_config'] == 'right' || $gismo_ts['layout']['header_config'] == 'center'):?>
                
					<?php if(has_action('gismo_primary_navigation')):?>
                    <div class="<?php echo !has_action('gismo_after_primary_navigation') ? 'uk-grid ' : '';?>primary-navigation-wrapper">
                    <?php do_action('gismo_primary_navigation');?>
                    </div>
                    <?php endif;?>
                    
                    <?php if(has_action('gismo_after_primary_navigation')):?>
                    <div class="<?php echo !has_action('gismo_primary_navigation') ? 'uk-grid ' : '';?>after-primary-navigation-wrapper">
                    <?php do_action('gismo_after_primary_navigation');?>
                    </div>
                    <?php endif;?>
                
                <?php endif;?>
                
            </div>
            
            <?php if($gismo_ts['layout']['header_config'] == 'center'):?>
            
            <div class="uk-width-2-10">
            	
                <?php if(has_action('gismo_before_logo')):?>
                <div class="uk-grid before-logo-wrapper">
                    <?php do_action('gismo_before_logo');?>
                </div>
                <?php endif;?>
                
                <h1 id="logo">
                       
				  <?php if(empty($gismo_ts['style']['logo']['url'])):?>
                  
                  <a href="<?php echo site_url();?>" style="width:<?php echo $gismo_ts['style']['logo']['width'];?>px; height:<?php echo $gismo_ts['style']['logo']['height'];?>px;" id="logo-img">
                    <img src="<?php echo get_template_directory_uri();?>/images/gismo-logo.svg"/>
                  </a>
                  
                  <?php else:?>
                  
                  <a href="<?php echo site_url();?>" style="width:<?php echo $gismo_ts['style']['logo']['width'];?>px; height:<?php echo $gismo_ts['style']['logo']['height'];?>px;" id="logo-img">
                    <img src="<?php echo $gismo_ts['style']['logo']['url'];?>"/>
                  </a>
                  
                  <?php endif;?> 
                   
                </h1>
                
                <?php if(has_action('gismo_after_logo')):?>
                <div class="uk-grid after-logo-wrapper">
                    <?php do_action('gismo_after_logo');?>
                </div>
                <?php endif;?>
               
            </div>
            
            <?php endif;?>
            
            <div class="<?php echo $col_right_class;?>">
            	
                <?php if($gismo_ts['layout']['header_config'] == 'right'):?>
                
					<?php if(has_action('gismo_before_logo')):?>
                    <div class="uk-grid before-logo-wrapper">
                    	<?php do_action('gismo_before_logo');?>
                    </div>
                    <?php endif;?>
                    
                    <h1 id="logo">
                           
                      <?php if(empty($gismo_ts['style']['logo']['url'])):?>
                      
                      <a href="<?php echo site_url();?>" style="width:<?php echo $gismo_ts['style']['logo']['width'];?>px; height:<?php echo $gismo_ts['style']['logo']['height'];?>px;" id="logo-img">
                        <img src="<?php echo get_template_directory_uri();?>/images/gismo-logo.svg"/>
                      </a>
                      
                      <?php else:?>
                      
                      <a href="<?php echo site_url();?>" style="width:<?php echo $gismo_ts['style']['logo']['width'];?>px; height:<?php echo $gismo_ts['style']['logo']['height'];?>px;" id="logo-img">
                        <img src="<?php echo $gismo_ts['style']['logo']['url'];?>"/>
                      </a>
                      
                      <?php endif;?> 
                       
                    </h1>
                    
                    <?php if(has_action('gismo_after_logo')):?>
                    <div class="uk-grid after-logo-wrapper">
                    	<?php do_action('gismo_after_logo');?>
                    </div>
                    <?php endif;?>
                
                <?php endif;?>
                
                <?php if($gismo_ts['layout']['header_config'] == 'left' || $gismo_ts['layout']['header_config'] == 'stacked'):?>
                
					<?php if(has_action('gismo_primary_navigation')):?>
                    <div class="<?php echo !has_action('gismo_after_primary_navigation') ? 'uk-grid ' : '';?>primary-navigation-wrapper">
                    <?php do_action('gismo_primary_navigation');?>
                    </div>
                    <?php endif;?>
                    
                    <?php if(has_action('gismo_after_primary_navigation')):?>
                    <div class="<?php echo !has_action('gismo_primary_navigation') ? 'uk-grid ' : '';?>after-primary-navigation-wrapper">
                    <?php do_action('gismo_after_primary_navigation');?>
                    </div>
                    <?php endif;?>
                
                <?php endif;?>
                
                <?php if($gismo_ts['layout']['header_config'] == 'center'):?>
                
					<?php if(has_action('gismo_secondary_navigation')):?>
                    <div class="uk-grid secondary-navigation-wrapper">
                    <?php do_action('gismo_secondary_navigation');?>
                    </div>
                    <?php endif;?>
                
                <?php endif;?>
                
            </div>
            
            <?php endif;?>
            
            </div>
            
        </div>
        
        <?php echo($gismo_ts['layout']['header_position'] == 'docked' && $gismo_ts['layout']['orientation'] == 'vertical-center' ? '</div>' : '');?>
    
	</header>
    
    <?php if(has_action('gismo_after_header')):?>
    <div class="uk-grid after-header-wrapper">
    <?php do_action('gismo_after_header');?>
    </div>
    <?php endif;?>
    
    <?php if($gismo_ts['layout']['orientation'] == 'horizontal'):?>
    
    <?php if(has_action('gismo_before_footer')):?>
    <div class="before-footer-wrapper">
        <div class="uk-grid before-footer">
        <?php do_action('gismo_before_footer');?>
        </div>
    </div>
    <?php endif;?>
    
    <footer id="colophon" class="site-footer" role="contentinfo">
    	
		<div class="site-info">
			<a href="<?php echo esc_url( __( 'https://wordpress.org/', 'gismo' ) ); ?>"><?php printf( esc_html__( 'Proudly powered by %s', 'gismo' ), 'WordPress' ); ?></a>
			<span class="sep"> | </span>
			<?php printf( esc_html__( '%1$s Theme', 'gismo' ), 'Gismo' ); ?>
		</div>
        
	</footer>
    
    <?php if(has_action('gismo_after_footer')):?>
    <div class="after-footer-wrapper">
        <div class="uk-grid after-footer">
        <?php do_action('gismo_after_footer');?>
        </div>
    </div>
    <?php endif;?>
    
    <?php endif;?>
    
    <?php if($gismo_ts['layout']['orientation'] == 'horizontal'):?>
    
    	</div>
        
        <div class="uk-width-3-4<?php echo($gismo_ts['layout']['header_position'] == 'docked' ? ' uk-push-1-4' : '');?>">
    
    <?php endif;?>