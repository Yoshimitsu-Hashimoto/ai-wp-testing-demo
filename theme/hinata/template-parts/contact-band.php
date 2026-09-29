<?php
/**
 * ページ下部のお問い合わせ案内。
 *
 * @var array{title?: string, text?: string} $args
 */
?>
<section class="contact-band">
	<h2><?php echo esc_html( $args['title'] ?? 'お気軽にご相談ください' ); ?></h2>
	<p><?php echo esc_html( $args['text'] ?? 'Webサイトの制作・リニューアル、運用のご相談など、お気軽にお問い合わせください。' ); ?></p>
	<a class="button" href="<?php echo esc_url( hinata_page_url( 'contact' ) ); ?>">お問い合わせ　→</a>
</section>
