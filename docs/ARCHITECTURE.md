# Architecture

## Overview

Compare Assignment is a WordPress plugin that displays a searchable, paginated product table using data from the DummyJSON API. The architecture prioritizes clean separation of concerns, making each component independently understandable and swappable.

## Design Decisions

### 1. WordPress Plugin from the Start

Rather than building a standalone PHP app and then retrofitting it as a plugin, the project is built as a WordPress plugin from day one. This avoids the common pitfall of a messy "bolt-on" integration and lets us leverage WordPress APIs (HTTP client, transients, shortcodes) naturally.

### 2. Repository Pattern for Data Access

The `ProductRepositoryInterface` defines a single method: `fetch_products(ProductQuery): ProductQueryResult`. The `DummyJsonProductRepository` implements this interface today, but any data source (database, different API) could be swapped in by implementing the same interface.

This decision was made to:

- Keep the rendering layer completely unaware of where data comes from.
- Make the pagination parameters backend-controlled — the controller decides `limit`, `skip`, and `per_page`, not the frontend.
- Allow future extensibility (e.g., a database-backed repository) without refactoring.

### 3. Server-Side Rendering with URL State

All data fetching, searching, and pagination happen on the server. The current search query and page number are stored in URL query parameters (`?ca_search=query&paged=2`). We use `ca_search` instead of `s` to avoid conflicting with WordPress's built-in search routing. This means:

- Every state is bookmarkable and shareable.
- Search engines can crawl paginated results.
- No JavaScript is required for core functionality.

### 4. JavaScript Only for Gallery

The only client-side JavaScript is the Gallery feature (Part 2 requirement). Product image URLs are embedded as `data-images` attributes in each table row, so the JS can toggle gallery rows without any AJAX calls.

### 5. Transient Caching

API responses are cached using WordPress Transients, keyed by an MD5 hash of the search term and the page number. The hash ensures cache keys stay within WordPress's 172-character limit regardless of search term length. Cache TTL is 1 hour. This:

- Avoids redundant API calls for repeated queries.
- Respects DummyJSON rate limits.
- Is automatically cleaned up by WordPress.

### 6. API Field Selection

The DummyJSON API supports a `select` parameter to limit which fields are returned. We only request the 9 fields we display (`title,description,price,rating,stock,brand,category,thumbnail,images`), reducing response payload significantly.

## File Structure

```
compare-assignment.php              → Plugin bootstrap: hooks, activation/deactivation
uninstall.php                       → Clean removal of plugin data

includes/
  class-plugin.php                  → Orchestrator: registers shortcode, enqueues assets
  class-product.php                 → Product value object
  class-product-query.php           → Query parameters (search, page, per_page)
  class-product-query-result.php    → Query result (products, pagination metadata)
  interface-product-repository.php  → Data access contract
  class-dummyjson-repository.php    → DummyJSON API implementation
  class-product-table-renderer.php  → HTML rendering (table, search, pagination)

assets/
  css/products.css                  → Styles
  js/gallery.js                     → Gallery toggle logic
```

## Data Flow

```
HTTP Request → WordPress → Shortcode Handler
  → ProductQuery::from_request()        (sanitize GET params)
  → DummyJsonRepository::fetch_products()
    → Check transient cache
    → On miss: wp_remote_get() → parse JSON → build Product objects → cache
  → ProductTableRenderer::render()       (pure HTML output)
  → Gallery.js                           (client-side toggle)
```

## Error Handling

- **API failure**: `wp_remote_get` errors return an empty `ProductQueryResult`, and the renderer shows "No products available."
- **Empty search results**: The renderer shows "No products found for [query]."
- **Malformed API data**: `Product::from_array()` uses defensive defaults for every field, so missing or unexpected data never causes breakage.
- **XSS prevention**: All server-side output is escaped with `esc_html()` / `esc_attr()` / `esc_url()`. Client-side gallery rendering uses `document.createElement` / `setAttribute` instead of `innerHTML` to prevent injection from untrusted image URLs.
