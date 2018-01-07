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
	
	<a href="<?php echo $links[0];?>" class="card-bottom no-top no-pad" data-uk-lightbox title="<?php echo the_title();?>">
		
		<?php echo '<div class="feat-image rd5" style="background: url(' . $links[0] . ') no-repeat center center; background-size:cover; width:100%; height:300px;"></div>';?>

	</a>
    
</article>