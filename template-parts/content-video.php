<?php
/**
 * Template part for displaying posts.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package gismo
 */

$links = get_post_meta( $post->ID, '_gismo_post_format_section_links', true );
$replace = '"(https?://.*)(?=;)"';
$url = preg_replace($replace, '', $links[0]);

$is_vimeo = strpos($url,'vimeo.com');

if(!$is_vimeo){
	
	preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $url, $matches);
	
	$image = 'http://img.youtube.com/vi/' . $matches[1] . '/0.jpg';
	
}else{
	
	$fetchVimeoIdArr = explode('/', $url);
    $idCounter = count($fetchVimeoIdArr) - 1;
    $vimeoId = $fetchVimeoIdArr[$idCounter];
	
	$image = json_decode(file_get_contents('http://vimeo.com/api/v2/video/'.$vimeoId.'.json'));
	$image = $image[0]->thumbnail_large;
	
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('card');?>>
	
	<a href="<?php echo $url;?>" class="card-bottom no-top no-pad" data-uk-lightbox title="<?php the_title();?>">
		
		<?php echo '<div class="feat-image rd5" style="background: url(' . $image . ') no-repeat center center; background-size:cover; width:100%; height:220px;"></div>';?>

	</a>
    
</article>