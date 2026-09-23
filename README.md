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
