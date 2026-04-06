<?php

/**
 * Abstraction for fetching products. Swap implementations to change the data
 * source (e.g. DummyJSON API today, custom database tomorrow) without touching
 * any rendering or plugin logic.
 */
interface ProductRepositoryInterface {

    public function fetch_products( ProductQuery $query ): ProductQueryResult;
}
