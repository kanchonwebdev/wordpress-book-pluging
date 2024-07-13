<?php
class Book_Manager_Settings
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
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function add_settings_page()
    {
        add_options_page(
            'Book Manager Settings',
            'Book Manager',
            'manage_options',
            'book-manager-settings',
            array($this, 'render_settings_page')
        );
    }

    public function render_settings_page()
    {
?>
        <div class="wrap">
            <h1>Book Manager Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('book_manager_settings');
                do_settings_sections('book-manager-settings');
                submit_button();
                ?>
            </form>
        </div>
    <?php
    }

    public function register_settings()
    {
        register_setting('book_manager_settings', 'bm_enable_pagination');
        register_setting('book_manager_settings', 'bm_books_per_page');
        register_setting('book_manager_settings', 'bm_enable_search');
        register_setting('book_manager_settings', 'bm_default_sort');
        register_setting('book_manager_settings', 'bm_sort_text');

        add_settings_section('bm_general_settings', 'General Settings', null, 'book-manager-settings');

        add_settings_field('bm_enable_pagination', 'Enable Pagination', array($this, 'render_enable_pagination_field'), 'book-manager-settings', 'bm_general_settings');
        add_settings_field('bm_books_per_page', 'Books Per Page', array($this, 'render_books_per_page_field'), 'book-manager-settings', 'bm_general_settings');
        add_settings_field('bm_enable_search', 'Enable Search', array($this, 'render_enable_search_field'), 'book-manager-settings', 'bm_general_settings');
        add_settings_field('bm_default_sort', 'Default Sort By', array($this, 'render_default_sort_field'), 'book-manager-settings', 'bm_general_settings');
        add_settings_field('bm_sort_text', 'Order By/Sort By Text', array($this, 'render_sort_text_field'), 'book-manager-settings', 'bm_general_settings');
    }

    public function render_enable_pagination_field()
    {
        $value = get_option('bm_enable_pagination', 'yes');
    ?>
        <input type="checkbox" name="bm_enable_pagination" value="yes" <?php checked($value, 'yes'); ?> />
    <?php
    }

    public function render_books_per_page_field()
    {
        $value = get_option('bm_books_per_page', 10);
    ?>
        <input type="number" name="bm_books_per_page" value="<?php echo esc_attr($value); ?>" />
    <?php
    }

    public function render_enable_search_field()
    {
        $value = get_option('bm_enable_search', 'yes');
    ?>
        <input type="checkbox" name="bm_enable_search" value="yes" <?php checked($value, 'yes'); ?> />
    <?php
    }

    public function render_default_sort_field()
    {
        $value = get_option('bm_default_sort', 'date');
    ?>
        <select name="bm_default_sort">
            <option value="date" <?php selected($value, 'date'); ?>>Date</option>
            <option value="price" <?php selected($value, 'price'); ?>>Price</option>
        </select>
    <?php
    }

    public function render_sort_text_field()
    {
        $value = get_option('bm_sort_text', 'Order By/Sort By');
    ?>
        <input type="text" name="bm_sort_text" value="<?php echo esc_attr($value); ?>" />
<?php
    }
}
?>