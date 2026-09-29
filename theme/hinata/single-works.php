<?php
/**
 * 制作実績の詳細。
 */

get_header();

while ( have_posts() ) :
	the_post();
	$industry  = hinata_get_work_industry_name( get_the_ID() );
	$meta_rows = hinata_get_works_meta_rows( get_the_ID() );
	?>
<main>
	<?php
	get_template_part(
		'template-parts/page-header',
		null,
		array(
			'title'   => '制作実績',
			'eyebrow' => 'WORKS',
			'crumbs'  => array(
				array(
					'label' => '制作実績',
					'url'   => get_post_type_archive_link( 'works' ),
				),
				array( 'label' => get_the_title() ),
			),
		)
	);
	?>
<div class="container page-main">
	<article class="work-detail">
		<div class="detail-top">
			<?php if ( $industry ) : ?>
				<span class="work-category"><?php echo esc_html( $industry ); ?></span>
			<?php endif; ?>
			<h2><?php the_title(); ?></h2>
			<?php if ( has_excerpt() ) : ?>
				<p><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'full', array( 'class' => 'detail-hero' ) ); ?>
		<?php endif; ?>

		<?php if ( $meta_rows ) : ?>
			<table class="meta-table">
				<tbody>
					<?php foreach ( $meta_rows as $row ) : ?>
						<tr><th scope="row"><?php echo esc_html( $row['label'] ); ?></th><td><?php echo esc_html( $row['value'] ); ?></td></tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<div class="entry-content">
			<?php the_content(); ?>
		</div>

		<p class="center-link"><a class="outline-button" href="<?php echo esc_url( get_post_type_archive_link( 'works' ) ); ?>">制作実績一覧に戻る　→</a></p>
	</article>
</div>
	<?php
	get_template_part(
		'template-parts/contact-band',
		null,
		array(
			'title' => 'Webサイト制作のご相談はこちら',
			'text'  => '貴社の想いを丁寧に伺い、課題に合わせたWebサイトをご提案します。',
		)
	);
	?>
</main>
	<?php
endwhile;

get_footer();
