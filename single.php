<?php
/**
 * The template for displaying all single posts.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
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
        
                    get_template_part( 'template-parts/content', 'single' );
        
                    the_post_navigation(array('prev_text' => '<p>← %title</p>', 'next_text' => '<p>%title →</p>'));
        
                    // If comments are open or we have at least one comment, load up the comment template.
                    if ( comments_open() || get_comments_number() ) :
                        comments_template();
                    endif;
        
                endwhile; // End of the loop.
                ?>
        
                </main>
            
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

<?php

get_footer();
