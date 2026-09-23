# Espire Clothing — WordPress theme

The custom theme for espireclothing.com.au lives in [`espire-theme/`](espire-theme/).
Edit the files here; GitHub uploads them to the site automatically.

## How changes reach the site

> **Status:** the deploy workflow (`.github/workflows/deploy.yml`) is not added yet —
> it's waiting on sign-off. Until then, upload the theme to the site by hand.

| Where you push | What happens |
| --- | --- |
| any branch | PHP syntax check only — the live site is not touched |
| `main` | syntax check, then `espire-theme/` is uploaded to VentraIP over FTPS |

So the flow is: work on a branch → open a pull request → merge into `main` → live.
Only changed files are uploaded (the deploy keeps a small
`.ftp-deploy-sync-state.json` file in the theme folder on the server to track this —
leave it there). A deploy can also be re-run by hand from the **Actions** tab
("Check & deploy theme" → *Run workflow*).

## One-time setup (VentraIP)

1. **Create an FTP account** in VentraIP's cPanel → *FTP Accounts*. Limit its
   directory to the theme folder if you like, e.g.
   `public_html/wp-content/themes/espire-theme`.
2. **Add four repository secrets** in GitHub → this repo → *Settings* →
   *Secrets and variables* → *Actions* → *New repository secret*:

   | Secret | Example |
   | --- | --- |
   | `FTP_SERVER` | `ftp.espireclothing.com.au` (or the server hostname from VentraIP's welcome email) |
   | `FTP_USERNAME` | `deploy@espireclothing.com.au` |
   | `FTP_PASSWORD` | the FTP account's password |
   | `FTP_THEME_DIR` | path to the theme folder **as that FTP account sees it**, ending in `/` — e.g. `/public_html/wp-content/themes/espire-theme/`, or just `/` if the account is limited to the theme folder |

   Secrets are encrypted by GitHub and never appear in the code or logs.
3. Make sure the theme folder name on the server matches (`espire-theme`), and
   activate it in *Appearance → Themes* if it isn't already.

## Plugins the theme expects

WooCommerce, Advanced Custom Fields (category banner/sidebar fields are defined
in `functions.php`, so their structure is version-controlled here).
