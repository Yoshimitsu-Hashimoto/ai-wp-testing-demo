<?php
/**
 * トップページ。
 */

get_header();

$office_image = get_theme_file_uri( 'assets/images/office-team.jpg' );
$latest_news  = get_posts(
	array(
		'post_type'      => 'post',
		'posts_per_page' => 1,
	)
);
$latest_works = new WP_Query(
	array(
		'post_type'      => 'works',
		'posts_per_page' => 3,
	)
);
?>
<main>
<section class="home-hero">
	<img src="<?php echo esc_url( $office_image ); ?>" alt="窓辺の明るいオフィスで相談する<?php bloginfo( 'name' ); ?>のスタッフ">
	<div class="hero-copy">
		<h1>想いをかたちに、<br>事業の力に。</h1>
		<p>Webサイトの制作から運用まで、<br>地域の企業に寄り添います。</p>
		<a class="hero-button" href="<?php echo esc_url( get_post_type_archive_link( 'works' ) ); ?>">制作実績を見る　→</a>
	</div>
</section>

<div id="news" class="news-strip">
	<div class="container news-inner">
		<span class="news-label">お知らせ</span>
		<?php if ( $latest_news ) : ?>
			<span class="news-date"><?php echo esc_html( get_the_date( 'Y.m.d', $latest_news[0] ) ); ?></span>
			<a class="news-text" href="<?php echo esc_url( get_permalink( $latest_news[0] ) ); ?>"><?php echo esc_html( get_the_title( $latest_news[0] ) ); ?></a>
		<?php else : ?>
			<span class="news-text">現在お知らせはありません。</span>
		<?php endif; ?>
	</div>
</div>

<section class="section">
	<div class="container">
		<div class="section-heading"><h2>制作実績</h2><span class="eyebrow">WORKS</span><div class="heading-mark"></div></div>
		<?php if ( $latest_works->have_posts() ) : ?>
			<div class="works-grid">
				<?php
				while ( $latest_works->have_posts() ) :
					$latest_works->the_post();
					get_template_part( 'template-parts/work-card', null, array( 'heading' => 'h3' ) );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php else : ?>
			<p class="works-empty">制作実績はまだありません。</p>
		<?php endif; ?>
		<p class="center-link"><a class="text-link" href="<?php echo esc_url( get_post_type_archive_link( 'works' ) ); ?>">制作実績をすべて見る　→</a></p>
	</div>
</section>

<section id="services" class="section services-section">
	<div class="container">
		<div class="section-heading"><h2>事業内容</h2><span class="eyebrow">SERVICE</span><div class="heading-mark"></div></div>
		<div class="service-grid">
			<article class="service-item"><h3>Webサイト制作</h3><p>企業の想いを丁寧にヒアリングし、伝わるデザインでWebサイトを制作します。</p></article>
			<article class="service-item"><h3>WordPress構築</h3><p>お知らせや実績を更新しやすい、長く使えるWordPressサイトを構築します。</p></article>
			<article class="service-item"><h3>運用サポート</h3><p>公開後の更新や改善もサポートし、継続的な情報発信をお手伝いします。</p></article>
		</div>
	</div>
</section>

<section class="section">
	<div class="container about-preview">
		<div>
			<span class="eyebrow">ABOUT</span>
			<h2>地域の企業と、ともに歩む。</h2>
			<p><?php bloginfo( 'name' ); ?>は、Webの力で地域の企業の想いをかたちにし、ビジネスの成長をサポートする制作会社です。丁寧な対話を大切に、企画から制作・運用まで一貫して伴走します。</p>
			<a class="text-link" href="<?php echo esc_url( hinata_page_url( 'company' ) ); ?>">会社案内を見る　→</a>
		</div>
		<img src="<?php echo esc_url( $office_image ); ?>" alt="打ち合わせをする<?php bloginfo( 'name' ); ?>のスタッフ">
	</div>
</section>

<?php get_template_part( 'template-parts/contact-band', null, array( 'text' => 'Web制作に関するご相談・お見積もりなど、まずはお気軽にお問い合わせください。' ) ); ?>
</main>
<?php
get_footer();
