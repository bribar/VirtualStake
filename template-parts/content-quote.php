<?php
/**
 * Template part for displaying posts.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package gismo
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class('card');?>>
	
	<div class="card-bottom no-top">
		
		<i class="quote-icon"></i>
		
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

	</div>
    
</article>