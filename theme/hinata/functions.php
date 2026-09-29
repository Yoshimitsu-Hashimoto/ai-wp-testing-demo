<?php
/**
 * テーマの初期設定と、機能ごとのファイルの読み込み。
 */

require_once __DIR__ . '/inc/template-tags.php';
require_once __DIR__ . '/inc/post-types.php';
require_once __DIR__ . '/inc/works-meta.php';
require_once __DIR__ . '/inc/works-query.php';
require_once __DIR__ . '/inc/contact-form.php';

add_action(
	'after_setup_theme',
	static function () {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	}
);

add_action(
	'wp_enqueue_scripts',
	static function () {
		$path = '/assets/css/main.css';
		wp_enqueue_style(
			'hinata-main',
			get_theme_file_uri( $path ),
			array(),
			(string) filemtime( get_theme_file_path( $path ) )
		);
	}
);

add_filter(
	'document_title_separator',
	static function () {
		return '|';
	}
);
