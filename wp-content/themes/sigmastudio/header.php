<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package sigmaStudio
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
    <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <div id="page" class="site">
        <a class="skip-link screen-reader-text"
            href="#content"><?php esc_html_e( 'Skip to content', 'sigmastudio' ); ?></a>

        <header id="masthead" class="site-header">
            <div class="site-branding">
            </div><!-- .site-branding -->
            <nav id="site-navigation" class="main-navigation">

                <!-- start menu mobile  -->
                <div class="topnav" id="myTopnav">
                    <a href="javascript:void(0);" style="font-size:15px;" class="icon"
                        onclick="myFunction()">&#9776;</a>
                    <a>

                        <?php
                        //menu location
                        $args = array(
                            'theme_location' => 'head',
                            'class' => 'main-menu'
                        );
                        //get menu
                        wp_nav_menu( $args );
                    ?>
                    </a>
                </div>
            </nav><!-- #site-navigation -->
        </header><!-- #masthead -->
        <script>
        function myFunction() {
            var x = document.getElementById("myTopnav");
            if (x.className === "topnav") {
                x.className += " responsive";
            } else {
                x.className = "topnav";
            }
        }

        var prevScrollpos = window.pageYOffset;
        window.onscroll = function() {
            var currentScrollPos = window.pageYOffset;
            if (prevScrollpos > currentScrollPos) {
                document.getElementById("myTopnav").style.top = "0";
            } else {
                document.getElementById("myTopnav").style.top = "-50px";
            }
            prevScrollpos = currentScrollPos;
        }
        </script>

        <div id="content" class="site-content">