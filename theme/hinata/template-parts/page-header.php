<?php
/**
 * パンくずとページタイトル。
 *
 * @var array{title: string, eyebrow?: string, crumbs?: array<int, array{label: string, url?: string}>} $args
 */

$crumbs = $args['crumbs'] ?? array( array( 'label' => $args['title'] ) );
?>
<div class="container breadcrumb">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>">トップ</a>
	<?php foreach ( $crumbs as $crumb ) : ?>
		　›　<?php if ( ! empty( $crumb['url'] ) ) : ?><a href="<?php echo esc_url( $crumb['url'] ); ?>"><?php echo esc_html( $crumb['label'] ); ?></a><?php else : ?><?php echo esc_html( $crumb['label'] ); ?><?php endif; ?>
	<?php endforeach; ?>
</div>
<div class="page-title">
	<h1><?php echo esc_html( $args['title'] ); ?></h1>
	<?php if ( ! empty( $args['eyebrow'] ) ) : ?>
		<span class="eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></span>
	<?php endif; ?>
</div>
