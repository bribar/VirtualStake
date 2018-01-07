<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package gismo
 */

$gismo_ts = $GLOBALS['gismo_theme_settings'];

?>

	<?php if($gismo_ts['layout']['orientation'] != 'horizontal'):?>
    
    <?php if(has_action('gismo_before_footer')):?>
    <div class="before-footer-wrapper">
    	<?php echo($gismo_ts['layout']['orientation'] == 'vertical-center' ? '<div class="uk-container uk-container-center">':'');?>
        <div class="uk-grid before-footer">
        <?php do_action('gismo_before_footer');?>
        </div>
        <?php echo($gismo_ts['layout']['orientation'] == 'vertical-center' ? '</div>':'');?>
    </div>
    <?php endif;?>
    
	<footer id="colophon" class="<?php echo apply_filters('gismo_footer_classes', ($gismo_ts['layout']['orientation'] == 'vertical-center' ? 'uk-container uk-container-center site-footer':'site-footer'));?>" role="contentinfo">
    	
        <div class="uk-grid">
        
            <div class="site-info uk-width-1-1">
                <a href="/">Copyright © 2016 <?php bloginfo( 'name' ); ?>. All Rights Reserved.</a>
            </div>
        
        </div>
        
	</footer>
    
    <?php if(has_action('gismo_after_footer')):?>
    <div class="after-footer-wrapper">
    	<?php echo($gismo_ts['layout']['orientation'] == 'vertical-center' ? '<div class="uk-container uk-container-center">':'');?>
        <div class="uk-grid after-footer">
        <?php do_action('gismo_after_footer');?>
        </div>
        <?php echo($gismo_ts['layout']['orientation'] == 'vertical-center' ? '</div>':'');?>
    </div>
    <?php endif;?>
    
    <?php else:?>
    
    </div>
    
    </div>
    
    <?php endif;?>
    
</div>

<?php wp_footer(); ?>

</body>
</html>
