<?php
/**
 * お問い合わせの送信完了（固定ページ thanks）。
 */

get_header();
?>
<main>
<?php
get_template_part(
	'template-parts/page-header',
	null,
	array(
		'title'   => 'お問い合わせ',
		'eyebrow' => 'CONTACT',
		'crumbs'  => array(
			array(
				'label' => 'お問い合わせ',
				'url'   => hinata_page_url( 'contact' ),
			),
			array( 'label' => '送信完了' ),
		),
	)
);
?>
<section class="container thanks-content">
	<div class="checkmark" aria-hidden="true">✓</div>
	<h2>お問い合わせを受け付けました。</h2>
	<p>ご入力いただき、ありがとうございます。</p>
	<p>このページは講座用デモです。入力内容は講座用の確認メールボックスにだけ届き、実際の返信はありません。</p>
	<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>">トップページへ戻る　→</a>
</section>
</main>
<?php
get_footer();
