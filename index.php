<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package gismo
 */

global $wp_query;
//print_r($wp_query);
$gismo_ts = $GLOBALS['gismo_theme_settings'];

get_header();

?>

<?php if(is_home() && is_front_page()):?>

<?php if(has_action('gismo_before_blog')):?>
<div class="before-blog">
<?php do_action('gismo_before_blog');?>
</div>
<?php endif;?>

<?php if(is_user_logged_in() && current_user_can('activate_plugins')):?>
<div class="hook-area">Before Blog</div>
<?php endif;?>

<?php endif;?>

<div id="content" class="<?php echo apply_filters('gismo_content_classes', 'site-content');?>">

	<div class="flexy">
    
    	<?php if($gismo_ts['layout']['blog']['sidebar'] == 'left'):?>
        
        <div>
        	
            <?php if(has_action('gismo_sidebar')):?>
            <div>
        		<?php do_action('gismo_sidebar');?>
            </div>
            <?php endif;?>
			
			<?php if(is_user_logged_in() && current_user_can('activate_plugins')):?>
			<div class="hook-area">Sidebar</div>
			<?php endif;?>
        
        </div>
        
        <?php endif;?>

        <div style="width: 100%;">
        	
            <main id="main" class="site-main" role="main">
    		
            <?php if( is_home() && ! is_front_page()):?>
                <header>
                    <h1 class="page-title screen-reader-text"><?php single_post_title();?></h1>
                </header>
            <?php endif;?>
            
            <?php if(have_posts()):?>
    			
				<div class="<?php echo($gismo_ts['layout']['blog']['layout'] == 'grid' ? ' uk-grid-width-small-1-1 uk-grid-width-medium-1-2 uk-grid-width-large-1-3' : ' uk-grid-width-1-1');?><?php echo($gismo_ts['layout']['blog']['paging'] == 'infinite' ? ' infinite-scroll' : '');?>"<?php echo($gismo_ts['layout']['blog']['layout'] == 'grid' ? ' data-uk-grid="{gutter: 20}"' : '');?>>
				
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
				?>
                
                </div>
                
                <div class="flexy">
                
                	<div>
					<?php
                        if($gismo_ts['layout']['blog']['paging'] == 'simple'){
                            
                            the_posts_navigation();
                            
                        }elseif($gismo_ts['layout']['blog']['paging'] == 'numbered'){
                            
                            $paged = $wp_query->query_vars['paged'] == 0 ? $wp_query->query_vars['paged'] + 1 : $wp_query->query_vars['paged']; 			
                            
                            $start = 1;
                            $limit = $wp_query->max_num_pages;
                            
                            $pagi = '<ul class="uk-pagination">';
                            
                            if($paged > 1){
                                $pagi .= '<li><a href="'.site_url($wp_query->query_vars['pagename'] . ($paged == 2 ? '' : '/page/' . ($paged - 1))).'"><i class="uk-icon-angle-double-left"></i></a></li>';
                            }
                            
                            if($wp_query->max_num_pages > 10){
                                $start = ceil($paged - 2.5);
                                if($start < 1){
                                    $start = 1;
                                }
                                $limit = $start + 4;
                                if($limit > $wp_query->max_num_pages){
                                    $limit = $wp_query->max_num_pages;
                                }
                                
                                if($start > 2){
                                    $pagi .= '<li><a href="'.site_url($wp_query->query_vars['pagename']).'">1</a></li>';
                                    $pagi .= '<li><span>...</span></li>';
                                }
                                if($start == 2){
                                    $pagi .= '<li><a href="'.site_url($wp_query->query_vars['pagename']).'">1</a></li>';
                                }
                            }
                            
                            for($i = $start; $i <= $limit; $i++){
                                
                                if($i == $paged){
                                    $pagi .= '<li class="uk-active"><span>'.$i.'</span></li>';
                                }else{
                                    $pagi .= '<li><a href="'.site_url($wp_query->query_vars['pagename'] . ($i == 1 ? '' : '/page/' . $i)).'">'.$i.'</a></li>';
                                }
                                
                            }
                            
                            if($wp_query->max_num_pages > 10){
                                if($limit < ($wp_query->max_num_pages)){
                                $pagi .= '<li><span>...</span></li>';
                                $pagi .= '<li><a href="'.site_url($wp_query->query_vars['pagename'] . '/page/' . $wp_query->max_num_pages).'">'.$wp_query->max_num_pages.'</a></li>';
                                }
                            }
                            
                            if($paged != $wp_query->max_num_pages){
                                $pagi .= '<li><a href="'.site_url($wp_query->query_vars['pagename'] . '/page/' . ($paged + 1)).'"><i class="uk-icon-angle-double-right"></i></a></li>';
                            }
                            
                            $pagi .= '</ul>';
                            
                            echo $pagi;
                            
                        }else{
                            
                            if($wp_query->max_num_pages > 1){
                                echo '<div class="fetch-more" data-page="2"/>';
                            }
                            
                        }
                    ?>
                	</div>
                
                </div>
             
            <?php else:?>
    
                <?php get_template_part( 'template-parts/content', 'none' );?>
    
            <?php endif;?>
    
            </main>
            
        </div>
        
        <?php if($gismo_ts['layout']['blog']['sidebar'] == 'right'):?>
        
        <div>
        	
            <?php if(has_action('gismo_sidebar')):?>
            <div>
        		<?php do_action('gismo_sidebar');?>
            </div>
            <?php endif;?>
			
			<?php if(is_user_logged_in() && current_user_can('activate_plugins')):?>
			<div class="hook-area">Sidebar</div>
			<?php endif;?>
        
        </div>
        
        <?php endif;?>
    
    </div>
    
</div>

<?php if(is_home() && is_front_page()):?>

<?php if(has_action('gismo_after_blog')):?>
<div class="after-blog">
<?php do_action('gismo_after_blog');?>
</div>
<?php endif;?>

<?php if(is_user_logged_in() && current_user_can('activate_plugins')):?>
<div class="hook-area">After Blog</div>
<?php endif;?>

<?php endif;?>

<?php

get_footer();
