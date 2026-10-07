<?php

add_filter( 'theme_page_templates', 'register_my_custom_template' );

function register_my_custom_template( $templates ) {
    // 'file-name.php' => 'Template Name'
    $templates['template-registration.php'] = 'Template Registration';
    $templates['template-login.php'] = 'Template Login';
    $templates['template-partnership.php'] = 'Template Partnership';
    $templates['template-myaccount.php'] = 'Template MyAccount';
    return $templates;
}