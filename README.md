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
