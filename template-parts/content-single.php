<?php
/**
 * Template part for displaying posts.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package gismo
 */
$gismo_ts = $GLOBALS['gismo_theme_settings'];
$feat_image = wp_get_attachment_url( get_post_thumbnail_id($post->ID) );
$terms = wp_get_post_terms( $post->ID, 'post_format' );
if(!empty($terms)){
$links = get_post_meta( $post->ID, '_gismo_post_format_section_links', true );
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	
	<?php if(!empty($feat_image)):?>
	<div style="width: 100%; height: 450px; background: url(<?php echo $feat_image;?>) no-repeat center center; background-size: cover;"><?php //the_post_thumbnail( 'full' );?></div>
	<?php endif;?>
	
    <div>
		
		<header class="entry-header">
			
			<?php if($gismo_ts['layout']['page_title'] == 'default'):?>

				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

			<?php endif;?>
			
		</header>
		
		<div class="entry-content">
			<?php
			
				if(!empty($terms) && $terms[0]->name == 'Gallery'){
					
					if(!empty($links)){
						
						foreach($links as $link){

							echo '<img src="'.$link.'"/>';

						}
						
					}
					
				}else{
					
					the_content();
					/*the_excerpt( sprintf(
						wp_kses( __( 'Continue reading %s <span class="meta-nav">&rarr;</span>', 'gismo' ), array( 'span' => array( 'class' => array() ) ) ),
						the_title( '<span class="screen-reader-text">"', '"</span>', false )
					) );*/

					wp_link_pages( array(
						'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'gismo' ),
						'after'  => '</div>',
					) );
					
				}
			?>
		</div>

		<footer class="entry-footer">
			<?php gismo_entry_footer(); ?>
		</footer>
		
	</div>
    
</article>

<?php if(has_action('gismo_after_content')):?>
<div class="after-content">
<?php do_action('gismo_after_content');?>
</div>
<?php endif;?>

<?php if(is_user_logged_in() && current_user_can('activate_plugins')):?>
<div class="hook-area">After Content</div>
<?php endif;?>