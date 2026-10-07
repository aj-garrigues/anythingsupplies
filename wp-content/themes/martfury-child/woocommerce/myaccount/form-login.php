<?php
/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-login.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$login_actived = true;
if ( ! empty( $_POST ) && isset( $_POST['woocommerce-register-nonce'] ) && ! empty( $_POST['woocommerce-register-nonce'] ) ) {
	$login_actived = false;
}

$login_form_layout = martfury_get_option( 'login_register_layout' );
$login_layout      = 'martfury-login-' . $login_form_layout;
if ( $login_form_layout == 'promotion' ) {
	$login_layout .= ' martfury-login-tabs';
}

?>

<?php do_action( 'woocommerce_before_customer_login_form' ); ?>

<?php
$login_class     = $login_actived ? 'active' : '';
$register_class  = ! $login_actived ? 'active' : '';
$col_login_class = 'col-md-6 col-sm-6 col-md-offset-3 col-sm-offset-3';
if ( $login_form_layout == 'promotion' ) {
	$col_login_class = 'col-md-5 col-sm-12';
}
?>

<div class="customer-login">

    <div class="row">

        <div class=" col-login" >
            <div class="<?php echo esc_attr( $login_layout ); ?>">

                <div >
                         
                        <?php if (  is_page('account-login') ) : ?>
                    <div class="container-fluid" >
        <div class="row" style="">
            <div>
                    <div style="max-width: 400px; margin-left: auto;">

                       

                        <form class="woocommerce-form woocommerce-form-login login myaccount-login" method="post">

							<h2><?php esc_html_e( 'Log In Your Account', 'martfury' ); ?></h2>
                            <div class="login-start"><?php do_action( 'woocommerce_login_form_start' ); ?></div>

                            <div class="login-separator">
                                <span class="login-separator__text">OR</span>
                            </div>

                            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" required
                                       placeholder="<?php esc_attr_e( 'Username or email address', 'martfury' ); ?>"
                                       name="username" id="username" autocomplete="username"
                                       value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( $_POST['username'] ) : ''; ?>" required aria-required="true"/>
                            </p>

                            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide form-row-password">
                                <input class="woocommerce-Input woocommerce-Input--text input-text" required
                                       placeholder="<?php esc_attr_e( 'Password', 'martfury' ); ?>" type="password"
                                       autocomplete="current-password"
                                       name="password" id="password" required aria-required="true"/>
                            </p>

							<?php do_action( 'woocommerce_login_form' ); ?>

                            <p class="form-row">
                                <span class="woocommerce-form-row__remember">
                                    <label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
                                        <input class="woocommerce-form__input woocommerce-form__input-checkbox"
                                               name="rememberme" type="checkbox" id="rememberme" value="forever"/>
                                        <span><?php esc_html_e( 'Remember me', 'martfury' ); ?></span>
                                     </label>
                                   <a class="lost-password"
                                      href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Forgot your password?', 'martfury' ); ?></a>
                                </span>

								<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
                                <button type="submit" class="woocommerce-Button button" name="login"
                                        value="<?php esc_attr_e( 'Log in', 'martfury' ); ?>"><?php esc_html_e( 'Log in', 'martfury' ); ?></button>
                            </p>
                            <div style="margin-top: 15px; border-top: 1px solid #e0e0e0; padding-top: 15px;">
                                <p>New to Anything Supplies Market? <a href="<?php echo esc_url( home_url( '/account-register/' ) ); ?>">Sign Up</a> </p>
                            </div>
							
                            <div class="login-end">
                                <?php do_action( 'woocommerce_login_form_end' ); ?>
                            </div>

                        </form>
                    </div>
                    
                    
                    </div>
                    </div>
                    <?php endif; ?>

					<?php if ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' && is_page('account-register') ) : ?>
                   <div class="container-fluid">
    <div class="row" style="">
        <div>
            <div style="max-width: 400px; margin-left: auto;">
                        <div class=" <?php echo esc_attr( $register_class ); ?>" style="margin-top: 20px; margin-bottom: 100px; ">

                            

                            <form method="post" class="register woocommerce-form woocommerce-form-register" <?php do_action( 'woocommerce_register_form_tag' ); ?> style="background-color: #ffffff; padding: 30px; border-radius: 3px;" >
<h2><?php esc_html_e( 'Register An Account', 'martfury' ); ?></h2>
								<?php do_action( 'woocommerce_register_form_start' ); ?>

								<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>

                                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide" >
                                        <input type="text" required
                                               class="woocommerce-Input woocommerce-Input--text input-text"
                                               placeholder="<?php esc_attr_e( 'Username', 'martfury' ); ?>"
                                               name="username" id="reg_username" autocomplete="username"
                                               value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( $_POST['username'] ) : ''; ?>" required aria-required="true"/>
                                    </p>

								<?php endif; ?>

                                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                                    <input type="email" required
                                           class="woocommerce-Input woocommerce-Input--text input-text"
                                           placeholder="<?php esc_attr_e( 'Email address', 'martfury' ); ?>"
                                           name="email" id="reg_email" autocomplete="email"
                                           value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( $_POST['email'] ) : ''; ?>" required aria-required="true"/>
                                </p>

								<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>

                                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                                        <input type="password" required
                                               placeholder="<?php esc_attr_e( 'Password', 'martfury' ); ?>"
                                               class="woocommerce-Input woocommerce-Input--text input-text"
                                               autocomplete="new-password"
                                               name="password" id="reg_password" required aria-required="true"/>
                                    </p>

								<?php else : ?>

                                    <p><?php esc_html_e( 'Enter your email to create your secure profile.', 'martfury' ); ?></p>

								<?php endif; ?>

								<?php do_action( 'woocommerce_register_form' ); ?>

                                <p class="woocommerce-form-row form-row">
									<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
                                    <button type="submit" class="woocommerce-Button button" name="register"
                                            value="<?php esc_attr_e( 'Register', 'martfury' ); ?>"><?php esc_html_e( 'Register', 'martfury' ); ?></button>
                                </p>
                                <p>Already have an account? <a href="https://anythingsupplies.com/account-login/">Log In</a> </p>
								<?php do_action( 'woocommerce_register_form_end' ); ?>

                            </form>

                        </div>
                        </div>
        </div>
    </div>
</div>
                    </div>

					<?php endif; ?>

                </div>
            </div>
        </div>

		<?php do_action( 'martfury_after_login_form' ); ?>

    </div>
</div>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
