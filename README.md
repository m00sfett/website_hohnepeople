# website_hohnepeople
HohnePeople Website

## Manual security checks

* Generate a sitemap locally and confirm canonical URLs stay on the configured base (set `site.base_url` in `config.php`).
* Send requests with manipulated Host headers (e.g. `curl -H "Host: attacker.example.com" http://localhost:8000`) and verify the rendered canonical/OG links still point to the configured base or a safe localhost fallback.
