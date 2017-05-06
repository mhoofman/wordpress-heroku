<!doctype html>

  <html class="no-js"  <?php language_attributes(); ?>>

	<head>
		<meta charset="utf-8">
		
		<!-- Force IE to use the latest rendering engine available -->
		<meta http-equiv="X-UA-Compatible" content="IE=edge">

		<!-- Mobile Meta -->
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta class="foundation-mq">
		
		<!-- If Site Icon isn't set in customizer -->
		<?php if ( ! function_exists( 'has_site_icon' ) || ! has_site_icon() ) { ?>
			<!-- Icons & Favicons -->
			<link rel="icon" href="<?php echo get_template_directory_uri(); ?>/favicon.png">
			<link href="<?php echo get_template_directory_uri(); ?>/assets/images/apple-icon-touch.png" rel="apple-touch-icon" />
			<!--[if IE]>
				<link rel="shortcut icon" href="<?php echo get_template_directory_uri(); ?>/favicon.ico">
			<![endif]-->
			<meta name="msapplication-TileColor" content="#ec1f27">
			<meta name="msapplication-TileImage" content="<?php echo get_template_directory_uri(); ?>/assets/images/win8-tile-icon.png">
	    	<meta name="theme-color" content="#ec1f27">
	    	<link rel="apple-touch-icon-precomposed" sizes="57x57" href="<?php echo get_template_directory_uri(); ?>/assets/images/apple-touch-icon-57x57.png" />
			<link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo get_template_directory_uri(); ?>/assets/images/apple-touch-icon-114x114.png" />
			<link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo get_template_directory_uri(); ?>/assets/images/apple-touch-icon-72x72.png" />
			<link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?php echo get_template_directory_uri(); ?>/assets/images/apple-touch-icon-144x144.png" />
			<link rel="apple-touch-icon-precomposed" sizes="120x120" href="<?php echo get_template_directory_uri(); ?>/assets/images/apple-touch-icon-120x120.png" />
			<link rel="apple-touch-icon-precomposed" sizes="152x152" href="<?php echo get_template_directory_uri(); ?>/assets/images/apple-touch-icon-152x152.png" />
			<link rel="icon" type="image/png" href="<?php echo get_template_directory_uri(); ?>favicon-32x32.png" sizes="32x32" />
			<link rel="icon" type="image/png" href="<?php echo get_template_directory_uri(); ?>favicon-16x16.png" sizes="16x16" />
	    <?php } ?>

		<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
		<link rel="stylesheet" href="https://use.fontawesome.com/1f728dd4e9.css"> 

		<?php wp_head(); ?>

		<!-- Drop Google Analytics here -->
		<!-- end analytics -->

	</head>
	
	<!-- Uncomment this line if using the Off-Canvas Menu --> 
		
	<body <?php body_class(); ?>>

		<div class="off-canvas-wrapper">
							
			<?php get_template_part( 'parts/content', 'offcanvas' ); ?>
			
			<div class="off-canvas-content" data-off-canvas-content>
				
				<header class="header" role="banner">
						
					 <!-- This navs will be applied to the topbar, above all content 
						  To see additional nav styles, visit the /parts directory -->
					 <?php get_template_part( 'parts/nav', 'offcanvas-topbar' ); ?>
	 	
				</header> <!-- end .header -->