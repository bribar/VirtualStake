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

	<div id="content" class="<?php echo apply_filters('gismo_content_classes', 'site-content');?>">
    	
        <div class="uk-container uk-container-center">
        
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
        
                    get_template_part( 'template-parts/content', 'single' );
        
                    the_post_navigation();
        
                    // If comments are open or we have at least one comment, load up the comment template.
                    if ( comments_open() || get_comments_number() ) :
                        comments_template();
                    endif;
        
                endwhile; // End of the loop.
                ?>
        
                </main>
            
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
        
	</div>

<?php

get_footer();
