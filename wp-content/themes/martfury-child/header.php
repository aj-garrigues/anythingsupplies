<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package Martfury
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

    <link rel="icon" href="https://anythingsupplies.com/favicon.ico">
    <link rel="icon" type="image/png" sizes="96x96" href="https://anythingsupplies.com/wp-content/uploads/2026/06/favicon-96x96-1.png">
    <link rel="apple-touch-icon" sizes="180x180" href="https://anythingsupplies.com/wp-content/uploads/2026/06/apple-touch-icon.png">

    <meta property="og:title" content="Anything Supplies | B2B Wholesale Sourcing & OEM Supplier">
    <meta property="og:type" content="website">
    <meta property="og:description" content="Source anything for your business — CCTV, PPE, furniture, packaging and more. Low MOQ, OEM manufacturing across 6 countries, and a price match guarantee.">
    <meta property="og:url" content="https://anythingsupplies.com">
    <meta property="og:site_name" content="Anything Supplies | B2B Wholesale Sourcing & OEM Supplier">
    
    <meta property="og:image" content="https://anythingsupplies.com/wp-content/uploads/2026/07/anything_supplies_og_image.png">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Anything Supplies | B2B Wholesale Sourcing & OEM Supplier">
    <meta name="twitter:description" content="Source anything for your business — CCTV, PPE, furniture, packaging and more. Low MOQ, OEM manufacturing across 6 countries, and a price match guarantee.">
    <meta name="twitter:image" content="https://anythingsupplies.com/wp-content/uploads/2026/07/anything_supplies_og_image.png">

	<?php wp_head(); ?>
<meta name="facebook-domain-verification" content="5q6pn0ih8nx8rlk008p4ibix6oxp10" />
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-57GS932L');</script>
<!-- End Google Tag Manager -->

<!-- Meta Pixel Code -->
<script>
  !function(f,b,e,v,n,t,s)
  {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
  n.queue=[];t=b.createElement(e);t.async=!0;
  t.src=v;s=b.getElementsByTagName(e)[0];
  s.parentNode.insertBefore(t,s)}(window, document,'script',
  'https://connect.facebook.net/en_US/fbevents.js');
  fbq('init', '865649639358202');
  fbq('track', 'PageView');
</script>
<noscript>
  <img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=865649639358202&ev=PageView&noscript=1"/>
</noscript>
<!-- End Meta Pixel Code -->

<!-- Leadsy AI -->
 <script id="vtag-ai-js" async src="https://r2.leadsy.ai/tag.js" data-pid="7K6fluNdkI582ddS" data-version="062024"></script>
 <!-- Leadsy AI end -->
</head>

<body <?php body_class(); ?>>
  <!-- Google Tag Manager (noscript) -->
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-57GS932L"
  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
  <!-- End Google Tag Manager (noscript) -->
<?php martfury_body_open(); ?>

<div id="page" class="hfeed site">
	<?php if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) {
		?>
		<?php do_action( 'martfury_before_header' ); ?>
        <header id="site-header" class="site-header <?php martfury_header_class(); ?>">
			<?php do_action( 'martfury_header' ); ?>
        </header>
	<?php } ?>
	<?php do_action( 'martfury_after_header' ); ?>

    <div id="content" class="site-content">
		<?php do_action( 'martfury_after_site_content_open' ); ?>
