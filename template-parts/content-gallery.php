<?php
/**
 * Template part for displaying posts.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package gismo
 */

$links = get_post_meta( $post->ID, '_gismo_post_format_section_links', true );

?>

<article id="post-<?php the_ID(); ?>" <?php post_class('card');?>>
	
	<div class="card-bottom no-top no-pad">
		
		<?php //echo do_shortcode($content);?>
		
		<div class="uk-slidenav-position" data-uk-slideshow>
			<ul class="uk-slideshow">
				<?php if(!empty($links)):?>
				<?php foreach($links as $link):?>
				<li>
					
					<img src="<?php echo $link;?>">
				</li>
				<?php endforeach;?>
				<?php endif;?>
			</ul>
			<a href="" class="uk-slidenav uk-slidenav-contrast uk-slidenav-previous" data-uk-slideshow-item="previous"></a>
			<a href="" class="uk-slidenav uk-slidenav-contrast uk-slidenav-next" data-uk-slideshow-item="next"></a>
		</div>
		
		<header class="entry-header">
			<?php
			
				the_title( '<h2 class="entry-title" style="margin:10px 0 5px;"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
			
			?>
		</header>
		
	</div>
    
</article>