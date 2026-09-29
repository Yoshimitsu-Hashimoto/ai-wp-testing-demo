<?php
/**
 * 講座用の固定テストデータを投入する。scripts/seed.sh から wp eval-file で実行する。
 *
 * 既存の投稿・固定ページ・制作実績・メディア・業種をすべて削除してから作り直すため、
 * 何度実行しても同じ状態になる。内容は docs/test-data.md と一致させること。
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// 既存データの削除
// ---------------------------------------------------------------------------

$existing = get_posts(
	array(
		'post_type'      => array( 'post', 'page', 'works', 'attachment' ),
		'post_status'    => array_keys( get_post_stati() ), // ゴミ箱・自動下書きも含める
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
);
foreach ( $existing as $id ) {
	if ( 'attachment' === get_post_type( $id ) ) {
		wp_delete_attachment( $id, true );
	} else {
		wp_delete_post( $id, true );
	}
}

$terms = get_terms(
	array(
		'taxonomy'   => 'industry',
		'hide_empty' => false,
		'fields'     => 'ids',
	)
);
foreach ( is_array( $terms ) ? $terms : array() as $term_id ) {
	wp_delete_term( $term_id, 'industry' );
}

// ---------------------------------------------------------------------------
// サイトの設定
// ---------------------------------------------------------------------------

update_option( 'blogdescription', '想いをかたちに、事業の力に。' );
update_option( 'show_on_front', 'posts' );
update_option( 'wp_page_for_privacy_policy', 0 );

// ---------------------------------------------------------------------------
// 業種（絞り込みボタンは作成順に並ぶ）
// ---------------------------------------------------------------------------

$industries = array(
	'food'      => '飲食',
	'making'    => '製造',
	'service'   => 'サービス',
	'medical'   => '医療', // 実績0件の業種
);
foreach ( $industries as $slug => $name ) {
	$result = wp_insert_term( $name, 'industry', array( 'slug' => $slug ) );
	if ( is_wp_error( $result ) ) {
		WP_CLI::error( $result );
	}
}

// ---------------------------------------------------------------------------
// 画像
// ---------------------------------------------------------------------------

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$images = array(
	'food'    => array( 'japanese-restaurant.jpg', '季節の和食料理' ),
	'making'  => array( 'precision-machining.jpg', '精密加工の現場' ),
	'service' => array( 'community-care.jpg', '地域の人に寄り添うスタッフ' ),
);
$image_ids = array();
foreach ( $images as $key => $image ) {
	$source = get_theme_file_path( 'assets/images/' . $image[0] );
	$tmp    = wp_tempnam( $image[0] );
	copy( $source, $tmp );
	$id = media_handle_sideload(
		array(
			'name'     => $image[0],
			'tmp_name' => $tmp,
		),
		0
	);
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id );
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $image[1] );
	$image_ids[ $key ] = $id;
}

// ---------------------------------------------------------------------------
// 制作実績
// ---------------------------------------------------------------------------

/**
 * 実績の本文。
 *
 * @param string $summary 制作の概要。
 * @param string $points  取り組んだこと。
 */
$works_content = static function ( $summary, $points ) {
	return "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">制作の概要</h2>\n<!-- /wp:heading -->\n\n"
		. "<!-- wp:paragraph -->\n<p>{$summary}</p>\n<!-- /wp:paragraph -->\n\n"
		. "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">取り組んだこと</h2>\n<!-- /wp:heading -->\n\n"
		. "<!-- wp:paragraph -->\n<p>{$points}</p>\n<!-- /wp:paragraph -->";
};

