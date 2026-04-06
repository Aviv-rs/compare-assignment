<?php

class ProductQuery {

    public $search_term;
    public $page;
    public $per_page;

    public function __construct( string $search_term = '', int $page = 1, int $per_page = 10 ) {
        $this->search_term = $search_term;
        $this->page        = max( 1, $page );
        $this->per_page    = max( 1, $per_page );
    }

    public static function from_request( int $per_page = 10 ): self {
        // Use a namespaced parameter to avoid conflicting with WordPress's native ?s= search.
        $search_term = isset( $_GET['ca_search'] ) ? sanitize_text_field( wp_unslash( $_GET['ca_search'] ) ) : '';
        $page        = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;

        return new self( $search_term, $page, $per_page );
    }

    public function get_offset(): int {
        return ( $this->page - 1 ) * $this->per_page;
    }
}
