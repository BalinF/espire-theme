<?php
/**
 * footer.php — runs at the bottom of every page (called via get_footer()).
 * Content here is pulled from the real live site so nothing's lost in
 * the rebuild — links point at the real URLs already confirmed in the
 * site-inventory doc.
 */
?>
	<footer class="site-footer">

		<div class="footer-cols">
			<div class="footer-col">
				<h4>Shop</h4>
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/product-category/tees/' ) ); ?>">The Tee Bar</a></li>
					<li><a href="<?php echo esc_url( home_url( '/product-category/leg-hoodies/' ) ); ?>">Leg Hoodies &amp; Shorts</a></li>
					<li><a href="<?php echo esc_url( home_url( '/product-category/the-bad-batch/' ) ); ?>">The Bad Batch</a></li>
					<li><a href="<?php echo esc_url( home_url( '/product-category/nannas-threads/' ) ); ?>">Nanna's Threads</a></li>
					<li><a href="<?php echo esc_url( home_url( '/product-category/kids/' ) ); ?>">Kids</a></li>
					<li><a href="<?php echo esc_url( home_url( '/product-category/shirts/' ) ); ?>">Shirts</a></li>
					<li><a href="<?php echo esc_url( home_url( '/store/' ) ); ?>">Shop All</a></li>
				</ul>
			</div>

			<div class="footer-col">
				<h4>Support</h4>
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>">Returns &amp; Exchanges</a></li>
					<li><a href="<?php echo esc_url( home_url( '/store/' ) ); ?>">Size Guides</a></li>
					<li><a href="<?php echo esc_url( home_url( '/privacypolicy/' ) ); ?>">Privacy &amp; Cookies</a></li>
					<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a></li>
					<li><a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">My Account</a></li>
				</ul>
			</div>

			<div class="footer-col">
				<h4>Our Story</h4>
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/australian-made/' ) ); ?>">The Aussie Story</a></li>
					<li><a href="<?php echo esc_url( home_url( '/sustainability/' ) ); ?>">F*ck Fast Fashion</a></li>
					<li><a href="<?php echo esc_url( home_url( '/made-in-store/' ) ); ?>">Made In Store</a></li>
				</ul>
			</div>

			<div class="footer-col">
				<h4>Get In Touch</h4>
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact Us</a></li>
					<li>Store open daily 9am&ndash;5pm</li>
					<li>0490 124 074</li>
				</ul>
			</div>
		</div>

		<!-- Condensed: social icons and the newsletter form share one row
		     (was two full-width stacked rows) — see .footer-bottom-row in
		     style.css. -->
		<div class="footer-bottom-row">
			<div class="footer-social">
				<a href="#" aria-label="Facebook">FB</a>
				<a href="#" aria-label="Instagram">IG</a>
				<a href="#" aria-label="TikTok">TT</a>
				<a href="#" aria-label="YouTube">YT</a>
			</div>
			<!-- TODO: swap the FB/IG/TT/YT text placeholders above for real
			     icon SVGs and the real profile URLs once we have them. -->

			<div class="footer-newsletter">
				<span>Join the clothing (r)evolution and follow our story</span>
				<!-- The real signup runs through MailPoet (already installed —
				     see Plugins list). This is a plain placeholder form; the
				     real MailPoet shortcode/widget gets dropped in here once
				     we confirm which MailPoet list it should post to. -->
				<input type="email" placeholder="Your email">
				<button type="button" class="btn olive">Send</button>
			</div>
		</div>

		<!-- Condensed: acknowledgement + copyright share one row instead
		     of two stacked full-width blocks. -->
		<div class="footer-ack-row">
			<p class="footer-ack">Espire Clothing acknowledges and pays respect to the past, present and future Traditional Custodians and Elders of this nation and the continuation of cultural, spiritual and educational practices of Aboriginal and Torres Strait Islander peoples.</p>
			<div class="footer-bottom">
				&copy; Espire Clothing, <?php echo esc_html( date( 'Y' ) ); ?>
			</div>
		</div>

	</footer>

<?php wp_footer(); // Required — same idea as wp_head(), lets plugins add scripts before </body>. ?>
</body>
</html>
