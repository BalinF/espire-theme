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

WooCommerce, Advanced Custom Fields (category banner/sidebar fields are defined
in `functions.php`, so their structure is version-controlled here).

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

**Products → edit a product → Product Page Extras**
- *Raw Materials / Fabric / Stitched / Care* — the fact lines under the description.
- **Linked Products → Cross-sells** (in WooCommerce's Product data box) — the
  "Goes Well With" row. Falls back to related products if left empty.

**Products → Attributes → (e.g. Colour) → Configure terms → edit a term → Swatch**
- *Swatch Colour* or *Swatch Image* — that option then shows as a coloured
  square instead of a text button.

**Pages → FAQ → Quick Answers**
- The questions in the product page's "Shipping & Returns" panel.

**Store hub (`/store/`)** — one tile per top-level product category, in the
order you drag them into under Products → Categories. Tile photo: the
category's *Banner Image*, else its WooCommerce thumbnail, else the homepage
photo. Tile text: the category *Description*, else the homepage copy. The intro
line under the banner is the Store page's *Excerpt*; anything typed into the
page body shows above the tiles. The Design Your Own tile uses the DIY page's
featured image.

**Australian Made / F\*ck Fast Fashion (Pages → edit → Brand Story box)** — hero,
pillar cards (each linking to its longer post), stat strip, shop feature,
definition line and closing banner, in tabs. Blank fields use the starting copy.
Any other page can use this layout via *Page Attributes → Template → Brand Story*.

**Symbol pages (`/product-tag/<symbol>/`)** — built from the same tag *Symbol* box:
add a *Symbol Page Banner*, optional *Journey Diagram* and *Certification Line*.
Products carrying the tag are listed automatically.

**Pages → DIY → Design Your Own** — pick the *Base Garment Category* (its
sub-categories become the Base Fit buttons) and write the *Design Guide*. Banner
photo = the page's featured image; banner line = its excerpt.

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
