<?php
/**
 * page-contact.php — Contact (/contact/). Site Map "Contact" artboard:
 * heading, intro and store details on the left; the contact form on the
 * right.
 *
 * The form is whatever is in the Contact page's body in wp-admin (e.g. a
 * form plugin's shortcode), so the existing form — and its Cloudflare
 * Turnstile spam protection — keeps working untouched. The left-hand text
 * and details are the "Contact Details" box (inc/pages.php).
 */

get_header();

$espire_details = espire_store_details();
$espire_field   = function ( $name, $default = '' ) {
	$value = function_exists( 'get_field' ) ? get_field( $name ) : '';
	return $value ? $value : $default;
};
$espire_address = $espire_field( 'contact_address' );

while ( have_posts() ) :
	the_post();
	?>
	<div class="contact-wrap">
		<div class="contact-left">
			<span class="kicker"><?php the_title(); ?></span>
			<h1><?php echo esc_html( $espire_field( 'contact_heading', "We'd Love To Hear From You" ) ); ?></h1>
			<p class="lead"><?php echo esc_html( $espire_field( 'contact_lead', 'Get in touch — questions about an order, a custom fit, or the DIY builder, we read everything ourselves.' ) ); ?></p>
			<ul class="contact-details">
				<li>
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
					<span><?php echo esc_html( $espire_details['hours'] ); ?></span>
				</li>
				<li>
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.362 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
					<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $espire_details['phone'] ) ); ?>"><b><?php echo esc_html( $espire_details['phone'] ); ?></b></a>
				</li>
				<?php if ( $espire_details['email'] ) : ?>
					<li>
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="1"/><path d="M3 7l9 6 9-6"/></svg>
						<a href="mailto:<?php echo esc_attr( antispambot( $espire_details['email'] ) ); ?>"><?php echo esc_html( antispambot( $espire_details['email'] ) ); ?></a>
					</li>
				<?php endif; ?>
				<?php if ( $espire_address ) : ?>
					<li>
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 21s-7-6.2-7-11a7 7 0 0114 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
						<a href="<?php echo esc_url( 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $espire_address ) ); ?>" target="_blank" rel="noopener"><?php echo nl2br( esc_html( $espire_address ) ); ?></a>
					</li>
				<?php endif; ?>
			</ul>
		</div>

		<div class="contact-right">
			<span class="kicker">Get In Touch</span>
			<h2>Send Us A Message</h2>
			<div class="contact-form">
				<?php
				if ( trim( get_the_content() ) ) {
					the_content();
				} else {
					echo '<p class="contact-fallback">Give us a call on <a href="tel:' . esc_attr( preg_replace( '/[^0-9+]/', '', $espire_details['phone'] ) ) . '">' . esc_html( $espire_details['phone'] ) . '</a> or drop into the store.</p>';
				}
				?>
			</div>
		</div>
	</div>
	<?php
endwhile;

get_footer();
