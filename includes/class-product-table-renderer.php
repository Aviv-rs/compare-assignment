<?php

class ProductTableRenderer {

    /**
     * Render the full product table UI: search bar, table, and pagination.
     * Returns HTML string — no side effects.
     */
    public function render( ProductQueryResult $result, ProductQuery $query ): string {
        $html  = '<div class="ca-product-table-wrap">';
        $html .= $this->render_search_bar( $query );

        if ( ! $result->has_products() ) {
            $html .= $this->render_empty_state( $query );
        } else {
            $html .= $this->render_table( $result );
            $html .= $this->render_pagination( $result, $query );
        }

        $html .= '</div>';

        return $html;
    }

    private function render_search_bar( ProductQuery $query ): string {
        $current_search = esc_attr( $query->search_term );
        $permalink      = get_permalink();

        // Strip query string from the action URL — we'll re-add needed params as hidden fields.
        $action_url   = esc_url( strtok( $permalink, '?' ) );
        $hidden_fields = $this->build_hidden_fields_from_permalink( $permalink );

        return <<<HTML
        <form class="ca-search-form" method="get" action="{$action_url}">
            {$hidden_fields}
            <input
                type="text"
                name="ca_search"
                value="{$current_search}"
                placeholder="Search products..."
                class="ca-search-input"
            />
            <button type="submit" class="ca-search-button">Search</button>
        </form>
HTML;
    }

    /**
     * With plain permalinks (?page_id=6), method="get" forms discard the
     * action URL's query string. We preserve those params as hidden inputs.
     */
    private function build_hidden_fields_from_permalink( string $permalink ): string {
        $query_string = wp_parse_url( $permalink, PHP_URL_QUERY );
        if ( ! $query_string ) {
            return '';
        }

        $fields = '';
        parse_str( $query_string, $params );
        foreach ( $params as $key => $value ) {
            $fields .= '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
        }

        return $fields;
    }

    private function render_empty_state( ProductQuery $query ): string {
        if ( ! empty( $query->search_term ) ) {
            $escaped_term = esc_html( $query->search_term );
            return "<p class=\"ca-empty-state\">No products found for \"{$escaped_term}\".</p>";
        }

        return '<p class="ca-empty-state">No products available.</p>';
    }

    private function render_table( ProductQueryResult $result ): string {
        $html = '<table class="ca-product-table">';
        $html .= $this->render_table_header();
        $html .= '<tbody>';

        foreach ( $result->products as $product ) {
            $html .= $this->render_product_row( $product );
        }

        $html .= '</tbody></table>';

        return $html;
    }

    private function render_table_header(): string {
        return <<<HTML
        <thead>
            <tr>
                <th>Thumbnail</th>
                <th>Title</th>
                <th>Description</th>
                <th>Price</th>
                <th>Rating</th>
                <th>Stock</th>
                <th>Brand</th>
                <th>Category</th>
                <th>Gallery</th>
            </tr>
        </thead>
HTML;
    }

    private function render_product_row( Product $product ): string {
        $title       = esc_html( $product->title );
        $description = esc_html( $product->description );
        $price       = esc_html( number_format( $product->price, 2 ) );
        $rating      = esc_html( $product->rating );
        $stock       = esc_html( $product->stock );
        $brand       = esc_html( $product->brand );
        $category    = esc_html( $product->category );
        $thumbnail   = esc_url( $product->thumbnail );

        // Embed images as a data attribute so gallery.js can read them without AJAX.
        $images_json = esc_attr( wp_json_encode( $product->images ) );

        return <<<HTML
        <tr class="ca-product-row" data-images="{$images_json}">
            <td><img src="{$thumbnail}" alt="{$title}" class="ca-thumbnail" /></td>
            <td>{$title}</td>
            <td>{$description}</td>
            <td>\${$price}</td>
            <td>{$rating}</td>
            <td>{$stock}</td>
            <td>{$brand}</td>
            <td>{$category}</td>
            <td><button type="button" class="ca-gallery-button">Gallery</button></td>
        </tr>
HTML;
    }

    private function render_pagination( ProductQueryResult $result, ProductQuery $query ): string {
        if ( $result->total_pages <= 1 ) {
            return '';
        }

        $html     = '<nav class="ca-pagination">';
        $base_url = get_permalink();

        for ( $page = 1; $page <= $result->total_pages; $page++ ) {
            $url_params = array( 'paged' => $page );

            if ( ! empty( $query->search_term ) ) {
                $url_params['ca_search'] = $query->search_term;
            }

            $url          = esc_url( add_query_arg( $url_params, $base_url ) );
            $active_class = ( $page === $result->current_page ) ? ' ca-pagination-active' : '';

            $html .= "<a href=\"{$url}\" class=\"ca-pagination-link{$active_class}\">{$page}</a>";
        }

        $html .= '</nav>';

        return $html;
    }
}
