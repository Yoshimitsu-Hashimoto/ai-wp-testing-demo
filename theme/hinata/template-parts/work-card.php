<?php
/**
 * 制作実績のカード。ループ内で使う。
 *
 * @var array{heading?: string} $args
 */

$heading  = $args['heading'] ?? 'h2';
$industry = hinata_get_work_industry_name( get_the_ID() );
?>
<a class="work-card" href="<?php the_permalink(); ?>">
	<?php if ( has_post_thumbnail() ) : ?>
		<?php the_post_thumbnail( 'large' ); ?>
	<?php else : ?>
		<div class="work-thumb-placeholder"></div>
	<?php endif; ?>
	<<?php echo tag_escape( $heading ); ?> class="work-title"><?php the_title(); ?></<?php echo tag_escape( $heading ); ?>>
	<?php if ( $industry ) : ?>
		<span class="work-category"><?php echo esc_html( $industry ); ?></span>
	<?php endif; ?>
</a>
