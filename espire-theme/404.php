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
	<span class="notfound-script">well, that thread's come loose</span>
	<h1>We Couldn't Find That Page</h1>
	<p>The page you're looking for may have been moved, renamed, or doesn't exist. Try a search, or head back to one of the pages below.</p>

	<?php // Searches products (WooCommerce's product search results page). ?>
	<form class="notfound-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<label class="screen-reader-text" for="notfound-s">Search products</label>
		<input type="search" id="notfound-s" name="s" placeholder="Search products…" value="<?php echo esc_attr( get_search_query() ); ?>">
		<input type="hidden" name="post_type" value="product">
		<button type="submit" class="btn olive">Search</button>
	</form>

	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn olive notfound-home">Back To Homepage</a>

	<p class="notfound-links-label">Or Try One Of These</p>
	<div class="notfound-links">
		<a href="<?php echo esc_url( home_url( '/store/' ) ); ?>">Shop Store</a>
		<a href="<?php echo esc_url( home_url( '/product-category/shirts/' ) ); ?>">Shirts</a>
		<a href="<?php echo esc_url( home_url( '/product-category/hoodies/' ) ); ?>">Hoodies</a>
		<a href="<?php echo esc_url( home_url( '/diy/' ) ); ?>">Design Your Own</a>
		<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact Us</a>
	</div>
</div>

<?php get_footer(); ?>
