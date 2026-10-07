<?php

/**
 * Template Name: Template Registration
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
    <title>Registration Successful | <?php bloginfo( 'name' ); ?></title>
    <?php wp_head(); ?>
    <style>
        .card{
            margin-bottom: 20px;
        }
        .bg-blue-light{
background: #D6EFFF;
background: linear-gradient(300deg, rgba(214, 239, 255, 1) 0%, rgba(214, 239, 255, 1) 0%, rgba(247, 252, 255, 1) 79%);
        }
        .container-fluid{
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
            border-bottom: 1px solid #e0e0e0;
        }
    </style>
</head>
<body <?php body_class(); ?>>
<div class="container-fluid">
    <div class="container registration-header">
    <div class="row">
        <div class="col-lg-6">
            <a class="myaccount-header-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <img class="logo-register" alt="Anything Supplies" src="<?php echo esc_url( get_theme_file_uri() ); ?>/images/SALS3-03-scaled.webp">
                <span class="logo-myaccount-text"> | My Account</span>
            </a>
        
        </div>
        <div class="col-lg-6">
            <div class="myaccount-header-help">
            <a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>" class=" has-icon font-black"><i class="ion-bag"></i> Shop</a> |  
            <a href="<?php echo esc_url( home_url( '/frequently-asked/' ) ); ?>" class=" has-icon  font-black"><i class="ion-help"></i> Help</a>
            </div>
        </div>
    </div>
    </div>
</div>

<div class="  bg-blue-light">
   <div class="container ">

 <?php
if ( have_posts() ) :
	while ( have_posts() ) : the_post();
		the_content();
	endwhile;

endif;
?>
</div>
</div>
<?php wp_footer();
get_footer();
?>
</body>
</html>