<?php
/**
 * Template Name: No Sidebar
 *
 * @package gismo
 */

get_header(); ?>

<div id="content" class="<?php echo apply_filters('gismo_content_classes', 'site-content');?>">

	<div class="uk-grid">
    
    	<div class="uk-width-1-1">
    
            <main id="main" class="site-main" role="main">
    
                <?php
                while ( have_posts() ) : the_post();
    
                    get_template_part( 'template-parts/content', 'page' );
    
                endwhile; // End of the loop.
                ?>
    
            </main><!-- #main -->
        
        </div>
        
	</div>
    
</div>

<?php

get_footer();