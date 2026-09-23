<?php
/**
 * index.php — the one file every WordPress theme MUST have. It's the
 * final fallback template used any time a more specific file (like
 * front-page.php) doesn't exist for the page being requested.
 * Keeping this simple and generic is normal — it's a safety net,
 * not something visitors will see often once we've built out
 * specific templates (single product, category archive, etc.).
 */
get_header();
?>

<div class="section-wrap">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article <?php post_class(); ?>>
				<h1><?php the_title(); ?></h1>
				<div><?php the_content(); ?></div>
			</article>
		<?php endwhile; ?>
	<?php else : ?>
		<p>Nothing found.</p>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
