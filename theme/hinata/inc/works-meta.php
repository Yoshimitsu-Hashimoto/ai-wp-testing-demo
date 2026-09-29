<?php
/**
 * 制作実績のカスタムフィールド「顧客名・制作年・担当範囲」。
 */

/**
 * カスタムフィールドの定義。キーはメタキー。
 *
 * @return array<string, array{label: string, description: string}>
 */
function hinata_works_fields() {
	return array(
		'client_name'     => array(
			'label'       => '顧客名',
			'description' => '例：和食処 やまの葉',
		),
		'production_year' => array(
			'label'       => '制作年',
			'description' => '西暦4桁の数字（例：2026）。未入力の場合、詳細ページに表示しません。',
		),
		'scope'           => array(
			'label'       => '担当範囲',
			'description' => '例：企画・デザイン・WordPress構築',
		),
	);
}

/**
 * 制作年を西暦4桁の文字列に揃える。不正な値は空文字にする。
 *
 * @param mixed $value 入力値。
 */
function hinata_sanitize_year( $value ) {
	$value = trim( (string) $value );
	return preg_match( '/\A\d{4}\z/', $value ) ? $value : '';
}

add_action(
	'init',
	static function () {
		foreach ( array_keys( hinata_works_fields() ) as $key ) {
			register_post_meta(
				'works',
				$key,
				array(
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => false,
					'sanitize_callback' => 'production_year' === $key ? 'hinata_sanitize_year' : 'sanitize_text_field',
					'auth_callback'     => static function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}
);

add_action(
	'add_meta_boxes_works',
	static function () {
		add_meta_box( 'hinata-works-fields', '実績の情報', 'hinata_render_works_meta_box', 'works', 'normal', 'high' );
	}
);

/**
 * 管理画面の入力欄。
 *
 * @param WP_Post $post 編集中の投稿。
 */
function hinata_render_works_meta_box( $post ) {
	wp_nonce_field( 'hinata_save_works_fields', 'hinata_works_fields_nonce' );
	echo '<table class="form-table" role="presentation"><tbody>';
	foreach ( hinata_works_fields() as $key => $field ) {
		$value = get_post_meta( $post->ID, $key, true );
		printf(
			'<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><input type="text" class="regular-text" id="%1$s" name="%1$s" value="%3$s"><p class="description">%4$s</p></td></tr>',
			esc_attr( $key ),
			esc_html( $field['label'] ),
			esc_attr( $value ),
			esc_html( $field['description'] )
		);
	}
	echo '</tbody></table>';
}

add_action(
	'save_post_works',
	static function ( $post_id ) {
		if ( ! isset( $_POST['hinata_works_fields_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_POST['hinata_works_fields_nonce'] ), 'hinata_save_works_fields' )
			|| ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			|| ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		foreach ( array_keys( hinata_works_fields() ) as $key ) {
			$value = isset( $_POST[ $key ] ) && is_string( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
			// 保存時のサニタイズは register_post_meta の sanitize_callback で行われる。
			// 制作年は不正な値を未入力として扱うため、ここで先に確認する。
			if ( 'production_year' === $key ) {
				$value = hinata_sanitize_year( $value );
			}
			if ( '' === trim( $value ) ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $value );
			}
		}
	}
);

/**
 * 詳細ページに表示する項目。値が空の項目は見出しごと含めない。
 *
 * @param int $post_id 制作実績のID。
 * @return array<int, array{label: string, value: string}>
 */
function hinata_get_works_meta_rows( $post_id ) {
	$rows = array();
	foreach ( hinata_works_fields() as $key => $field ) {
		$value = trim( (string) get_post_meta( $post_id, $key, true ) );
		if ( 'production_year' === $key ) {
			$value .= '年';
		}
		if ( '' === $value ) {
			continue;
		}
		$rows[] = array(
			'label' => $field['label'],
			'value' => $value,
		);
	}
	return $rows;
}
