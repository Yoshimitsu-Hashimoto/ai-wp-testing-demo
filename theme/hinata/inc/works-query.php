<?php
/**
 * 制作実績一覧の表示件数と、業種による絞り込み。
 *
 * 一覧 /works/ に ?industry=<業種のスラッグ> を付けると、その業種の実績だけを表示する。
 */

/**
 * URLで指定された業種のスラッグ。指定がなければ空文字。
 *
 * 業種のスラッグとして使えない値が指定された場合は、どの業種にも一致しない '-' を返し、該当0件にする。
 */
function hinata_get_current_industry() {
	if ( ! isset( $_GET['industry'] ) || '' === $_GET['industry'] ) {
		return '';
	}
	$slug = is_string( $_GET['industry'] ) ? sanitize_title( wp_unslash( $_GET['industry'] ) ) : '';
	return '' !== $slug ? $slug : '-';
}

add_action(
	'pre_get_posts',
	static function ( $query ) {
		if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'works' ) ) {
			return;
		}

		// 一覧は1ページにすべて表示する。
		$query->set( 'posts_per_page', -1 );

		$industry = hinata_get_current_industry();
		if ( '' !== $industry ) {
			$query->set( 'industry', $industry );
		}
	}
);

/**
 * 絞り込みボタンに並べる業種。実績が0件の業種も含め、登録順に並べる。
 *
 * @return WP_Term[]
 */
function hinata_get_industries() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'industry',
			'hide_empty' => false,
			'orderby'    => 'term_id',
			'order'      => 'ASC',
		)
	);
	return is_array( $terms ) ? $terms : array();
}
