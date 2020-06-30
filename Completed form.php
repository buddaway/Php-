<?php

class ProductSearch {

  private static $initiated = FALSE;

  public static function init() {
    if (!self::$initiated) {
      self::init_hooks();
    }
  }

  /**
   * Initializes WordPress hooks
   */
  private static function init_hooks() {
    self::$initiated = TRUE;

    /**
     * Register css file.
     */
    add_action('wp_enqueue_scripts', array('ProductSearch', 'load_css'));

    /**
     * Register a new short code: [product-search]
     */
    add_shortcode('product-search', array('ProductSearch', 'shortcode'));
  }

  /**
   * The callback function that will load css.
   */
  public static function load_css() {
    wp_register_style('basic_search.css', PRODUCT_SEARCH__PLUGIN_DIR_URL . 'css/basic_search.css');
    wp_enqueue_style('basic_search.css');
  }

  /**
   * The callback function that will replace [book]
   *
   * @return string
   */
  public static function shortcode() {
    ob_start();
    self::searchProducts();
    return ob_get_clean();
  }

  /**
   * Helper function to display product search form and results.
   */
  public static function searchProducts() {
    if (isset($_POST['submit'])) {
      // validate search form.
      self::formValidation($_POST['keyword']);

      // sanitize user form input
      global $keyword;

      // sanitize basic search form fields.
      $keyword = sanitize_text_field($_POST['keyword']);

      // submit basic search form.
      self::formSubmit($keyword);
    }

    $keyword = isset($keyword) ? $keyword : '';

    // display basic search form.
    self::displayForm($keyword);

    // display basic search results.
    self::displayResults($keyword);
  }

  /**
   * Helper function to display basic search form.
   *
   * @param $keyword
   */
  public static function displayForm($keyword) {
    echo '<form role="search" class="search-form" action="' . $_SERVER['REQUEST_URI'] . '" method="post">
	<label for="search-form-2">
		<span class="screen-reader-text">Search for:</span>
		<input type="search" id="search-form-2" class="search-field" placeholder="Search Products…" value="' . (isset($_POST['keyword']) ? $keyword : NULL) . '" name="keyword">
	</label>
	<input type="submit" name="submit" class="search-submit" value="Search">
</form>';
  }

  /**
   * Helper function to display basic search form validation.
   *
   * @param $keyword
   */
  public static function formValidation($keyword) {
    global $reg_errors;
    $reg_errors = new WP_Error;

    if (empty($keyword)) {
      $reg_errors->add('field', 'Required form field is missing');
    }

    if (is_wp_error($reg_errors)) {
      foreach ($reg_errors->get_error_messages() as $error) {
        echo '<div class="error-message">';
        echo $error . '<br/>';
        echo '</div>';
      }
    }
  }

  /**
   * Helper function to display basic search form submit hanlder.
   */
  public static function formSubmit($keyword) {

  }

  /**
   * Helper function to display basic search results.
   *
   * @param $keyword
   */
  public static function displayResults($keyword) {
    $keyword = isset($_POST['keyword']) ? $keyword : NULL;
    if (!empty($keyword)) {
      global $wpdb;
      // Please try now.
      $products = $wpdb->get_results(
        $wpdb->prepare(
          "SELECT * FROM `{$wpdb->base_prefix}products` WHERE Product LIKE %s;", '%' . $wpdb->esc_like($keyword) . '%'
        )
      );

      echo "<table id='product-search-results'>";

      echo '<thead>';
      echo "<tr>";
      echo "<th>" . __('Product') . "</th>";
      echo "<th>" . __('Price') . "</th>";
      echo "<th>" . __('Link') . "</th>";
      echo "<th>" . __('Store') . "</th>";
      echo "</tr>";
      echo '</thead>';

      echo '<tbody>';
      if (!empty($products)) {
        foreach ($products as $product) {
          echo "<tr>";
          echo "<td>" . $product->Product . "</td>";
          echo "<td>" . $product->Price . "</td>";
          echo "<td>" . $product->Link . "</td>";
          echo "<td>" . $product->Store . "</td>";
          echo "</tr>";
        }
      }
      else {
        echo "<tr>";
        echo "<td colspan='4'>" . __('No product found matching your search criteria.') . "</td>";
        echo "</tr>";
      }
      echo '</tbody>';

      echo "</table>";
    }
  }

}