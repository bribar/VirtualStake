<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package gismo
 */

$gismo_ts = $GLOBALS['gismo_theme_settings'];

get_header(); ?>

<?php if(has_action('gismo_before_content')):?>
<div class="before-content">
<?php do_action('gismo_before_content');?>
</div>
<?php endif;?>

<?php if(is_user_logged_in() && current_user_can('activate_plugins')):?>
<div class="hook-area">Before Content</div>
<?php endif;?>

<div id="content" class="<?php echo apply_filters('gismo_content_classes', 'site-content');?>">

	<div class="flexy">
    
    	<?php if($gismo_ts['layout']['blog']['sidebar'] == 'left'):?>

		<div>

			<?php if(has_action('gismo_sidebar')):?>
			<div>
				<?php do_action('gismo_sidebar');?>
			</div>
			<?php endif;?>
			
			<?php if(is_user_logged_in() && current_user_can('activate_plugins')):?>
			<div class="hook-area">Sidebar</div>
			<?php endif;?>

		</div>
		
		<?php endif;?>

        <div>
    
            <main id="main" class="site-main" role="main">
    
                <?php
                while ( have_posts() ) : the_post();
    
                    get_template_part( 'template-parts/content', 'page' );
    
                endwhile; // End of the loop.
                ?>
    
            </main><!-- #main -->
            
        </div>
        
        <?php if($gismo_ts['layout']['blog']['sidebar'] == 'right'):?>

		<div>

			<?php if(has_action('gismo_sidebar')):?>
			<div>
				<?php do_action('gismo_sidebar');?>
			</div>
			<?php endif;?>
			
			<?php if(is_user_logged_in() && current_user_can('activate_plugins')):?>
			<div class="hook-area">Sidebar</div>
			<?php endif;?>

		</div>
		
		<?php endif;?>
        
     </div>

</div>

<?php if(has_action('gismo_after_content')):?>
<div class="after-content">
<?php do_action('gismo_after_content');?>
</div>
<?php endif;?>

<?php if(is_user_logged_in() && current_user_can('activate_plugins')):?>
<div class="hook-area">After Content</div>
<?php endif;?>

<?php

get_footer();
