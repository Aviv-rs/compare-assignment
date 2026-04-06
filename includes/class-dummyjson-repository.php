<?php

class DummyJsonProductRepository implements ProductRepositoryInterface {

    private const BASE_URL    = 'https://dummyjson.com/products';
    private const SEARCH_URL  = 'https://dummyjson.com/products/search';
    private const CACHE_TTL   = HOUR_IN_SECONDS;
    private const TIMEOUT     = 5; // seconds

    // Only fetch the fields we actually display.
    private const SELECT_FIELDS = 'title,description,price,rating,stock,brand,category,thumbnail,images';

    public function fetch_products( ProductQuery $query ): ProductQueryResult {
        $cache_key = $this->build_cache_key( $query );
        $cached    = get_transient( $cache_key );

        if ( false !== $cached ) {
            return $cached;
        }

        $url      = $this->build_api_url( $query );
        $response = wp_remote_get( $url, array( 'timeout' => self::TIMEOUT ) );

        if ( is_wp_error( $response ) ) {
            return new ProductQueryResult( array(), 0, $query->page, $query->per_page );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( ! is_array( $body ) || ! isset( $body['products'] ) ) {
            return new ProductQueryResult( array(), 0, $query->page, $query->per_page );
        }

        $products = array_map( array( 'Product', 'from_array' ), $body['products'] );
        $total    = isset( $body['total'] ) ? (int) $body['total'] : 0;

        $result = new ProductQueryResult( $products, $total, $query->page, $query->per_page );
        set_transient( $cache_key, $result, self::CACHE_TTL );

        return $result;
    }

    private function build_api_url( ProductQuery $query ): string {
        $base_url = empty( $query->search_term ) ? self::BASE_URL : self::SEARCH_URL;

        $params = array(
            'limit'  => $query->per_page,
            'skip'   => $query->get_offset(),
            'select' => self::SELECT_FIELDS,
        );

        if ( ! empty( $query->search_term ) ) {
            $params['q'] = $query->search_term;
        }

        return $base_url . '?' . http_build_query( $params );
    }

    private function build_cache_key( ProductQuery $query ): string {
        $search_hash = md5( $query->search_term ?: 'all' );
        return "ca_products_{$search_hash}_p{$query->page}";
    }
}
