<?php
/**
 * カスタム投稿タイプ「制作実績」とカスタムタクソノミー「業種」。
 */

add_action(
	'init',
	static function () {
		register_post_type(
			'works',
			array(
				'labels'        => array(
					'name'          => '制作実績',
					'singular_name' => '制作実績',
					'add_new_item'  => '制作実績を追加',
					'edit_item'     => '制作実績を編集',
					'all_items'     => '制作実績一覧',
				),
				'public'        => true,
				'has_archive'   => true,
				'rewrite'       => array( 'slug' => 'works' ),
				'menu_position' => 5,
				'menu_icon'     => 'dashicons-portfolio',
				'show_in_rest'  => true,
				'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
			)
		);

		// 絞り込みは inc/works-query.php で行うため、業種ごとのアーカイブページは作らない。
		register_taxonomy(
			'industry',
			'works',
			array(
				'labels'             => array(
					'name'          => '業種',
					'singular_name' => '業種',
					'add_new_item'  => '業種を追加',
					'edit_item'     => '業種を編集',
				),
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => true,
				'show_in_rest'       => true,
				'show_admin_column'  => true,
				'hierarchical'       => true,
				'query_var'          => false,
				'rewrite'            => false,
			)
		);
	}
);

// テーマ有効化時にパーマリンクを更新し、/works/ を有効にする。
add_action( 'after_switch_theme', 'flush_rewrite_rules' );
