<?php
/**
 * 制作実績一覧の表示件数。
 */

add_action(
	'pre_get_posts',
	static function ( $query ) {
		if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'works' ) ) {
			return;
		}

		// 一覧は1ページにすべて表示する。
		$query->set( 'posts_per_page', -1 );
	}
);
