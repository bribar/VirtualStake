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

<div id="content" class="<?php echo apply_filters('gismo_content_classes', 'site-content');?>">

	<div class="uk-grid">
    
    	<?php if($gismo_ts['layout']['blog']['sidebar'] == 'left'):?>

			<div class="uk-width-1-4">
            	
                <?php if(has_action('gismo_sidebar')):?>
                <div class="uk-grid">
            		<?php do_action('gismo_sidebar');?>
                </div>
                <?php endif;?>

            </div>
		
		<?php endif;?>

        <div class="<?php echo($gismo_ts['layout']['blog']['sidebar'] == 'none' ? 'uk-width-1-1' : 'uk-width-3-4');?>">
    
            <main id="main" class="site-main" role="main">
    
                <?php
                while ( have_posts() ) : the_post();
    
                    get_template_part( 'template-parts/content', 'page' );
    
                endwhile; // End of the loop.
                ?>
    
            </main><!-- #main -->
            
        </div>
        
        <?php if($gismo_ts['layout']['blog']['sidebar'] == 'right'):?>

			<div class="uk-width-1-4">
            	
                <?php if(has_action('gismo_sidebar')):?>
                <div class="uk-grid">
            		<?php do_action('gismo_sidebar');?>
                </div>
                <?php endif;?>

            </div>
		
		<?php endif;?>
        
     </div>

</div>

<?php

get_footer();
