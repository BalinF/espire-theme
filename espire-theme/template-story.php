<?php
/**
 * Template Name: Brand Story
 *
 * template-story.php — shared layout for the brand story pages (see
 * inc/story.php for the content and editable fields). Australian Made and
 * F*ck Fast Fashion use it automatically via page-australian-made.php and
 * page-sustainability.php; any other page can pick it under
 * Page Attributes > Template.
 */

get_header();

while ( have_posts() ) :
	the_post();
	$espire_s = espire_story_content( get_post() );
	?>

	<section class="story-hero">
		<?php if ( $espire_s['hero_image'] ) : ?>
			<img src="<?php echo esc_url( espire_story_image_url( $espire_s['hero_image'] ) ); ?>" alt="">
		<?php endif; ?>
		<div class="overlay"></div>
		<div class="content">
			<?php if ( $espire_s['hero_label'] ) : ?>
				<span class="story-label"><?php echo esc_html( $espire_s['hero_label'] ); ?></span>
			<?php endif; ?>
			<h1><?php echo esc_html( $espire_s['hero_title'] ); ?></h1>
			<?php if ( $espire_s['hero_intro'] ) : ?>
				<p><?php echo esc_html( $espire_s['hero_intro'] ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<?php if ( $espire_s['pillars'] ) : ?>
		<section class="story-pillars">
			<?php if ( $espire_s['pillars_heading'] ) : ?>
				<h2 class="story-eyebrow"><?php echo esc_html( $espire_s['pillars_heading'] ); ?></h2>
			<?php endif; ?>
			<div class="pillar-grid">
				<?php foreach ( $espire_s['pillars'] as $espire_p ) : ?>
					<?php list( $espire_img, $espire_title, $espire_text, $espire_label, $espire_url ) = $espire_p; ?>
					<article class="pillar">
						<?php if ( $espire_img ) : ?>
							<div class="p-shot"><img src="<?php echo esc_url( espire_story_image_url( $espire_img ) ); ?>" alt="" loading="lazy"></div>
						<?php endif; ?>
						<div class="p-body">
							<h3><?php echo esc_html( $espire_title ); ?></h3>
							<p><?php echo esc_html( $espire_text ); ?></p>
							<?php if ( $espire_url ) : ?>
								<a class="p-link" href="<?php echo esc_url( espire_story_link( $espire_url ) ); ?>"><?php echo esc_html( $espire_label ?: 'Learn More' ); ?> &rarr;</a>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $espire_s['stat_number'] || $espire_s['stat_lead'] ) : ?>
		<section class="stat-strip">
			<span class="stat-num"><?php echo esc_html( $espire_s['stat_number'] ); ?></span>
			<p class="stat-text"><b><?php echo esc_html( $espire_s['stat_lead'] ); ?></b> <?php echo esc_html( $espire_s['stat_text'] ); ?></p>
		</section>
	<?php endif; ?>

	<?php if ( $espire_s['feature_title'] ) : ?>
		<section class="story-feature">
			<div class="sf-shot<?php echo $espire_s['feature_image'] ? '' : ' is-empty'; ?>">
				<?php if ( $espire_s['feature_image'] ) : ?>
					<img src="<?php echo esc_url( espire_story_image_url( $espire_s['feature_image'] ) ); ?>" alt="" loading="lazy">
				<?php endif; ?>
			</div>
			<div class="sf-text">
				<h2><?php echo esc_html( $espire_s['feature_title'] ); ?></h2>
				<p><?php echo esc_html( $espire_s['feature_text'] ); ?></p>
				<?php if ( $espire_s['feature_button'][1] ) : ?>
					<a class="btn olive" href="<?php echo esc_url( espire_story_link( $espire_s['feature_button'][1] ) ); ?>"><?php echo esc_html( $espire_s['feature_button'][0] ); ?> &rarr;</a>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $espire_s['definition_term'] ) : ?>
		<p class="story-definition"><b><?php echo esc_html( $espire_s['definition_term'] ); ?>:</b> <?php echo esc_html( $espire_s['definition_text'] ); ?></p>
	<?php endif; ?>

	<?php if ( $espire_s['closing_title'] ) : ?>
		<section class="story-closing is-<?php echo esc_attr( 'feature' === $espire_s['closing_style'] ? 'feature' : 'band' ); ?>">
			<?php if ( $espire_s['closing_image'] ) : ?>
				<img src="<?php echo esc_url( espire_story_image_url( $espire_s['closing_image'] ) ); ?>" alt="" loading="lazy">
			<?php endif; ?>
			<div class="overlay"></div>
			<div class="c-text">
				<?php if ( $espire_s['closing_label'] ) : ?>
					<span class="story-label"><?php echo esc_html( $espire_s['closing_label'] ); ?></span>
				<?php endif; ?>
				<h2><?php echo esc_html( $espire_s['closing_title'] ); ?></h2>
				<?php if ( $espire_s['closing_text'] ) : ?>
					<p><?php echo esc_html( $espire_s['closing_text'] ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( $espire_s['closing_button'][1] ) : ?>
				<a class="btn" href="<?php echo esc_url( espire_story_link( $espire_s['closing_button'][1] ) ); ?>"><?php echo esc_html( $espire_s['closing_button'][0] ); ?> &rarr;</a>
			<?php endif; ?>
		</section>
	<?php endif; ?>

	<?php
endwhile;

get_footer();