// 公開日の新しい順に一覧へ並ぶ。year が空の実績は「制作年」を登録しない。
$works = array(
	array(
		'slug'     => 'yamanoha',
		'title'    => '和食処 やまの葉 様',
		'client'   => '和食処 やまの葉',
		'industry' => 'food',
		'year'     => '2026',
		'scope'    => '企画・デザイン・WordPress構築',
		'date'     => '2026-09-01 10:00:00',
		'status'   => 'publish',
		'excerpt'  => '季節の味わいと、お店の魅力が伝わるサイトに。',
		'summary'  => '地元の旬の食材を使ったお料理と、落ち着いた和の空間が伝わるよう、写真を中心にしたシンプルで上質なデザインで制作しました。',
		'points'   => 'お料理の写真を生かした、落ち着きのあるデザイン。お店で更新しやすい、お知らせとメニューの構成。',
	),
	array(
		'slug'     => 'takahashi-seimitsu',
		'title'    => '高橋精密工業株式会社 様',
		'client'   => '高橋精密工業株式会社',
		'industry' => 'making',
		'year'     => '2026',
		'scope'    => 'デザイン・WordPress構築',
		'date'     => '2026-08-20 10:00:00',
		'status'   => 'publish',
		'excerpt'  => '確かな技術力が伝わる、信頼感のあるサイトに。',
		'summary'  => '精密加工の技術と設備を、取引先の担当者が短時間で把握できるよう情報を整理しました。',
		'points'   => '設備一覧と加工事例のページを新設。問い合わせまでの導線を短く。',
	),
	array(
		'slug'     => 'haruka-service',
		'title'    => 'はるかサービス 様',
		'client'   => 'はるかサービス',
		'industry' => 'service',
		'year'     => '2025',
		'scope'    => '企画・デザイン・WordPress構築・運用サポート',
		'date'     => '2026-08-05 10:00:00',
		'status'   => 'publish',
		'excerpt'  => '利用者とご家族が安心できる、やさしいサイトに。',
		'summary'  => '介護サービスの内容と一日の流れを、写真とわかりやすい言葉で紹介するサイトを制作しました。',
		'points'   => '大きめの文字と、読みやすい行間。サービス内容ごとのページ構成。',
	),
	array(
		'slug'     => 'komorebi',
		'title'    => 'カフェ こもれび 様',
		'client'   => 'カフェ こもれび',
		'industry' => 'food',
		'year'     => '2025',
		'scope'    => 'デザイン・コーディング',
		'date'     => '2026-07-15 10:00:00',
		'status'   => 'publish',
		'excerpt'  => 'ゆったりした店内の空気感が伝わるサイトに。',
		'summary'  => '季節ごとに変わるメニューと、店内の雰囲気を伝える写真を中心に構成しました。',
		'points'   => 'メニューを店主が更新できる仕組み。営業日カレンダーの掲載。',
	),
	array(
		'slug'     => 'aoki-seisakusho',
		'title'    => '株式会社青木製作所 様',
		'client'   => '株式会社青木製作所',
		'industry' => 'making',
		'year'     => '2025',
		'scope'    => '企画・デザイン・WordPress構築',
		'date'     => '2026-06-25 10:00:00',
		'status'   => 'publish',
		'excerpt'  => '採用にもつながる、ものづくりの現場を伝えるサイトに。',
		'summary'  => '製品紹介に加えて、働く人と現場の様子を伝える採用ページを制作しました。',
		'points'   => '社員インタビューの掲載。製品カテゴリーごとの一覧ページ。',
	),
	array(
		'slug'     => 'midori-fudosan',
		'title'    => 'みどり不動産 様',
		'client'   => 'みどり不動産',
		'industry' => 'service',
		'year'     => '2024',
		'scope'    => 'WordPress構築・運用サポート',
		'date'     => '2026-06-10 10:00:00',
		'status'   => 'publish',
		'excerpt'  => '地域の暮らしに寄り添う、相談しやすいサイトに。',
		'summary'  => '物件情報だけでなく、地域の暮らしや相談の流れを伝えるページを用意しました。',
		'points'   => '相談の流れを図で説明。スタッフ紹介ページの新設。',
	),
	array(
		'slug'     => 'muginone',
		'title'    => 'ベーカリー麦の音 様',
		'client'   => 'ベーカリー麦の音',
		'industry' => 'food',
		'year'     => '2024',
		'scope'    => 'デザイン・WordPress構築',
		'date'     => '2026-05-20 10:00:00',
		'status'   => 'publish',
		'excerpt'  => '焼きたての香りが伝わるような、あたたかいサイトに。',
		'summary'  => '毎日の焼き上がり時間と、季節限定のパンを紹介するサイトを制作しました。',
		'points'   => '焼き上がり時間の掲載。季節限定商品のお知らせ。',
	),
	array(
		'slug'     => 'towa-mokko',
		'title'    => '東和木工株式会社 様',
		'client'   => '東和木工株式会社',
		'industry' => 'making',
		'year'     => '2024',
		'scope'    => '企画・デザイン',
		'date'     => '2026-05-01 10:00:00',
		'status'   => 'publish',
		'excerpt'  => '木のぬくもりと職人の技が伝わるサイトに。',
		'summary'  => '家具と木工製品の魅力を、素材と工程の写真で伝えるデザインにしました。',
		'points'   => '製品ごとの素材と工程の紹介。オーダー相談フォームへの導線。',
	),
	array(
		'slug'     => 'sakura-gakushu',
		'title'    => 'さくら学習室 様',
		'client'   => 'さくら学習室',
		'industry' => 'service',
		'year'     => '', // 制作年が未入力の実績
		'scope'    => 'デザイン・WordPress構築',
		'date'     => '2026-04-15 10:00:00',
		'status'   => 'publish',
		'excerpt'  => '保護者が安心して相談できる、落ち着いたサイトに。',
		'summary'  => '教室の方針と授業の様子、料金をわかりやすく伝えるサイトを制作しました。',
		'points'   => '料金表の整理。体験授業の申し込みまでの導線。',
	),
	array(
		'slug'     => 'hidamari-draft',
		'title'    => '喫茶 ひだまり 様',
		'client'   => '喫茶 ひだまり',
		'industry' => 'food',
		'year'     => '2026',
		'scope'    => 'デザイン',
		'date'     => '2026-09-10 10:00:00',
		'status'   => 'draft', // 未公開の実績
		'excerpt'  => '公開前の実績です。',
		'summary'  => '公開準備中の実績です。',
		'points'   => '公開準備中の実績です。',
	),
);

