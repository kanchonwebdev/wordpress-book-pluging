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
        <div id="book-archive">
            <form id="book-filter-form" class="container">
                <div class="row">
                    <div class="col-md-6">
                        <label for="book_sort" class="form-label"><?php echo esc_html(get_option('bm_sort_text', 'Order By/Sort By')); ?></label>
                    </div>
                    <div class="col-md-6">
                        <select id="book_sort" class="form-control" name="book_sort">
                            <option value="date">Latest</option>
                            <option value="price">Price</option>
                        </select>
                    </div>
                </div>
            </form>
            <div class="card">
                <div class="card-header">
                    Featured
                </div>
                <div class="card-body">
                    <h5 class="card-title">Special title treatment</h5>
                    <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
                    <a href="#" class="btn btn-primary">Go somewhere</a>
                </div>
            </div>
            <div id="book-list">
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

    private function display_books($sort = 'date')
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
        ?>
            <ul>
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <li>
                        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        <p><?php the_excerpt(); ?></p>
                    </li>
                <?php endwhile; ?>
            </ul>
<?php
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
    Book_Manager_Frontend::get_instance()->display_books($sort);
    wp_die();
}
?>