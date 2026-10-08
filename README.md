# Espire Clothing — WordPress theme

The custom theme for Espire Clothing lives in [`espire-theme/`](espire-theme/).
Edit the files here; GitHub uploads them to the **staging** site automatically.

- Staging (where this project is built): https://staging.espireclothing.com.au
- Live (untouched until the project is finished and copied over): https://espireclothing.com.au

## How changes reach staging

| Where you push | What happens |
| --- | --- |
| any branch | PHP syntax check only — nothing is uploaded |
| `main` | syntax check, then `espire-theme/` is uploaded to staging over FTPS |

Flow: work on a branch → pull request → merge into `main` → staging updates.
Only changed files are uploaded (the deploy keeps a small
`.ftp-deploy-sync-state.json` file in the theme folder on the server to track this —
leave it there). A deploy can be re-run by hand from the **Actions** tab
("Check & deploy theme to staging" → *Run workflow*).

Until the FTP secrets below exist, the deploy step is skipped with a warning.

## One-time setup (VentraIP)

1. **Create an FTP account** in VentraIP's cPanel → *FTP Accounts*, limited to the
   **staging** site's theme folder, e.g.
   `staging.espireclothing.com.au/wp-content/themes/espire-theme`
   (check the staging site's document root in cPanel → *Domains*).
2. **Add four repository secrets** in GitHub → this repo → *Settings* →
   *Secrets and variables* → *Actions* → *New repository secret*:

   | Secret | Example |
   | --- | --- |
   | `FTP_SERVER` | `ftp.espireclothing.com.au` (or the server hostname from VentraIP's welcome email) |
   | `FTP_USERNAME` | `deploy@espireclothing.com.au` |
   | `FTP_PASSWORD` | the FTP account's password |
   | `FTP_THEME_DIR` | the theme folder **as that FTP account sees it**, ending in `/` — just `/` if the account is limited to the theme folder |

   Secrets are encrypted by GitHub and never appear in the code or logs.
3. Make sure the theme folder on staging is named `espire-theme` and is the active
   theme (*Appearance → Themes*).

## Plugins the theme expects

WooCommerce, Advanced Custom Fields — the free version is enough (all the theme's
fields are defined in code, so nothing needs setting up under ACF → Field Groups,
and their structure is version-controlled here). Keep it active on live too.

Lists you fill in row by row — Seed To Store *Steps*, *Available Fits* and their
*Size Notes*, FAQ *Quick Answers*, Brand Story *Pillar Cards* — show as a fixed set
of numbered boxes (Step 1–6, Fit 1–4…) rather than ACF Pro's add-a-row buttons;
fill in as many as you need and leave the rest empty (`inc/acf-rows.php`).

Seed To Store step icons: a symbol tag with an uploaded icon shows that image as
it is (no ring), so upload the full circular badge. Tags without one get the
plain ringed leaf mark.

## Filling in content (wp-admin)

The templates fall back gracefully when these are empty, so fill them in as you go.

**Products → Categories → edit a category** (sub-categories inherit from their parent)
- *Category Emblem* — the collection badge shown beside the product name.
- *Available Fits* — each fit's name and one-liner, plus the **Fit Guide** details
  (photo, intro, model note, size notes, measurements). The product page's
  "Fit Guide" button only appears once a fit has guide content.
  Measurements are typed one row per line with `|` between cells:
  ```
  | XS | S | M | L | XL
  Chest (B) | 109 | 114 | 119 | 124 | 129
  ```

**Products → Tags → edit a tag → Symbol** — symbols *are* product tags
- A product shows a badge for every symbol tag it carries; each badge opens
  that symbol's own slide-in panel. Tag products as you already do.
- *Show as symbol*: **Automatic** (the default) turns it on for the built-in
  symbols (`australian-made`, `respired`, `made-in-store`, `good-earth-cotton`,
  `belgian-linen` slugs); set **Yes** for any other tag, **No** to hide one.
- *Icon, Badge Ring Text, Badge Order, Panel Tagline / Heading / Intro /
  Points, Story button, Shop button* — anything left blank uses the built-in
  copy. The Shop button links to the tag's own page (all products with it).

**Products → edit a product → Product short description**: the text in the
product page's "About The [Product]" box. If it's empty, the main description is used.

**Category emblem beside the product name**: the product's category *Category Emblem*
(a sub-category uses its parent's). If that's blank, the category's WooCommerce
*Thumbnail* is used.

**Products → edit a product → Product Page Extras**
- *Raw Materials / Fabric / Stitched / Care* — the fact lines under the description.
- **Linked Products → Cross-sells** (in WooCommerce's Product data box) — the
  "Goes Well With" row. Falls back to related products if left empty.

**Products → Attributes → (e.g. Colour) → Configure terms → edit a term → Swatch**
- *Swatch Colour* or *Swatch Image* — that option then shows as a coloured
  square instead of a text button.

**Pages → FAQ → Quick Answers**
- The questions in the product page's "Shipping & Returns" panel.

**Banner photos** — every collection banner uses an image you pick:
- Category pages: Products → Categories → edit → *Banner Image* (or the
  category's normal WooCommerce *Thumbnail*; sub-categories use their parent's).
  With neither set, the homepage photo for that collection is used. Add a
  *Category Emblem* on the same screen to show it centred on the banner.
- Store / DIY / Shop page: that page's **Featured image** (right-hand sidebar
  when editing the page). The line under the title is the page's *Excerpt*.

**Filter bar**: Fit / Size / Colour options across the top of every category
page, the Store hub and the shop. No plugin needed. Options come from the products'
attributes, either kind:
- global attributes (Products → Attributes) named like "fit", "size" or
  "colour"/"color"; several can share a group (e.g. "Size" and "Kids Size")
- attributes typed straight into a product (Product data → Attributes → custom
  attribute) with those names

A category page only offers the values its own products use. Click an option to
switch it on and again to switch it off; several can be combined. If there's no Fit
attribute, a category's sub-categories are the Fit buttons. Every category gets the
green **Fit Guide** button: it opens the full Fit Guide when the category (or its
parent) has fit details, otherwise the Sidebar Intro / Fits / Sourcing text, or a
"contact us" note.

**Swatch colours**, first found:
1. Products → Attributes → Colour → edit a colour → *Swatch*
2. whatever the old GetWooPlugins swatches plugin (or another swatches plugin) saved
3. a colour guessed from the name ("Dyed Navy" → navy)

Set a Swatch Colour to fix any colour that's guessed wrong.

**Icon attributes** (e.g. Fit, Sleeve): give an attribute's values an image in
Products → Attributes → (attribute) → Configure terms → edit → *Swatch Image* (images
the old swatches plugin saved also count). Those values then show as small icons
instead of text, on product cards and in the filter bar.

**Product cards** show colour dots (or "4 Colours") and a "Sizes S–XL · Classic
fit" line from the same attributes, so a variation-swatches plugin isn't needed.

**Menus (Appearance → Menus)**: two menus you can edit; tick its *Display location*
at the bottom of the menu screen:
- **Primary Header Menu**: the links across the top, and in the phone menu. Open an item
  to pick its **Menu Icon** (pencil, shop front, globe, leaf, T-shirt …), choose *No
  icon*, or upload your own. Left on *Automatic*, an icon is matched from the link.
- **Category Bar**: the dark strip of collection links under the banners. Add
  categories from *Product categories* on the left and drag to reorder.

Until a menu is assigned, the built-in links show.

**Homepage → From Seed To Store** (Products → Categories → edit → *Seed To
Store Journey*) — upload a garment sketch and add its steps in order, one product
tag per step (drag to reorder; optional custom label). Symbol tags show their own
mark and link to their symbol page. The homepage shows a random category that has
steps on each visit; with none set up, the original Tees journey shows.

**Product pages → From Seed To Store**: built from the product's own tags. It uses the
category's journey steps that the product is tagged with, in that order, then any other
symbol tags the product carries. Symbol steps open that symbol's slide-in panel. The
sketch is the product's *Journey Sketch* (edit a product, right-hand sidebar) or, if
that's blank, its category's sketch.

**Homepage → Shop The Set**: Pages → the page chosen in Settings → Reading → *Your
homepage displays → A static page*, or a page with the slug `home`. Then use the *Shop
The Set* box — pick
2–4 products, a photo and a title; photos, prices and the total fill in. *Shop This
Set* steps through the pieces one at a time: it opens the first product with a
"Piece 1 of 3" bar, and *Add & Next Piece* adds it and moves on, ending at the cart.
Leave the box's *"Shop This Set" Link* empty to keep that; fill it in only to send the
button somewhere else.
*Set Saving ($)* (default $15) comes off in the cart as a "Shop The Set saving" line
once every piece of the current set is in it, in any size or colour (twice for two
full sets). The homepage shows the was/now total. 0 turns it off.

**Homepage → hero (video + tagline)**: same page, *Homepage Hero* box — background
video (upload an MP4), a still from it to show while it loads, the tagline and both
buttons' text and links. Blank fields keep the current wording. Best video: a 10–20
second muted loop, 1080p or 720p, 3–5 MB. *Phone Video (optional)*: a lighter cut that
phones (up to 767px wide) get instead — about 10 seconds, portrait 540×960, under 2 MB.

**Free shipping threshold** ($300): the theme's wording lives in
`ESPIRE_FREE_SHIPPING_OVER` (`inc/product-page.php`); the actual free shipping is
WooCommerce → Settings → Shipping → each zone → *Free shipping* → *Minimum order
amount*. Change both together.

**Store / Shop (`/store/`)**: lists every product, with the filter bar. `/store/`
opens WooCommerce's Shop page. To make `/store/` the shop itself, set WooCommerce →
Settings → Products → *Shop page* to "Store". The banner photo and line come from the
Shop page's featured image and excerpt, or the Store page's if those are blank.

**Australian Made / F\*ck Fast Fashion (Pages → edit → Brand Story box)** — hero,
pillar cards (each linking to its longer post), stat strip, shop feature,
definition line and closing banner, in tabs. Blank fields use the starting copy.
Any other page can use this layout via *Page Attributes → Template → Brand Story*.

**Symbol pages (`/product-tag/<symbol>/`)** — built from the same tag *Symbol* box:
add a *Symbol Page Banner*, optional *Journey Diagram* and *Certification Line*.
Products carrying the tag are listed automatically.

**Design Your Own**: the DIY *product category* is the DIY page. Every "Design Your
Own" link goes to /diy/, which opens that category's page: same banner, filters and
cards as other categories, plus a "Start Designing" button on each card. Set it up
under Pages → DIY → *Design Your Own* box:
- **Base Garment Category**: which category is DIY. If left blank, a category with
  the slug `diy` is used.
- **Design Guide**: the text for the green *Design Guide* button.

The banner photo and text come from the category's own Banner fields. If those are
blank, the DIY page's featured image and excerpt are used. With no DIY category,
/diy/ shows the old DIY page.

**Pages → Contact** — the page body holds your contact form (e.g. its shortcode);
the *Contact Details* box sets the heading, intro, hours, phone, email and
address. Hours and phone also feed the footer.

## Pages to check on staging

- **Cart / Checkout** page bodies should be the classic `[woocommerce_cart]` and
  `[woocommerce_checkout]` shortcodes (not the Cart/Checkout blocks or old
  Elementor content) for the theme's design to apply.
- Pages the theme styles by slug: `store`, `diy`, `contact`, `australian-made`,
  `sustainability`, `faq`. A different slug means that page falls back to the
  plain page layout.

## Going live checklist (copying staging → espireclothing.com.au)

Staging has two temporary workarounds that must **not** carry over to live:

1. **Re-enable QUIC.cloud Hotlink Protection.** my.quic.cloud → espireclothing.com.au
   → CDN → Security → *Hotlink Protection* → **ON**. It's switched off only so
   staging can show live's images.
2. **Remove the staging image rule from `.htaccess`.** Delete the 7-line
   `# Staging only: load missing images from the live site` block (above
   `# BEGIN WordPress`) from the live copy of `.htaccess`. On live it would point
   at itself.
3. **Re-pick images uploaded only on staging.** Anything uploaded on staging for
   testing isn't on live. Upload the final images on live and choose them again in
   each place they're used: category *Banner Image* / *Category Emblem*, page
   *Featured image*s (Store, DIY, Shop), symbol tag icons and banners, Seed To
   Store sketches, Shop The Set photo, Homepage Hero videos and still, attribute
   *Swatch* images.

## Installing the theme by hand (no FTP needed)

Every push builds an installable zip:

1. GitHub → this repo → **Actions** tab → click the latest **"Check & deploy
   theme to staging"** run (green tick) for the branch you want.
2. Scroll to **Artifacts** → click **espire-theme** to download `espire-theme.zip`.
3. WordPress (staging) → **Appearance → Themes → Add New → Upload Theme** → choose
   the zip → **Install Now**. If the theme is already installed, WordPress asks to
   **Replace current with uploaded** — choose that.
4. **Activate** it (first time only).

Why not GitHub's green *Code → Download ZIP* button? That zips the whole repo
(README, workflow and all) with the theme one folder down, so WordPress can't
find `style.css` and rejects it. The Actions artifact is just the theme folder.
