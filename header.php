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

<?php echo(is_front_page() && !empty($gismo_ts['style']['background']['url']) ? '<div id="background-img" style="background:url('.$gismo_ts['style']['background']['url'].') no-repeat center center;"></div>' : '')?>

<div id="page" class="site">

	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e('Skip to content', 'gismo'); ?></a>
	
    <div class="flexy">
		
		<div id="left">
			
			<?php if(has_action('gismo_before_header')):?>
			<div class="before-header-wrapper">
				<?php do_action('gismo_before_header');?>
			</div>
			<?php endif;?>

			<?php if(is_user_logged_in() && current_user_can('activate_plugins')):?>
			<div class="hook-area">Before Header</div>
			<?php endif;?>
			
			<div class="box-pad">
				
				<?php if(has_action('gismo_before_logo')):?>
				<div class="before-logo-wrapper">
					<?php do_action('gismo_before_logo');?>
				</div>
				<?php endif;?>
				
				<?php if(is_user_logged_in() && current_user_can('activate_plugins')):?>
				<div class="hook-area">Before Logo</div>
				<?php endif;?>

				<h1 id="logo">

				  <?php if(empty($gismo_ts['style']['logo']['url'])):?>

				  <a href="<?php echo site_url();?>" style="width:<?php echo $gismo_ts['style']['logo']['width'];?>px;" id="logo-img">
					  <h2 class="site-title"><?php echo get_bloginfo('name');?></h2>
					  <h4 class="site-description"><?php echo get_bloginfo('description');?></h4>
				  </a>

				  <?php else:?>

				  <a href="<?php echo site_url();?>" style="width:<?php echo $gismo_ts['style']['logo']['width'];?>px; height:<?php echo $gismo_ts['style']['logo']['height'];?>px;" id="logo-img">
					<img src="<?php echo $gismo_ts['style']['logo']['url'];?>"/>
				  </a>

				  <?php endif;?> 

				</h1>

				<?php if(has_action('gismo_after_logo')):?>
				<div class="after-logo-wrapper">
					<?php do_action('gismo_after_logo');?>
				</div>
				<?php endif;?>
				
				<?php if(is_user_logged_in() && current_user_can('activate_plugins')):?>
				<div class="hook-area">After Logo</div>
				<?php endif;?>

				<?php if(has_action('gismo_primary_navigation')):?>
				<div class="primary-navigation-wrapper">
				<?php do_action('gismo_primary_navigation');?>
				</div>
				<?php endif;?>
				<?php if(is_user_logged_in() && current_user_can('activate_plugins')):?>
				<div class="hook-area">Primary Navigation</div>
				<?php endif;?>

				<?php if(has_action('gismo_after_primary_navigation')):?>
				<div class="after-primary-navigation-wrapper">
				<?php do_action('gismo_after_primary_navigation');?>
				</div>
				<?php endif;?>
				
				<?php if(is_user_logged_in() && current_user_can('activate_plugins')):?>
				<div class="hook-area">After Primary Navigation</div>
				<?php endif;?>

				<?php if(has_action('gismo_secondary_navigation')):?>
				<div class="secondary-navigation-wrapper">
				<?php do_action('gismo_secondary_navigation');?>
				</div>
				<?php endif;?>
				
				<?php if(is_user_logged_in() && current_user_can('activate_plugins')):?>
				<div class="hook-area">Secondary Navigation</div>
				<?php endif;?>
				
				<?php if(is_user_logged_in() && current_user_can('activate_plugins')):?>
				<div class="hook-area">Before Footer</div>
				<?php endif;?>
				
				<div class="site-info">
					&copy; <?php echo date('Y');?> <?php bloginfo( 'name' ); ?>.
				</div>
				
				<?php if(is_user_logged_in() && current_user_can('activate_plugins')):?>
				<div class="hook-area">After Footer</div>
				<?php endif;?>
				
			</div>
			
			<?php if(has_action('gismo_after_header')):?>
			<div class="after-header-wrapper">
				<?php do_action('gismo_after_header');?>
			</div>
			<?php endif;?>

			<?php if(is_user_logged_in() && current_user_can('activate_plugins')):?>
			<div class="hook-area">After Header</div>
			<?php endif;?>
			
		</div>
		
		<div style="width:100%;">