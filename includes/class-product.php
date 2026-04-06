<?php

class Product {

    public $title;
    public $description;
    public $price;
    public $rating;
    public $stock;
    public $brand;
    public $category;
    public $thumbnail;

    /** @var string[] */
    public $images;

    /**
     * Safe factory: always returns a renderable Product even if API data is incomplete.
     */
    public static function from_array( array $data ): self {
        $product = new self();

        $product->title       = isset( $data['title'] )       ? (string) $data['title']       : '';
        $product->description = isset( $data['description'] ) ? (string) $data['description'] : '';
        $product->price       = isset( $data['price'] )       ? (float)  $data['price']       : 0.0;
        $product->rating      = isset( $data['rating'] )      ? (float)  $data['rating']      : 0.0;
        $product->stock       = isset( $data['stock'] )       ? (int)    $data['stock']       : 0;
        $product->brand       = isset( $data['brand'] )       ? (string) $data['brand']       : '';
        $product->category    = isset( $data['category'] )    ? (string) $data['category']    : '';
        $product->thumbnail   = isset( $data['thumbnail'] )   ? (string) $data['thumbnail']   : '';
        $product->images      = isset( $data['images'] ) && is_array( $data['images'] )
            ? array_map( 'strval', $data['images'] )
            : array();

        return $product;
    }
}
