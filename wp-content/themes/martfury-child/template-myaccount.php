<?php

/**
 * Template Name: Template MyAccount
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
    <title>Anything Supplies Account | <?php bloginfo( 'name' ); ?></title>
    <?php wp_head(); ?>
    <style>
      .site-content{ 
        padding:40px 0px;  background-color:#f7fdff;
      }
      .woocommerce-account.logged-in .site-content{
        background-color: #fff;
      }
      .MyAccount-navigation{
        padding-right: 18px; 
      }
      .MyAccount-navigation ul{
        margin:0px;
        padding:0px;
        display: flex;
        flex-direction: column;
        gap: 6px;
      }
      .MyAccount-navigation ul li.my-account-menu-item{
        list-style:none; margin:0px;
        border: none;
        background-color: transparent;
      }
      .MyAccount-navigation ul li.my-account-menu-item a{
        padding:7px 0px;
        color:#000;
        font-weight:500;
      }
      .MyAccount-navigation ul li.is-active{
        border: none;
        background-color: transparent; 
      }
      .MyAccount-navigation ul li.is-active a{
        color: #00719a; font-weight: 600;
      }
        
      .MyAccount-navigation ul li a:hover{
        color: #00719a; 
      }
      .MyAccount-navigation ul li:last-child{
        border-bottom: none;
      }
      .MyAccount-navigation ul.my-account-child-menu{
          list-style:none; margin:0px 0px 0px 25px; padding-left:0px;
      }
      .MyAccount-navigation ul.my-account-child-menu .my-account-menu-item-child a{
          color:#000; font-weight:500;
      }
      .MyAccount-navigation ul.my-account-child-menu .is-active a{
          color:#00719a; font-weight:600;
      }
      .MyAccount-navigation .account-name{
        margin-left: 20px;
      } 
      .MyAccount-content{
        width: 79%;
      }
      .MyAccount-navigation {
        line-height: 1;
        /* min-width: 200px;
        max-width: 200px; */
        float: left;
      }
      .my-account{
        font-size:15px;
        border: 1px solid #e7e7e7;
        margin: 0px 0px;
        padding: 0px !important;
        background-color: transparent !important;
        border: none !important;
        box-shadow: none !important;
      }
      .address-box{
          border:1px solid #e7e7e7; padding:20px; margin-bottom:30px;
      } 
      .woocommerce-MyAccount-navigation hr{
          border-right:1px solid #e7e7e7; 
      }
      .my-account .woocommerce {
        display: flex;
      }
      .woocommerce-account .woocommerce .woocommerce-MyAccount-content{
        padding-left:30px;
        min-height:550px;
        width: 100%;
      }

      .account-hearder-text {
        font-family: system-ui, sans-serif;
        font-weight: 600 !important;
      }
        .my-account .my-account-header{
            font-size:30px !important; border-bottom:1px solid #e7e7e7; padding-bottom:10px; margin-bottom:35px;
        }
        .my-account .my-account-header h2{
            font-size:22px !important; margin-bottom: 2px; font-weight:600; margin-top:0px;
        }
        .my-account .my-account-header-tags{
            font-size:16px; color:#777777;
        }
        .my-account .woocommerce-order-details{
            margin-top:5px;
            padding:30px;
            border:1px solid #e7e7e7;
            border-top:3px solid #ff4c00;
            background-color:#fff;
        }
        .edit-account-fields {
          display: flex;
          flex-direction: column;
        }
        .edit-account-fields .edit-form-row{
            margin-bottom:30px; display: flex; gap: 10px;
        }
        .edit-account-fields .edit-account-inner{
            width: 63%; display:inline-block;
        }
        .edit-account-fields .edit-photo-section{
            width: 35%; display:inline-block; vertical-align: top;
        }
        .edit-account-fields .edit-account-photo-container{
            padding: 20px 0; display: flex; gap: 16px; width: 600px;
        } 
          .edit-account-fields .edit-account-photo-container p{
            font-size:14px; color:#555; margin-top:10px;
        } 
        .edit-account-fields .edit-account-photo-container img{
            width: 100px; height:100px; border-radius:50%; object-fit:cover; border:1px solid #ddd;
        }
        .edit-account-fields .edit-form-row label{
            font-weight:600; margin-bottom:8px; min-width:150px; text-align:right;
        }
        .edit-account-fields .edit-form-row > div{
           width: 100%;
        }
        .edit-account-fields .edit-form-row div input[type="text"], .edit-account-fields .edit-form-row div input[type="email"], .edit-account-fields .edit-form-row div input[type="password"], .edit-account-fields .edit-form-row div select, .edit-account-fields .edit-form-row div textarea, .edit-account-fields .edit-form-row div input[type="tel"], .edit-account-fields .edit-form-row div input[type="url"], .edit-account-fields .edit-form-row div input[type="number"], .edit-account-fields .edit-form-row div input[type="date"], .edit-account-fields .edit-form-row div input[type="search"], .edit-account-fields .edit-form-row div input[type="time"], .edit-account-fields .edit-form-row div select{
            width: 100%;
            background: #fff;
            padding: 10px; border: 1px solid #d9d9d9;
        }
        .rounded-8{
            border-radius:8px; padding:7px 20px;
        }
        .max-w-250{
          max-width:250px;
        }
 
 /* Container for the tabs */
  .wp-tab-container {
    display: flex;
    flex-wrap: wrap;
    max-width: 100%;
    font-family: sans-serif;
  
  }

  /* Hide the actual radio buttons */
  .wp-tab-input {
    display: none; 
  }

  /* Style the tab labels */


  /* Style the content area */
  .wp-tab-content {
    width: 100%;
    padding: 20px 0px;
    display: none;
    order: 1; /* Ensures content appears below labels */
    background: #fff;
    margin-top: -2px;
    border-top: 2px solid #eaeaea;
  }
   
 /* Show content for the checked radio */
  #tab-inquiries:checked ~ #content-inquiries,
  #tab-rfqs:checked ~ #content-rfqs,
  #tab-refund-aftersales:checked ~ #content-refund-aftersales,
  #tab-receive:checked ~ #content-receive,
  #tab-complete:checked ~ #content-complete,
  #tab-review:checked ~ #content-review { display: block; }

  .wp-tab-label {
    padding: 7px 25px;
    background: none;
    cursor: pointer;
    border: none;
    border-bottom: 2px solid transparent; 
    margin-right: 5px;
    font-weight: 500; width: 16%;
    text-align: center;
    color:#3a3a3a;
    transition: transform 0.2s ease-out; /* Makes the movement smooth */
  }

  .wp-tab-label span{
    display: inline-block;
    transition: transform 0.2s ease-out; /* Makes the movement smooth */
  }

  .wp-tab-label:hover > span {
    color: #ff4c00;
    transform: translateY(-3px); /* Moves the element up on the Y-axis */
  }

  /* Active Tab Styling */
  .wp-tab-input:checked + .wp-tab-label { 
    background: #fff; 
    border: none; 
    border-bottom: 2px solid #ff4c00; 
    position: relative; z-index: 2; color: #ff4c00;  font-weight: 500;
  }

  /* Table Styling */
  .inquiry-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    border: none; /* Removes outer border */
  }
  .inquiry-table th {
    text-align: left;
    padding: 8px 15px;
    border-bottom: 1px solid #eee; /* Header underline */
    border-right: none; /* Removes right border */
    color: #333;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    background-color: #f5f5f5;
  }
  .inquiry-table td {
    padding: 8px15px;
    border-bottom: 1px solid #eee; /* Only bottom row border */
    border-right: none; /* Removes right border */
    vertical-align: middle;
  }

