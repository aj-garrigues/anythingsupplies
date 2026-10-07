<?php

/**
 * Template Name: Template Partnership
 * Template Post Type: page
 */

// Disabling the standard header/footer to keep the landing page clean.
// If you want your site's menu, change this to get_header();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>My Sals3 Account | <?php bloginfo( 'name' ); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<div class="container-fluid" style="background-color: #f8f9fa; border-bottom: 1px solid #e0e0e0;">
    <div class="container registration-header">
    <div class="row">
        <div class="col-lg-6">
            <a class="myaccount-header-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <img class="logo-register" alt="Anything Supplies" src="<?php echo esc_url( get_theme_file_uri() ); ?>/images/SALS3-03-scaled.webp">
                <span class="logo-myaccount-text"> | Partnership Account</span>
            </a>
        
        </div> 
        <div class="col-lg-6">
            <div class="myaccount-header-help">
            <a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>" class=" has-icon font-black"><i class="ion-bag"></i> Shop</a> |  
            <a href="<?php echo esc_url( home_url( '/support/' ) ); ?>" class=" has-icon  font-black"><i class="ion-help"></i> Help</a>
            </div>
        </div>
    </div>
    </div>
</div>

<div class="container-fluid" style="background-image: url('<?php echo esc_url( get_theme_file_uri() ); ?>/images/login-background-jan.webp'); background-size: cover; background-position: center;" >
   
<div class="container">
 <?php
if ( have_posts() ) :
	while ( have_posts() ) : the_post(); 
		the_content();
	endwhile;

endif;
?>
</div></div>

<?php wp_footer();
get_footer();
?>
</body>
</html>