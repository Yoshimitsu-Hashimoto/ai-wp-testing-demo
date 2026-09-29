<?php
/**
 * 汎用テンプレート（固定ページ・お知らせ・404など、専用テンプレートがないページ）。
 */

get_header();

if ( is_404() ) {
	$page_title = 'ページが見つかりません';
} elseif ( is_singular() ) {
	$page_title = get_the_title();
} else {
	$page_title = 'お知らせ';
}
?>
<main>
<?php get_template_part( 'template-parts/page-header', null, array( 'title' => $page_title, 'eyebrow' => is_404() ? 'NOT FOUND' : '' ) ); ?>
<div class="container page-main">
	<div class="page-content entry-content">
		<?php if ( is_404() ) : ?>
			<p>お探しのページは移動または削除された可能性があります。</p>
			<p class="center-link"><a class="outline-button" href="<?php echo esc_url( home_url( '/' ) ); ?>">トップページへ戻る　→</a></p>
		<?php elseif ( is_singular() ) : ?>
			<?php
			while ( have_posts() ) :
				the_post();
				if ( is_singular( 'post' ) ) :
					?>
					<p class="news-date"><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></p>
					<?php
				endif;
				the_content();
			endwhile;
			?>
		<?php elseif ( have_posts() ) : ?>
			<ul>
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
				<?php endwhile; ?>
			</ul>
		<?php else : ?>
			<p>記事はありません。</p>
		<?php endif; ?>
	</div>
</div>
</main>
<?php
get_footer();