foreach ( $works as $work ) {
	$post_id = wp_insert_post(
		array(
			'post_type'     => 'works',
			'post_name'     => $work['slug'],
			'post_title'    => $work['title'],
			'post_status'   => $work['status'],
			'post_date'     => $work['date'],
			'post_excerpt'  => $work['excerpt'],
			'post_content'  => $works_content( $work['summary'], $work['points'] ),
			'post_author'   => 1,
			'comment_status' => 'closed',
		),
		true
	);
	if ( is_wp_error( $post_id ) ) {
		WP_CLI::error( $post_id );
	}
	wp_set_object_terms( $post_id, $work['industry'], 'industry' );
	set_post_thumbnail( $post_id, $image_ids[ $work['industry'] ] );
	update_post_meta( $post_id, 'client_name', $work['client'] );
	update_post_meta( $post_id, 'scope', $work['scope'] );
	if ( '' !== $work['year'] ) {
		update_post_meta( $post_id, 'production_year', $work['year'] );
	}
}

// ---------------------------------------------------------------------------
// 固定ページ（本文はテーマのテンプレートに書かれている）
// ---------------------------------------------------------------------------

$pages = array(
	'company' => '会社案内',
	'contact' => 'お問い合わせ',
	'thanks'  => '送信完了',
	'privacy' => 'プライバシーポリシー',
);
foreach ( $pages as $slug => $title ) {
	$page_id = wp_insert_post(
		array(
			'post_type'      => 'page',
			'post_name'      => $slug,
			'post_title'     => $title,
			'post_status'    => 'publish',
			'post_author'    => 1,
			'comment_status' => 'closed',
		),
		true
	);
	if ( is_wp_error( $page_id ) ) {
		WP_CLI::error( $page_id );
	}
}

// ---------------------------------------------------------------------------
// お知らせ
// ---------------------------------------------------------------------------

wp_insert_post(
	array(
		'post_type'      => 'post',
		'post_name'      => 'site-open',
		'post_title'     => 'コーポレートサイトを公開しました。',
		'post_status'    => 'publish',
		'post_date'      => '2026-09-15 10:00:00',
		'post_content'   => "<!-- wp:paragraph -->\n<p>株式会社ひなたのコーポレートサイトを公開しました。制作実績やサービス内容をご紹介しています。</p>\n<!-- /wp:paragraph -->",
		'post_author'    => 1,
		'comment_status' => 'closed',
	)
);

flush_rewrite_rules();

WP_CLI::success( sprintf( 'テストデータを投入しました（制作実績 %d 件、業種 %d 件）。', count( $works ), count( $industries ) ) );
