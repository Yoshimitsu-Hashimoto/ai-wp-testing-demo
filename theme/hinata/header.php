<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="site-header">
	<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
	<nav class="main-nav" aria-label="メインナビゲーション">
		<a href="<?php echo esc_url( home_url( '/#services' ) ); ?>">事業内容</a>
		<a href="<?php echo esc_url( get_post_type_archive_link( 'works' ) ); ?>"<?php echo hinata_nav_current( 'works' ); ?>>制作実績</a>
		<a href="<?php echo esc_url( hinata_page_url( 'company' ) ); ?>"<?php echo hinata_nav_current( 'company' ); ?>>会社案内</a>
		<a href="<?php echo esc_url( home_url( '/#news' ) ); ?>">お知らせ</a>
		<a class="nav-contact" href="<?php echo esc_url( hinata_page_url( 'contact' ) ); ?>"<?php echo hinata_nav_current( 'contact' ); ?>>お問い合わせ</a>
	</nav>
</header>
