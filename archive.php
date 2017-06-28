<?php
/**
 * The template for displaying archive pages.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
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
                if ( have_posts() ) : ?>
        
                    <header class="page-header">
                        <?php
                            the_archive_title( '<h1 class="page-title">', '</h1>' );
                            the_archive_description( '<div class="archive-description">', '</div>' );
                        ?>
                    </header><!-- .page-header -->
        
                    <?php
                    /* Start the Loop */
                    while ( have_posts() ) : the_post();
        
                        /*
                         * Include the Post-Format-specific template for the content.
                         * If you want to override this in a child theme, then include a file
                         * called content-___.php (where ___ is the Post Format name) and that will be used instead.
                         */
                        get_template_part( 'template-parts/content', get_post_format() );
        
                    endwhile;
        
                    the_posts_navigation();
        
                else :
        
                    get_template_part( 'template-parts/content', 'none' );
        
                endif; ?>
        
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
