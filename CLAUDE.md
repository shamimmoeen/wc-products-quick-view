# WCQV – Product Quick View for WooCommerce

## Project

- **Plugin name:** WCQV – Product Quick View for WooCommerce
- **Plugin slug:** `wc-products-quick-view`
- **Main file:** `wc-products-quick-view.php`
- **Text domain:** `wc-products-quick-view`
- **Constant/function prefix:** `WCQV_` / `wcqv_`
- **Author:** Mainul Hassan
- **Author URI:** https://mainulhassan.com
- **wp.org contributor:** shamimmoeen

## WordPress / WooCommerce standards

- Follow [WordPress Coding Standards (WPCS)](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- Escape all output: `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`
- Sanitize all input: `sanitize_text_field()`, `absint()`, etc.
- Verify nonces on all AJAX handlers
- Use `wp_send_json_success()` / `wp_send_json_error()` — not `echo json_encode()` + `die()`
- Use `wp_die()` — not bare `die()`
- Use `WC()` — not `global $woocommerce`
- Use `wc_get_template()` for template loading — not a custom loader; theme overrides go in `yourtheme/wc-products-quick-view/`

## Commit conventions

- Single-sentence commit messages; add a body only when context is genuinely needed
- Imperative mood, explain *why* or *what problem* was fixed — not just what changed
- One commit per logical change — do not batch unrelated work
