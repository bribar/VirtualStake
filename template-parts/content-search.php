<?php
/**
 * Template part for displaying results in search pages.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package gismo
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	
	<div class="flexy" style="background-color: #fff; margin-top: 20px;">
		
		<div style="width: 115px; border-right: 3px solid #eee;">
			
			<div style="padding: 20px 20px;">
			
				<?php if ( 'post' === get_post_type() ) : ?>
				<div class="entry-meta">
					<?php 
					
					$time_string = '<time class="entry-date published updated" datetime="%1$s"><span style="display:block; font-size:24px;">%3$s</span><span style="display:block; font-size:12px; text-transform:uppercase;">%2$s %4$s</span></time>';
					
					$time_string = sprintf( $time_string,
						esc_attr( get_the_date( 'c' ) ),
						esc_html( get_the_date('M') ),
					   esc_html( get_the_date('jS') ),
					   esc_html( get_the_date('Y') )
					);

					$posted_on = sprintf(
						esc_html_x( '%s', 'post date', 'gismo' ),
						'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark" style="text-align:center;">' . $time_string . '</a>'
					);
					
					echo $posted_on;
					
					?>
				</div><!-- .entry-meta -->
				<?php endif; ?>
				
			</div>
			
		</div>
		
		<div>
			
			<div style="padding: 20px 20px;">
			
				<header class="entry-header">
					<?php the_title( sprintf( '<h2 class="entry-title" style="margin-bottom: 0;"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>
				</header><!-- .entry-header -->

				<div class="entry-summary" style="margin-top: 5px;">
					<?php the_excerpt(); ?>
				</div><!-- .entry-summary -->
				
			</div>
			
		</div>
		
	</div>

</article><!-- #post-## -->
