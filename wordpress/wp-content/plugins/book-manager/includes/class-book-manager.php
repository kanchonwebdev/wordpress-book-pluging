<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

<?php
class Book_Manager
{
    private static $instance = null;

    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('init', array($this, 'register_book_post_type'));
        add_action('add_meta_boxes', array($this, 'add_book_meta_boxes'));
        add_action('save_post', array($this, 'save_book_meta_boxes'));
    }

    public function register_book_post_type()
    {
        $labels = array(
            'name' => 'Books',
            'singular_name' => 'Book',
            'menu_name' => 'Books',
            'name_admin_bar' => 'Book',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Book',
            'new_item' => 'New Book',
            'edit_item' => 'Edit Book',
            'view_item' => 'View Book',
            'all_items' => 'All Books',
            'search_items' => 'Search Books',
            'parent_item_colon' => 'Parent Books:',
            'not_found' => 'No books found.',
            'not_found_in_trash' => 'No books found in Trash.'
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'book'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'comments'),
            'taxonomies' => array('category')
        );

        register_post_type('book', $args);
    }

    public function add_book_meta_boxes()
    {
        add_meta_box('book_details', 'Book Details', array($this, 'render_book_meta_boxes'), 'book', 'normal', 'default');
    }

    public function render_book_meta_boxes($post)
    {
        // Add nonce for security and authentication
        wp_nonce_field('book_nonce_action', 'book_nonce_name');

        $author = get_post_meta($post->ID, '_book_author', true);
        $price = get_post_meta($post->ID, '_book_price', true);
        $genre = get_post_meta($post->ID, '_book_genre', true);
        $published_date = get_post_meta($post->ID, '_book_published_date', true);

        echo '<label for="book_author" class="form-label">Author Name</label>';
        echo '<input type="text" id="book_author" class="form-control" name="book_author mb-2" value="' . esc_attr($author) . '" />';

        echo '<label for="book_price" class="form-label">Price</label>';
        echo '<input type="text" id="book_price" name="book_price" class="form-control mb-2" value="' . esc_attr($price) . '" />';

        echo '<label for="book_genre" class="form-label">Genre</label>';
        echo '<input type="text" id="book_genre" name="book_genre" class="form-control mb-2" value="' . esc_attr($genre) . '" />';

        echo '<label for="book_published_date" class="form-label">Published Date</label>';
        echo '<input type="date" id="book_published_date" name="book_published_date" class="form-control mb-2" value="' . esc_attr($published_date) . '" />';
    }

    public function save_book_meta_boxes($post_id)
    {
        // Check if nonce is set
        if (!isset($_POST['book_nonce_name'])) {
            return;
        }

        // Verify the nonce
        if (!wp_verify_nonce($_POST['book_nonce_name'], 'book_nonce_action')) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Sanitize and save data
        if (isset($_POST['book_author'])) {
            update_post_meta($post_id, '_book_author', sanitize_text_field($_POST['book_author']));
        }
        if (isset($_POST['book_price'])) {
            update_post_meta($post_id, '_book_price', sanitize_text_field($_POST['book_price']));
        }
        if (isset($_POST['book_genre'])) {
            update_post_meta($post_id, '_book_genre', sanitize_text_field($_POST['book_genre']));
        }
        if (isset($_POST['book_published_date'])) {
            update_post_meta($post_id, '_book_published_date', sanitize_text_field($_POST['book_published_date']));
        }
    }
}
