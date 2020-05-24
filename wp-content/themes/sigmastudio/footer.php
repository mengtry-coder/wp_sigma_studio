<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package sigmaStudio
 */

?>

</div><!-- #content -->

<footer id="colophon" class="site-footer">
    <div class="site-info">
        <nav class="navbar navbar-expand-sm bg-dark navbar-dark">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 col-md-6 col-sm-6">
                        <h3 class= "footer-title"><?= the_title();?></h3>
                        <p><?= the_field('banner'); ;?></p>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-6">
                        <h3 class= "footer-title">Privacy & Condition</h3>
                        <?php
                			//menu location
                			$args = array(
                				'theme_location' => 'footer'
                			);
                			//get menu
                			wp_nav_menu( $args );
                		?>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-6">
                        <h3 class= "footer-title">Follow Us</h3>
                        <ul class="social-network social-circle">
                            <li><a href="<?php the_field('email');?>" class="icoRss" title="Rss"><i
                                        class="fa fa-rss"></i></a></li>
                            <li><a href="<?php the_field('facebook');?>" class="icoFacebook" title="Facebook"><i
                                        class="fa fa-facebook"></i></a></li>
                            <li><a href="<?php the_field('twitter');?>" class="icoTwitter" title="Twitter"><i
                                        class="fa fa-twitter"></i></a></li>
                            <li><a href="<?php the_field('linked');?>" class="icoGoogle" title="Google +"><i
                                        class="fa fa-google-plus"></i></a>
                            </li>
                        </ul>
                    </div>


                </div>
            </div>
        </nav>
    </div><!-- .site-info -->
</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>

</html>