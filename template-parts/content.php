<?php
/**
 * Template part for displaying posts.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package gismo
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class();?>>
	
    <div class="uk-grid">
    	
        <div class="uk-width-small-1-1 uk-width-medium-1-3">
        
		<?php
            $feat_image = wp_get_attachment_url( get_post_thumbnail_id($post->ID) );
        ?>
        
        <?php echo(empty($feat_image) ? '<div class="feat-image rd5" style="background: #26cdf9 url(' . get_template_directory_uri() . '/images/default-post-image.png) no-repeat center center; background-size:cover; width:100%; height:300px;"></div>' : '<div class="feat-image rd5" style="background: url(' . $feat_image . ') no-repeat center center; background-size:cover; width:100%; height:300px;"></div>');?>
    
    	</div>
        
        <div class="uk-width-small-1-1 uk-width-medium-2-3">
    		
            <div class="padding-mobile">
            
            <header class="entry-header">
                <?php
                if ( is_single() ) :
                    the_title( '<h1 class="entry-title">', '</h1>' );
                else :
                    the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
                endif;
        
                if ( 'post' === get_post_type() ) : ?>
                <div class="entry-meta">
                    <?php gismo_posted_on(); ?>
                </div>
                <?php
                endif; ?>
            </header>
        
            <div class="entry-content">
                <?php
                    the_excerpt( sprintf(
                        /* translators: %s: Name of current post. */
                        wp_kses( __( 'Continue reading %s <span class="meta-nav">&rarr;</span>', 'gismo' ), array( 'span' => array( 'class' => array() ) ) ),
                        the_title( '<span class="screen-reader-text">"', '"</span>', false )
                    ) );
        
                    wp_link_pages( array(
                        'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'gismo' ),
                        'after'  => '</div>',
                    ) );
                ?>
            </div>
        
            <footer class="entry-footer">
                <?php gismo_entry_footer(); ?>
            </footer>
    		
            </div>
            
    	</div>
    
    </div>
    
</article>