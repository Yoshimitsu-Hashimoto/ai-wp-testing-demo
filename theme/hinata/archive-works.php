<?php
/**
 * 制作実績一覧（/works/）。
 */

get_header();
?>
<main class="works-page">
<?php get_template_part( 'template-parts/page-header', null, array( 'title' => '制作実績', 'eyebrow' => 'WORKS' ) ); ?>
<div class="container page-main">
	<p class="lead">さまざまな業種の企業・店舗のWeb制作をお手伝いしています。</p>

	<p class="result-count"><?php echo esc_html( sprintf( '%d件の実績', $wp_query->found_posts ) ); ?></p>

	<?php if ( have_posts() ) : ?>
		<div class="works-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/work-card' );
			endwhile;
			?>
		</div>
	<?php else : ?>
		<p class="works-empty">該当する実績はありません。</p>
	<?php endif; ?>
</div>
<?php get_template_part( 'template-parts/contact-band' ); ?>
</main>
<?php
get_footer();
