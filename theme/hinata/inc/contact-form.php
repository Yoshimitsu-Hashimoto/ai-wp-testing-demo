<?php
/**
 * お問い合わせフォーム（固定ページ contact）の送信処理。
 *
 * 入力を検証し、問題がなければ管理者メールアドレス宛てに内容を送信して、
 * 送信完了ページ（固定ページ thanks）へ移動する。問題があればエラーを表示して入力内容を残す。
 */

/**
 * フォームの状態。テンプレート page-contact.php から参照する。
 *
 * @return array{values: array<string, string>, errors: array<string, string>}
 */
function hinata_contact_state() {
	global $hinata_contact_state;
	if ( ! is_array( $hinata_contact_state ) ) {
		$hinata_contact_state = array(
			'values' => array(
				'name'    => '',
				'email'   => '',
				'message' => '',
				'consent' => '',
			),
			'errors' => array(),
		);
	}
	return $hinata_contact_state;
}

/**
 * 入力を検証する。
 *
 * @param array<string, string> $values 入力値。
 * @return array<string, string> 項目名 => エラーメッセージ
 */
function hinata_validate_contact( $values ) {
	$errors = array();

	if ( '' === $values['name'] ) {
		$errors['name'] = 'お名前を入力してください。';
	}

	if ( '' === $values['email'] ) {
		$errors['email'] = 'メールアドレスを入力してください。';
	} elseif ( ! is_email( $values['email'] ) ) {
		$errors['email'] = 'メールアドレスの形式が正しくありません。';
	}

	if ( '' === $values['message'] ) {
		$errors['message'] = 'お問い合わせ内容を入力してください。';
	}

	if ( 'agree' !== $values['consent'] ) {
		$errors['consent'] = 'プライバシーポリシーへの同意が必要です。';
	}

	return $errors;
}

add_action(
	'template_redirect',
	static function () {
		if ( ! is_page( 'contact' ) || 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || ! isset( $_POST['hinata_contact'] ) ) {
			return;
		}

		global $hinata_contact_state;
		$hinata_contact_state = hinata_contact_state();

		$post = wp_unslash( $_POST );
		// 文字列以外（配列など）が送られた場合は未入力として扱う。
		// 前後の空白は全角スペースも含めて取り除き、空白だけの入力を未入力とみなす。
		$field  = static function ( $key ) use ( $post ) {
			$value = isset( $post[ $key ] ) && is_string( $post[ $key ] ) ? $post[ $key ] : '';
			return (string) preg_replace( '/\A[\s\x{3000}]+|[\s\x{3000}]+\z/u', '', $value );
		};
		$values = array(
			'name'    => sanitize_text_field( $field( 'contact_name' ) ),
			'email'   => sanitize_text_field( $field( 'contact_email' ) ),
			'message' => sanitize_textarea_field( $field( 'contact_message' ) ),
			'consent' => sanitize_key( $field( 'contact_consent' ) ),
		);
		$hinata_contact_state['values'] = $values;

		if ( ! wp_verify_nonce( sanitize_key( $field( 'hinata_contact_nonce' ) ), 'hinata_contact' ) ) {
			$hinata_contact_state['errors'] = array( 'form' => '送信の有効期限が切れました。もう一度送信してください。' );
			return;
		}

		$errors = hinata_validate_contact( $values );
		if ( $errors ) {
			$hinata_contact_state['errors'] = $errors;
			return;
		}

		$subject = sprintf( '【%s】お問い合わせ（%s 様）', get_bloginfo( 'name' ), $values['name'] );
		$body    = implode(
			"\n",
			array(
				'お問い合わせフォームから次の内容が送信されました。',
				'',
				'お名前：' . $values['name'],
				'メールアドレス：' . $values['email'],
				'',
				'お問い合わせ内容：',
				$values['message'],
			)
		);
		$headers = array( 'Reply-To: ' . $values['email'] );

		if ( ! wp_mail( get_option( 'admin_email' ), $subject, $body, $headers ) ) {
			$hinata_contact_state['errors'] = array( 'form' => '送信に失敗しました。時間をおいてもう一度お試しください。' );
			return;
		}

		wp_safe_redirect( hinata_page_url( 'thanks' ), 303 );
		exit;
	}
);
