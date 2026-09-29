<?php
/**
 * 制作実績一覧（/works/）。?industry=<業種のスラッグ> で絞り込む。
 */

get_header();

$current_industry = hinata_get_current_industry();
$archive_url      = get_post_type_archive_link( 'works' );
?>
<main class="works-page">
<?php get_template_part( 'template-parts/page-header', null, array( 'title' => '制作実績', 'eyebrow' => 'WORKS' ) ); ?>
<div class="container page-main">
	<p class="lead">さまざまな業種の企業・店舗のWeb制作をお手伝いしています。</p>

	<nav class="filter-row" aria-label="業種で絞り込む">
		<a href="<?php echo esc_url( $archive_url ); ?>"<?php echo '' === $current_industry ? ' aria-current="page"' : ''; ?>>すべて</a>
		<?php foreach ( hinata_get_industries() as $term ) : ?>
			<a href="<?php echo esc_url( add_query_arg( 'industry', $term->slug, $archive_url ) ); ?>"<?php echo $term->slug === $current_industry ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $term->name ); ?></a>
		<?php endforeach; ?>
	</nav>
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
