<?php
get_header();
?>
<header>
    <div class="slide-rev">
        <?php echo do_shortcode( '[rev_slider alias="slider1"]' ); ?>
    </div>
</header>
<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <div class="jumbotron text-center">
            <h1><?= the_title(); ?></h1>
            <p><?= the_field('banner'); ?></p>
        </div>

        <div class="about-us-article" style="
    padding: 100px;
    background-image: url(https://cdn.shortpixel.ai/client/q_glossy,ret_img,w_750/https://freecourseudemy.com/wp-content/uploads/2019/02/The-Complete-Graphic-Design-Theory-For-Beginners-Course.jpg);
">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <div class="about-us">
                           <i class="fas fa-taxi"></i>
                            <h3>About Us</h3>
                            <p>
                                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Laborum iure fugit eos quibusdam quasi unde ratione, excepturi debitis obcaecati placeat possimus laudantium aliquid qui non harum. Ipsam, temporibus odit pariatur!
                            </p>
                            <span>Read More <i class="fas fa-chevron-right"></i></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="about-us">
                            <i class="fab fa-product-hunt"></i>
                            <h3>Service</h3>
                            <p>
                                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Laborum iure fugit eos quibusdam quasi unde ratione, excepturi debitis obcaecati placeat possimus laudantium aliquid qui non harum. Ipsam, temporibus odit pariatur!
                            </p>
                            <span>Read More <i class="fas fa-chevron-right"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </main><!-- #main -->
</div><!-- #primary -->
<div class="product-portfolio">
    <div class="row no-gutter">
        <div class="col-md-6">
            <div class="row no-gutter">
                <div class="col-md-6 title-product-group">
                    <div class="title-product">
                        <h2>Creativity and design the best idea</h2>
                        <span>Read More <i class="fas fa-chevron-right"></i></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <img src="https://media.istockphoto.com/photos/top-view-of-a-graphic-designer-at-work-picture-id532395452?k=6&m=532395452&s=612x612&w=0&h=yNhn8FLspcCrY8CRzMUzvh9wYqNrNjWzIfimEx5NLpg=" alt="design">
                    <div class="class-overlay"></div>
                </div>
                <div class="col-md-6">
                    <img src="https://kbworks.org/wp-content/uploads/2019/07/graphic-design-cape-town.jpg" alt="design">
                    <div class="class-overlay"></div>
                </div>
                <div class="col-md-6">
                    <div class="title-product">
                        <h2>Nice Dream</h2>
                        <span>Read More <i class="fas fa-chevron-right"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <img src="https://www.women-in-technology.com/hubfs/Recruiters%20Women%20in%20Techjpg.jpg" alt="design">
        </div>
    </div>
</div>

<?php
get_footer();