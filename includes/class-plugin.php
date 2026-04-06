<?php

class CompareAssignmentPlugin {

    private const SHORTCODE    = 'compare_assignment';
    private const ITEMS_PER_PAGE = 10;

    private $repository;
    private $renderer;

    public function __construct( ProductRepositoryInterface $repository ) {
        $this->repository = $repository;
        $this->renderer   = new ProductTableRenderer();
    }

    public function register(): void {
        add_shortcode( self::SHORTCODE, array( $this, 'render_shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    public function render_shortcode(): string {
        $query  = ProductQuery::from_request( self::ITEMS_PER_PAGE );
        $result = $this->repository->fetch_products( $query );

        return $this->renderer->render( $result, $query );
    }

    public function enqueue_assets(): void {
        if ( ! $this->is_plugin_page() ) {
            return;
        }

        wp_enqueue_style(
            'compare-assignment-styles',
            plugin_dir_url( CA_PLUGIN_FILE ) . 'assets/css/products.css',
            array(),
            CA_PLUGIN_VERSION
        );

        wp_enqueue_script(
            'compare-assignment-gallery',
            plugin_dir_url( CA_PLUGIN_FILE ) . 'assets/js/gallery.js',
            array(),
            CA_PLUGIN_VERSION,
            true // Load in footer.
        );
    }

    /**
     * Only load assets on the page that contains our shortcode.
     */
    private function is_plugin_page(): bool {
        global $post;
        return is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, self::SHORTCODE );
    }
}