/* Agent Photo Styling */
  .agent-cell { display: flex; align-items: center; gap: 8px; font-weight: 400; }
  .agent-photo img{
    width: 35px;
    height: 35px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid #ddd;
  }

  /* Mouseover Row Effect */
  .inquiry-table tr:hover {
    background-color: #fafafa !important;
  }

  /* Chat Now Button */
  .chat-btn {
    background-color: #2271b1;
    color: white;
    padding: 5px 16px;
    text-decoration: none;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 500;
    display: inline-block;
    transition: all 0.2s ease;
  }
  .chat-btn:hover {
    background-color: #135e96;
    color: #fff !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }
  .inquiries-product-cell{
    width:40%;
  }
  /* Initial hidden state */

  .btn-bluegreen{
    background-color: #2271b1;
    color: white !important;
      padding: 8px 14px;
    text-decoration: none;
    font-weight: 500;
    display: inline-block;
    transition: all 0.2s ease; font-size: 13px; line-height:1; border:none;
}
 .btn-bluegreen:hover{
    background-color: #48abfc; color: #000;
 }
.btn-orange{
    padding: 8px 14px;
    text-decoration: none; 
    font-weight: 500;
    transition: all 0.2s ease;
    background-color: #ff4c00;
    display: inline-block;
    color: #fff !important; 
    font-size: 13px; 
    line-height:1;
    border:none;
}
.btn-orange:hover{
    background-color: #ff9162; color: #000;
 }

.btn-darkgray{
    padding: 8px 14px; 
    text-decoration: none;
    font-weight: 500;
    display: inline-block; 
    transition: all 0.2s ease;  
    background-color: #555555;
    color: #fff !important; 
    font-size: 13px; 
    line-height:1;
}
.btn-darkgray:hover{
    background-color: #aeaeae; color: #000;
 }
.btn-whitegray{
    background-color: #b6b6b6;
    color: white !important;
    padding: 8px 14px;
    text-decoration: none;
    font-weight: 500;
    display: inline-block; 
    transition: all 0.2s ease; font-size: 13px; line-height:1;
}
.btn-whitegray:hover{
    background-color: #dbdbdb; color: #000;
 }
.popup {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 300px;
    background-color: #ffffff;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    border-radius: 8px;
    padding: 20px;
    border-left: 5px solid #ff4c00;
    /* Animation setup */
    transform: translateY(150%); /* Move it down off-screen */
    transition: transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    z-index: 1000;
}
.popup {
    position: fixed;
    bottom: 20px;
    right: 20px;
    
    /* Specified Size */
    min-width: 700px;
    min-height: 700px;
    max-width: 90vw; /* Responsive safety */
    max-height: 90vh; /* Responsive safety */
    
    background-color: #ffffff;
    box-shadow: -10px 10px 40px rgba(0,0,0,0.2);
    border-radius: 10px;
    /* overflow: hidden; */
    overflow-y: auto;
    display: flex;
    flex-direction: column;

    /* Animation */
    transform: translateY(110%); /* Hidden below */
    transition: transform 0.6s cubic-bezier(0.22, 1, 0.36, 1);
    z-index: 2000;
}

.popup.show {
    transform: translateY(0);
}

/* The Top-Right Close Icon */
.close-icon {
    position: absolute;
    top: 15px;
    right: 15px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #f5f5f5;
    border: none;
    font-size: 28px;
    color: #333;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.2s, transform 0.2s;
    z-index: 1001; /* Ensure icon is always on top */
}

.close-icon:hover {
    background-color: #e0e0e0;
    transform: scale(1.1);
}

/* Content Area */
.popup-scroll-container {
    padding: 40px;
    overflow-y: auto;
    height: 100%;
}

.popup-content h2 {
    margin-top: 0;
    font-family: sans-serif;
}
.d-flex{
  display:flex; gap:15px;
}
.flex-65{
    width:65%;
}
.flex-35{
    width:35%;
}
.orange-top-box{
  border:1px solid #e7e7e7; 
  border-top: 3px solid #ff4c00;
  padding:20px;
  margin-right:15px;
  transition: all 0.3s ease;
}
.my-dashboard-home .cards .card-item{
  box-shadow:0 2px 8px rgba(0,0,0,0.05);
  border:1px solid #eee; 
  border-top: 3px solid #ff4c00;
  padding:15px;
  margin-right:15px;
  transition: all 0.3s ease;
}

.flex-3{
    width:calc(100% / 3 - 20px);
}
.flex-2{
    width:calc(100% / 2 - 5px);
}
.my-dashboard-home .cards .card-item:hover{
  box-shadow:0 4px 12px rgba(0,0,0,0.1);
  border:1px solid #ff4c00; 
    border-top: 3px solid #ff4c00;
  padding:15px;
}
.card-item .card-head{
  font-size:18px; font-weight:600; margin-bottom:10px;
}
.card-item .card-footer{
  margin-top:10px; text-align:right;
}
.notice-info{
    background-color:#e6f7ff; border-left:4px solid #ff4c00; padding:15px 20px; border-radius:4px; margin-bottom:20px;
}

.woocommerce .woocommerce-customer-details address{
  padding: 0px;
}
.invoice-address-box {
  border:1px solid #e7e7e7; padding:20px; background-color:#fff; border-top:3px solid #ff4c00; 
}
.woocommerce-checkout table.shop_table {
    border: none;
    background-color: #ffffff;
    padding: 0;
}
#payment{
  border-top: 1px solid #bfbfbf;
}
.woocommerce-checkout #payment .wc_payment_methods {
    border: 1px solid #bfbfbf;
    border-top: none;
    background-color: #f7fdff;
}
.woocommerce-checkout #payment div.payment_box{
  color:#413f3f;
}
#payment div.form-row {
    padding: 1em 0px !important;
}
.woocommerce button.button.alt{
      background-color: #ff4c00 !important; border-radius:none;
}
.woocommerce button.button.alt:hover{
    background-color: #ff9162; color: #000;
 }
.order-footer-notes-content{
    min-height:160px; overflow-y:auto; padding-right:10px;
}


.iti__search-input-wrapper .iti__search-input  {
  padding-left: 30px !important;
}
.iti {
  width: 100%;
}
  </style>
</head>
<body <?php body_class(); ?>>
<?php get_header(); ?>
<div class="my-account">  
 <?php
if ( have_posts() ) :
	while ( have_posts() ) : the_post();
		the_content();
	endwhile;

endif;
?>
</div>
<?php wp_footer();
get_footer();
?>
</body>
</html>