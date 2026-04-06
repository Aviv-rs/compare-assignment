# Compare Assignment

A WordPress plugin that displays a searchable, paginated product table using data from the [DummyJSON Products API](https://dummyjson.com/docs/products).

## Prerequisites

- [Docker](https://www.docker.com/get-started) and Docker Compose

## Quick Start

```bash
cd docker
docker-compose up -d
```

Wait about 30 seconds for the initial setup (WordPress installation + plugin activation), then visit:

- **Product Table**: [http://localhost:8080/compare-assignment/](http://localhost:8080/compare-assignment/)
- **WordPress Admin**: [http://localhost:8080/wp-admin/](http://localhost:8080/wp-admin/) (login: `admin` / `admin`)

To stop the environment:

```bash
docker-compose down
```

To stop and remove all data (clean slate):

```bash
docker-compose down -v
```

## Features

- **Product Table** — Displays products with title, description, price, rating, stock, brand, category, and thumbnail.
- **Search** — Server-side search using the DummyJSON search API. Results update via form submission (no JavaScript required).
- **Pagination** — Server-side pagination with 10 items per page. Current page and search term are stored in URL query parameters (`?ca_search=query&paged=2`), making every state bookmarkable.
- **Gallery** — Click the "Gallery" button on any row to reveal up to 3 product images inline. Click again to collapse. Multiple galleries can be open simultaneously.
- **Caching** — API responses are cached using WordPress Transients (1 hour TTL) to avoid redundant requests.
- **Auto-Page Creation** — The plugin automatically creates a "Compare Assignment" page on activation and removes it on deactivation.

## Manual Installation (without Docker)

1. Copy the `compare-assignment` folder into your WordPress `wp-content/plugins/` directory.
2. Activate the plugin from the WordPress admin panel (Plugins → Activate).
3. Visit the auto-created "Compare Assignment" page, or add the `[compare_assignment]` shortcode to any page.

## Architecture

See [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) for detailed architecture decisions, data flow, and design rationale.

## Assumptions

- The DummyJSON API is available and responsive. If the API is down, the plugin displays a clean empty state rather than an error.
- The `select` API parameter is used to fetch only the fields we display, reducing payload size.
- Transient caching (1 hour TTL) is used to minimize API calls. In a production environment, the TTL could be adjusted.
- The plugin uses `wp_remote_get` with a 5-second timeout to prevent the page from hanging if the API is slow.
