<?php
/**
 * お問い合わせ（固定ページ contact）。送信処理は inc/contact-form.php。
 */

get_header();

$state  = hinata_contact_state();
$values = $state['values'];
$errors = $state['errors'];

/**
 * 項目にエラーがあれば aria 属性を出力する。
 *
 * @param string $field 項目名。
 */
$invalid_attrs = static function ( $field ) use ( $errors ) {
	return isset( $errors[ $field ] ) ? sprintf( ' aria-invalid="true" aria-describedby="error-%s"', esc_attr( $field ) ) : '';
};

/**
 * 項目のエラーメッセージを出力する。
 *
 * @param string $field 項目名。
 */
$field_error = static function ( $field ) use ( $errors ) {
	if ( isset( $errors[ $field ] ) ) {
		printf( '<p class="field-error" id="error-%s">%s</p>', esc_attr( $field ), esc_html( $errors[ $field ] ) );
	}
};
?>
<main>
<?php get_template_part( 'template-parts/page-header', null, array( 'title' => 'お問い合わせ', 'eyebrow' => 'CONTACT' ) ); ?>
<div class="container page-main">
	<p class="lead">Webサイトの制作・リニューアル、運用のご相談など、お気軽にお問い合わせください。<br>必須項目をご入力のうえ、送信してください。</p>

	<?php if ( $errors ) : ?>
		<div class="form-errors" role="alert">
			<p>入力内容に問題があります。</p>
			<ul>
				<?php foreach ( $errors as $message ) : ?>
					<li><?php echo esc_html( $message ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<?php // 入力チェックはサーバー側で行い、結果を画面に表示する（ブラウザ標準のチェックは使わない）。 ?>
	<form class="contact-form" action="<?php echo esc_url( hinata_page_url( 'contact' ) ); ?>" method="post" novalidate>
		<input type="hidden" name="hinata_contact" value="1">
		<?php wp_nonce_field( 'hinata_contact', 'hinata_contact_nonce', false ); ?>

		<div class="form-row">
			<label for="contact_name">お名前 <span class="required">必須</span></label>
			<input id="contact_name" name="contact_name" type="text" autocomplete="name" placeholder="例：山田 太郎" value="<?php echo esc_attr( $values['name'] ); ?>" required<?php echo $invalid_attrs( 'name' ); ?>>
			<?php $field_error( 'name' ); ?>
		</div>

		<div class="form-row">
			<label for="contact_email">メールアドレス <span class="required">必須</span></label>
			<input id="contact_email" name="contact_email" type="email" autocomplete="email" placeholder="例：yamada@example.com" value="<?php echo esc_attr( $values['email'] ); ?>" required<?php echo $invalid_attrs( 'email' ); ?>>
			<?php $field_error( 'email' ); ?>
		</div>

		<div class="form-row">
			<label for="contact_message">お問い合わせ内容 <span class="required">必須</span></label>
			<textarea id="contact_message" name="contact_message" placeholder="ご相談内容をご記入ください。" required<?php echo $invalid_attrs( 'message' ); ?>><?php echo esc_textarea( $values['message'] ); ?></textarea>
			<?php $field_error( 'message' ); ?>
		</div>

		<div class="consent">
			<label><input type="checkbox" name="contact_consent" value="agree" required<?php checked( 'agree', $values['consent'] ); ?><?php echo $invalid_attrs( 'consent' ); ?>> <a href="<?php echo esc_url( hinata_page_url( 'privacy' ) ); ?>">プライバシーポリシー</a>に同意する <span class="required">必須</span></label>
			<small>送信前に個人情報の取り扱いをご確認ください。</small>
			<?php $field_error( 'consent' ); ?>
		</div>

		<div class="form-actions">
			<button class="button" type="submit">送信する　→</button>
			<p class="demo-notice">このフォームは講座用デモです。テスト用の情報を入力してください。</p>
		</div>
	</form>
</div>
</main>
<?php
get_footer();
