<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

<?php
class Book_Manager_Frontend
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
        add_action('init', array($this, 'create_book_archive_page'));
        add_shortcode('book_manager_archive', array($this, 'render_book_archive'));
    }

    public function create_book_archive_page()
    {
        $page = get_page_by_path('book-archive');
        if (!$page) {
            $page_data = array(
                'post_title' => 'Book Archive',
                'post_content' => '[book_manager_archive]',
                'post_status' => 'publish',
                'post_type' => 'page'
            );
            wp_insert_post($page_data);
        }
    }

    public function render_book_archive($atts)
    {
        ob_start();
?>
        <div id="book-archive" class="container">
            <form id="book-filter-form" class="row mb-4">
                <div class="col-md-6">
                    <label for="book_sort" class="form-label"><?php echo esc_html(get_option('bm_sort_text', 'Order By/Sort By')); ?></label>
                </div>
                <div class="col-md-6">
                    <select id="book_sort" class="form-control" name="book_sort">
                        <option value="date">Latest</option>
                        <option value="price">Price</option>
                    </select>
                </div>
            </form>
            <div id="book-list" class="row">
                <?php $this->display_books(); ?>
            </div>
        </div>
        <script>
            jQuery(document).ready(function($) {
                $('#book_sort').change(function() {
                    var sort = $(this).val();
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: {
                            action: 'filter_books',
                            sort: sort
                        },
                        success: function(response) {
                            $('#book-list').html(response);
                        }
                    });
                });
            });
        </script>
        <?php
        return ob_get_clean();
    }

    public function display_books($sort = 'date')
    {
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $args = array(
            'post_type' => 'book',
            'posts_per_page' => get_option('bm_books_per_page', 10),
            'paged' => $paged,
            'orderby' => $sort,
            'order' => 'DESC'
        );

        $query = new WP_Query($args);
        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post();
                $author = get_post_meta(get_the_ID(), '_book_author', true);
                $price = get_post_meta(get_the_ID(), '_book_price', true);
                $genre = get_post_meta(get_the_ID(), '_book_genre', true);
                $published_date = get_post_meta(get_the_ID(), '_book_published_date', true);
        ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h3><?php the_title(); ?></h3>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php the_title(); ?></h5>
                        <p class="card-text"><?php the_excerpt(); ?></p>
                        <p class="card-text"><strong>Author:</strong> <?php echo esc_html($author); ?></p>
                        <p class="card-text"><strong>Price:</strong> $<?php echo esc_html($price); ?></p>
                        <p class="card-text"><strong>Genre:</strong> <?php echo esc_html($genre); ?></p>
                        <p class="card-text"><strong>Published Date:</strong> <?php echo esc_html($published_date); ?></p>
                        <a href="<?php the_permalink(); ?>" class="btn btn-primary">View more</a>
                    </div>
                </div>
<?php endwhile;
            the_posts_pagination();
        else :
            echo '<p>No books found</p>';
        endif;

        wp_reset_postdata();
    }
}

add_action('wp_ajax_filter_books', 'filter_books_callback');
add_action('wp_ajax_nopriv_filter_books', 'filter_books_callback');

function filter_books_callback()
{
    $sort = isset($_POST['sort']) ? sanitize_text_field($_POST['sort']) : 'date';
    ob_start();
    Book_Manager_Frontend::get_instance()->display_books($sort);
    $response = ob_get_clean();
    echo $response;
    wp_die();
}
?>