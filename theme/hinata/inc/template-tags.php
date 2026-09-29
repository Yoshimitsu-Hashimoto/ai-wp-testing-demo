<?php
/**
 * テンプレートで使う共通の関数。
 */

/**
 * 固定ページのURL。ページがなければ /<slug>/ を返す。
 *
 * @param string $slug 固定ページのスラッグ。
 */
function hinata_page_url( $slug ) {
	$page = get_page_by_path( $slug );
	return $page ? get_permalink( $page ) : home_url( '/' . $slug . '/' );
}

/**
 * ヘッダーのナビゲーションで、現在のページに aria-current を付ける。
 *
 * @param string $key works | company | contact
 */
function hinata_nav_current( $key ) {
	$current = ( 'works' === $key && ( is_post_type_archive( 'works' ) || is_singular( 'works' ) ) )
		|| ( in_array( $key, array( 'company', 'contact' ), true ) && is_page( $key ) );
	return $current ? ' aria-current="page"' : '';
}

/**
 * 制作実績の業種名（最初の1件）。
 *
 * @param int $post_id 制作実績のID。
 */
function hinata_get_work_industry_name( $post_id ) {
	$terms = get_the_terms( $post_id, 'industry' );
	return ( is_array( $terms ) && $terms ) ? $terms[0]->name : '';
}
