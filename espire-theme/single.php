<?php
/**
 * single.php — a story article (blog post), laid out like the live story
 * posts: hero photo with title + intro → black statement band → story
 * sections alternating image/text → olive callout → "Read more" cards.
 * Content comes from the post's "Article Layout" box (inc/articles.php); a
 * post without it shows its normal content under the hero.
 */

get_header();

while ( have_posts() ) :
	the_post();
	$espire_id       = get_the_ID();
	$espire_hero     = espire_article_field( 'article_hero_image' ) ?: get_the_post_thumbnail_url( $espire_id, 'full' );
	$espire_intro    = espire_article_field( 'article_intro' ) ?: ( has_excerpt() ? get_the_excerpt() : '' );
	$espire_sections = (array) espire_article_field( 'article_sections' );
	$espire_layout   = espire_article_has_layout( $espire_id );

	if ( ! $espire_layout ) :
		// Not moved to the Article Layout yet: show the post as it was built.
		?>
		<article <?php post_class( 'article article-legacy' ); ?>>
			<div class="article-content"><?php the_content(); ?></div>
		</article>
		<?php
		continue;
	endif;
	?>

	<article <?php post_class( 'article' ); ?>>
		<header class="article-hero<?php echo $espire_hero ? '' : ' no-photo'; ?>">
			<?php if ( $espire_hero ) : ?>
				<img src="<?php echo esc_url( $espire_hero ); ?>" alt="">
			<?php endif; ?>
			<div class="overlay"></div>
			<div class="ah-text">
				<h1><?php the_title(); ?></h1>
				<?php if ( $espire_intro ) : ?>
					<p><?php echo esc_html( $espire_intro ); ?></p>
				<?php endif; ?>
			</div>
		</header>

		<?php if ( espire_article_field( 'article_statement' ) ) : ?>
			<p class="article-statement"><?php echo espire_article_highlight( espire_article_field( 'article_statement' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside ?></p>
		<?php endif; ?>

		<?php if ( $espire_sections ) : ?>
			<div class="article-sections">
				<?php foreach ( $espire_sections as $espire_i => $espire_sec ) : ?>
					<?php
					$espire_kind = ! empty( $espire_sec['layout'] ) ? $espire_sec['layout'] : 'image-left';
					if ( empty( $espire_sec['image'] ) ) {
						$espire_kind = 'text';
					}
					?>
					<section class="article-section is-<?php echo esc_attr( $espire_kind ); ?>">
						<?php if ( 'text' !== $espire_kind ) : ?>
							<div class="as-media"><img src="<?php echo esc_url( $espire_sec['image'] ); ?>" alt="<?php echo esc_attr( $espire_sec['heading'] ?? '' ); ?>" loading="lazy"></div>
						<?php endif; ?>
						<div class="as-text">
							<?php if ( ! empty( $espire_sec['heading'] ) ) : ?>
								<h2><?php echo esc_html( $espire_sec['heading'] ); ?></h2>
							<?php endif; ?>
							<?php if ( ! empty( $espire_sec['text'] ) ) : ?>
								<div class="as-body"><?php echo wp_kses_post( $espire_sec['text'] ); ?></div>
							<?php endif; ?>
						</div>
					</section>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( espire_article_field( 'article_callout_title' ) ) : ?>
			<section class="article-callout">
				<?php if ( espire_article_field( 'article_callout_image' ) ) : ?>
					<img src="<?php echo esc_url( espire_article_field( 'article_callout_image' ) ); ?>" alt="" loading="lazy">
				<?php endif; ?>
				<h2><?php echo espire_article_highlight( espire_article_field( 'article_callout_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside ?></h2>
				<?php if ( espire_article_field( 'article_callout_text' ) ) : ?>
					<p><?php echo esc_html( espire_article_field( 'article_callout_text' ) ); ?></p>
				<?php endif; ?>
				<?php if ( espire_article_field( 'article_callout_button_link' ) ) : ?>
					<a class="btn" href="<?php echo esc_url( espire_story_link( espire_article_field( 'article_callout_button_link' ) ) ); ?>"><?php echo esc_html( espire_article_field( 'article_callout_button_label' ) ?: 'Find Out More' ); ?> &rarr;</a>
				<?php endif; ?>
			</section>
		<?php endif; ?>

		<?php $espire_related = array_filter( espire_article_related( $espire_id ) ); ?>
		<?php if ( $espire_related ) : ?>
			<section class="article-related">
				<h2 class="story-eyebrow">Read More</h2>
				<div class="pillar-grid">
					<?php foreach ( $espire_related as $espire_r ) : ?>
						<?php
						$espire_r_icon  = espire_article_field( 'article_icon', $espire_r->ID );
						$espire_r_photo = $espire_r_icon ? '' : get_the_post_thumbnail_url( $espire_r->ID, 'medium_large' );
						$espire_r_text  = espire_article_field( 'article_intro', $espire_r->ID ) ?: get_the_excerpt( $espire_r );
						?>
						<article class="pillar<?php echo $espire_r_icon ? ' has-icon' : ''; ?>">
							<?php if ( $espire_r_icon ) : ?>
								<div class="p-icon"><img src="<?php echo esc_url( $espire_r_icon ); ?>" alt="" loading="lazy"></div>
							<?php elseif ( $espire_r_photo ) : ?>
								<div class="p-shot"><img src="<?php echo esc_url( $espire_r_photo ); ?>" alt="" loading="lazy"></div>
							<?php endif; ?>
							<div class="p-body">
								<h3><?php echo esc_html( get_the_title( $espire_r ) ); ?></h3>
								<?php if ( $espire_r_text ) : ?>
									<p><?php echo esc_html( wp_trim_words( $espire_r_text, 32 ) ); ?></p>
								<?php endif; ?>
								<a class="p-link" href="<?php echo esc_url( get_permalink( $espire_r ) ); ?>">Read The Story &rarr;</a>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>
	</article>

	<?php
endwhile;

get_footer();
