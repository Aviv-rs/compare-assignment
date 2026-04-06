<?php

class ProductQueryResult {

    /** @var Product[] */
    public $products;

    public $total_items;
    public $current_page;
    public $total_pages;

    public function __construct( array $products, int $total_items, int $current_page, int $per_page ) {
        $this->products     = $products;
        $this->total_items  = $total_items;
        $this->current_page = $current_page;
        $this->total_pages  = (int) ceil( $total_items / max( 1, $per_page ) );
    }

    public function has_products(): bool {
        return ! empty( $this->products );
    }

    public function has_next_page(): bool {
        return $this->current_page < $this->total_pages;
    }

    public function has_previous_page(): bool {
        return $this->current_page > 1;
    }
}
