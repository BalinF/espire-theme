<?php
/**
 * 404.php — WordPress automatically uses this whenever a URL doesn't
 * match anything real on the site. Returning a real 404 status (which
 * WordPress does automatically for this template) rather than quietly
 * redirecting to the homepage matters for SEO — it tells search
 * engines "this page is genuinely gone," so they drop it from their
 * index instead of getting confused.
 */
get_header();
?>

<div class="notfound-wrap">
	<div class="code">404</div>
	<h1>Page Not Found</h1>
	<p>The page you're looking for doesn't exist, or may have moved.</p>

	<form class="notfound-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<input type="search" name="s" placeholder="Search the site…" value="<?php echo esc_attr( get_search_query() ); ?>">
		<button type="submit" class="btn olive">Search</button>
	</form>

	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn olive" style="margin-bottom:26px;display:inline-block;">Back To Homepage</a>

	<div class="notfound-links">
		<a href="<?php echo esc_url( home_url( '/store/' ) ); ?>">Shop Store</a>
		<a href="<?php echo esc_url( home_url( '/product-category/shirts/' ) ); ?>">Shirts</a>
		<a href="<?php echo esc_url( home_url( '/product-category/hoodies/' ) ); ?>">Hoodies</a>
		<a href="<?php echo esc_url( home_url( '/diy/' ) ); ?>">Design Your Own</a>
		<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact Us</a>
	</div>
</div>

<?php get_footer(); ?>
