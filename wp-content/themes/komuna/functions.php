<?php

require_once 'custom-elementor.php';

/**
 * Register Theme custom Scripts and Styles
 */
function add_theme_scripts()
{
	wp_enqueue_style('custom', get_stylesheet_directory_uri() . '/assets/custom.css', array(), '1.0', 'all');
	wp_enqueue_style('pages', get_stylesheet_directory_uri() . '/assets/pages.css', array(), '1.0', 'all');
	wp_enqueue_style('home', get_stylesheet_directory_uri() . '/assets/home.css', array(), '1.0', 'all');

	wp_enqueue_script('custom', get_stylesheet_directory_uri() . '/assets/custom.js', array('jquery'), 1.0, true);
}

add_action('wp_enqueue_scripts', 'add_theme_scripts', 11);

function get_item_filter($atts)
{
	ob_start();

	get_template_part('template-parts/items-filter', 'items-filter', [$atts]);

	return ob_get_clean();
}
add_shortcode('item_filter', 'get_item_filter');


// Render Custom Templates inside loops
function render_template($template)
{
	ob_start();

	// Include template 
	include($template);

	$ret = ob_get_contents();

	ob_end_clean();

	return $ret;
}

function items_filter_ajax()
{
	$tax = isset($_POST['tax']) ? $_POST['tax'] : false;
	$template_id = isset($_POST['template_id']) ? $_POST['template_id'] : false;
	$page = isset($_POST['page']) ? $_POST['page'] : false;
	$pt = isset($_POST['pt']) ? $_POST['pt'] : 'post';
	$type = isset($_POST['type']) ? $_POST['type'] : 'page';
	$per_page = isset($_POST['per_page']) ? $_POST['per_page'] : 12;
	$form_data = isset($_POST['form_data']) ? $_POST['form_data'] : null;
	$taxonomy = isset($_POST['taxonomy']) ? $_POST['taxonomy'] : null;
	$prefiltered_tax = isset($_POST['prefilter']) ? $_POST['prefilter'] : null;
	$date_from = isset($_POST['date_from']) ? $_POST['date_from'] : null;
	$date_to = isset($_POST['date_to']) ? $_POST['date_to'] : null;
	$date_field = isset($_POST['date_field']) ? $_POST['date_field'] : null;
	$custom_template = isset($_POST['custom_tpl']) ? $_POST['custom_tpl'] : null;
	
	$search_text = '';
	$date_year = '';
	$data_doc = '';
	if ($form_data) {
		// $form_data = $form_data[0];
		$search_text = $form_data['search_text'];
		$date_year = $form_data['date_year'];
	}

	$meta_key = ($type === 'page') 
    ? 'date' 
    : (($type === 'news') 
        ? 'data_e_lajmit' 
        : (($type === 'events') 
            ? 'data_fillimit' 
            : (($type === 'public-calls') 
                ? 'data_fillimit' 
                : ''
              )
          )
      );
	

	$ajaxposts = [
		'post_type' => $pt,
		'posts_per_page' => $per_page,
		'meta_key' => $meta_key,
		'paged' => $page,
		's'     => $search_text,
		'orderby' => 'meta_value',		
		'post_status'    => 'publish',
		'order' => 'DESC',		
	];

	$terms_year = array(
		'post_type'         => $pt,
		'posts_per_page'    => -1
	);

	if ($type === 'news') {
    	$ajaxposts['meta_key'] = 'data_e_lajmit';
	} elseif (($type === 'events' || $type === 'public-calls')) {
    	$meta_query = array('relation' => 'AND');
		if ($date_from && $date_to) {
			$meta_query[] = array(
				'relation' => 'AND',
				array(
					'key'     => 'data_fillimit',
					'value'   => array($date_from, $date_to),
					'compare' => 'BETWEEN',
					'type'    => 'DATE'
				),
				array(
					'key'     => 'data_mbarimit',
					'value'   => array($date_from, $date_to),
					'compare' => 'BETWEEN',
					'type'    => 'DATE'
				)
			);
		} elseif ($date_from && !$date_to) {
			$meta_query[] = array(
				'relation' => 'OR',
				array(
					'key'     => 'data_fillimit',
					'value'   => $date_from,
					'compare' => '>=',
					'type'    => 'DATE'
				),
				array(
					'key'     => 'data_mbarimit',
					'value'   => $date_from,
					'compare' => '>=',
					'type'    => 'DATE'
				)
			);
		} elseif (!$date_from && $date_to) {
			$meta_query[] = array(
				'relation' => 'OR',
				array(
					'key'     => 'data_fillimit',
					'value'   => $date_to,
					'compare' => '<=',
					'type'    => 'DATE'
				),
				array(
					'key'     => 'data_mbarimit',
					'value'   => $date_to,
					'compare' => '<=',
					'type'    => 'DATE'
				)
			);
		}
		$ajaxposts['meta_query'] = isset($ajaxposts['meta_query']) ? array_merge($ajaxposts['meta_query'], $meta_query) : $meta_query;
		
	} 
	elseif ($type === 'video') {
		$ajaxposts['meta_key'] = 'date';
	} else {
		$ajaxposts['meta_key'] = 'data_e_dokumentit';
	}
	
	if (($type !== 'events' && $type !== 'public-calls') && ($date_from || $date_to)) {
		$meta_query = array('relation' => 'AND');

		if ($date_from && $date_to) {
			$meta_query[] = array(
				'key'     => $ajaxposts['meta_key'],
				'value'   => array($date_from, $date_to),
				'compare' => 'BETWEEN',
				'type'    => 'DATE'
			);
		} elseif ($date_from && !$date_to) {
			$meta_query[] = array(
				'key'     => $ajaxposts['meta_key'],
				'value'   => $date_from,
				'compare' => '>=',
				'type'    => 'DATE'
			);
		} elseif (!$date_from && $date_to) {
			$meta_query[] = array(
				'key'     => $ajaxposts['meta_key'],
				'value'   => $date_to,
				'compare' => '<=',
				'type'    => 'DATE'
			);
		}

		$ajaxposts['meta_query'] = isset($ajaxposts['meta_query']) ? array_merge($ajaxposts['meta_query'], $meta_query) : $meta_query;
	}

	// If query needs to be pre filtered
	if ($prefiltered_tax) {
		$terms_year['tax_query'] = array(
			'relation' => 'AND',
			array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => array($prefiltered_tax),
			),
		);
		$ajaxposts['tax_query'][] = array(
			'relation' => 'AND',
			array(
				'taxonomy' => $taxonomy,
				'field' => 'slug',
				'terms' => array($prefiltered_tax)
			)
		);
	}

	// Taxonomy to filter with
	if ($tax && $tax != 'all') {
		$terms_year['tax_query'] = array(
			array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => array($tax)
			)
		);
		$ajaxposts['tax_query'][] = array(
			array(
				'taxonomy' => $taxonomy,
				'field' => 'slug',
				'terms' => array($tax)
			)
		);
	}

	$years = array();
	$query_year = new WP_Query($terms_year);

	if ($query_year->have_posts()) :
		while ($query_year->have_posts()) : $query_year->the_post();
			 if ($type === 'page') {
				 $data_doc = get_field('data_e_dokumentit') ?? null;
			 } elseif ($type === 'news') {
				 $data_doc = get_field('data_e_lajmit') ?? null;
			 } elseif ($type === 'events') {
				 $data_doc = get_field('data_fillimit') ?? null;
			 } else if ($tpye === 'public-calls') {
				 $data_doc = get_field('data_fillimit') ?? null;
			 } else if ($tpye === 'documents') {
				 $data_doc = get_field('data_e_dokumentit') ?? null;
			 }

			if ($data_doc) {
				$data_doc_time = str_replace('/', '-', $data_doc);
				$custom_field_year = date('Y', strtotime($data_doc_time));
				if ($custom_field_year && !in_array($custom_field_year, $years)) {
					$years[] = $custom_field_year;
				}
			}
		endwhile;
		wp_reset_postdata();
	endif;
	
// 		$ajaxposts['meta_query'][] = array(
// 			'key'     => 'data_e_dokumentit',
// 			'value'   => array("2022-01-01", "2022-12-31"),
// 			'compare' => 'BETWEEN',
// 			'type'    => 'DATE'
// 		);
	
	if ($date_year && $date_year != 'all') {
		$start_date = "$date_year-01-01";
		$end_date = "$date_year-12-31";
		$ajaxposts['meta_query'][] = array(
			'key'     => 'data_e_dokumentit',
			'value'   => array($start_date, $end_date),
			'compare' => 'BETWEEN',
			'type'    => 'DATE'
		);
	}

	$term = get_term_by('slug', $tax, $taxonomy);
	// $years = [];
	$data = '';
	$pagination = '';
	$query = new WP_Query($ajaxposts);

	if ($query->have_posts()) {
		while ($query->have_posts()) : $query->the_post();
			$id = get_the_ID();
			if ($template_id):
				$data .= "<div class='ItemsFilter-item item-$id'>" . do_shortcode("[elementor-template id=$template_id]") . "</div>";
			elseif ($custom_template):
				$data .= render_template(get_stylesheet_directory() . '/Filter/parts/' . $custom_template . '.php');
			else:
				$data .= render_template(get_stylesheet_directory() . '/Filter/parts/default.php');
			endif;
		endwhile;
	} else {
		$data = '<h2 class="no-results">' . __('Nuk u gjet asnje rezultat.', 'filter-items') . '</h2>';
	}

	// Sort the years in descending order
	rsort($years);

	$current_year = date("Y");
	$nr_years = count($years) > 0 ? count($years) : 5;

	$pagination = paginate_links(array(
		'base'         => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
		'total'        => $query->max_num_pages,
		'current'      => max(1, $page),
		'format'       => '?paged=%#%',
		'show_all'     => false,
		'type'         => 'plain',
		'end_size'     => 2,
		'mid_size'     => 1,
		'prev_next'    => true,
		'prev_text'    => sprintf('<i></i> %1$s', __('<', 'filter-items')),
		'next_text'    => sprintf('%1$s <i></i>', __('>', 'filter-items')),
		'add_args'     => false,
		'add_fragment' => '',
	));

	// echo $response;
	return wp_send_json(compact('data', 'pagination', 'term', 'years', 'date_from', 'date_to', 'type'), 200);
}
add_action('wp_ajax_items_filter_ajax', 'items_filter_ajax');
add_action('wp_ajax_nopriv_items_filter_ajax', 'items_filter_ajax');

// show_external_field();

// Gets News Date Automatically 
function set_default_acf_date( $post_id ) {
    // Check if the post type is 'news'
    if ( get_post_type( $post_id ) == 'news' ) {
        // Get the current date
        $current_date = new DateTime('now', new DateTimeZone('Europe/Zagreb'));
        $formatted_date = $current_date->format('Ymd H:i'); // Save in YYYYMMDD format
        // Check if the field is empty
        if ( empty( get_field('data_e_lajmit', $post_id) ) ) {
            // Update the ACF field with the current date
            update_field('data_e_lajmit', $formatted_date, $post_id);
        }
    }
}
// Run this function when a post is saved
add_action('save_post', 'set_default_acf_date');


// Gets Document Date Automatically 
function set_default_acf_dateDoc($post_id)
{
	// Check if the post type is 'post'
	 if (in_array(get_post_type($post_id), ['post', 'document'])) {
		// Get the current date
		$current_date = new DateTime('now', new DateTimeZone('Europe/Zagreb'));
		$formatted_date = $current_date->format('Ymd');
		// Check if the field is empty
		if (empty(get_field('data_e_dokumentit', $post_id))) {
			// Update the ACF field with the current date
			update_field('data_e_dokumentit', $formatted_date, $post_id);
		}
	}
}
// Run this function when a post is saved
add_action('save_post', 'set_default_acf_dateDoc');

//Gets FileType Automatically and paass it down to another field
function update_document_filetype()
{
	// Check if the 'ngarko_dokumentin' field has a file
	if (isset($_POST['acf']['field_6874bc38ddabe'])) {
		$file_id = $_POST['acf']['field_6874bc38ddabe'];
		$file = get_post($file_id);
		if ($file) {
			// Get the file type from the file URL
			$filetype = wp_check_filetype($file->guid);
			// Update the 'document_filetype' field with the file type
			$_POST['acf']['field_662652b65b40d'] = $filetype['ext'];
		}
	}
}
// Run this function before ACF saves the $_POST['acf'] data
add_action('acf/validate_save_post', 'update_document_filetype', 1);

//Gets Thirrje publike Filetype Automatically and paass it down to another field
function update_Aktivitetedocument_filetype()
{
        // Check if the 'ngarko_dokumentin' field has a file
        if (isset($_POST['acf']['field_6634946c43f3b'])) {
                $file_id = $_POST['acf']['field_6634946c43f3b'];
                $file = get_post($file_id);
                if ($file) {
                        // Get the file type from the file URL
                        $filetype = wp_check_filetype($file->guid);
                        // Update the 'document_filetype' field with the file type
                        $_POST['acf']['field_6661b1ae5973b'] = $filetype['ext'];
                }
        }
}
// Run this function before ACF saves the $_POST['acf'] data
add_action('acf/validate_save_post', 'update_Aktivitetedocument_filetype', 1);

//changes the limit of acf in Display Conditions Dropdown 
function custom_custom_fields_meta_limit($limit)
{
	$new_limit = 300; // Change this to your desired limit
	return $new_limit;
}

add_filter('elementor_pro/display_conditions/dynamic_tags/custom_fields_meta_limit', 'custom_custom_fields_meta_limit');

function format_acf_date() {
    $post_id = get_the_ID();
    
    $date = get_field('data_e_lajmit', $post_id) ?? null;

    if ($date) {
        $date_parts = explode(' ', $date);
        if (count($date_parts) > 1) {
            $date_part = $date_parts[0];
            $time_part = $date_parts[1];
            $date_components = explode('/', $date_part);
            $formatted_date_string = $date_components[2] . '-' . $date_components[1] . '-' . $date_components[0] . ' ' . $time_part;
        } else {
            $formatted_date_string = $date;
        }

        $timestamp = strtotime($formatted_date_string);
        if ($timestamp !== false) {
            return date('d/m/Y', $timestamp);
        }
    }

    return '';
}

function news_date_shortcode() {
    return format_acf_date();
}
add_shortcode('news_date', 'news_date_shortcode');

function custom_admin_css()
{
	echo '
    <style type="text/css">
         .acf-field-662652b65b40d, .acf-field-6661b1ae5973b{
		   display: none;
		}
    </style>
    ';
}
add_action('admin_head', 'custom_admin_css');

function display_current_post_content($atts) {
    // Ensure global post object is in scope
    global $post;

    // Check if we're within a single post context
    if (is_singular()) {
        return apply_filters('the_content', $post->post_content);
    }

    return 'No content found.';
}
add_shortcode('current_post_content', 'display_current_post_content');

//Dynamic Copyright year in footer 
function current_year_shortcode() {
    return date('Y');
}
add_shortcode('year', 'current_year_shortcode');

// This code makes all post types list in the dashboard desc
function my_admin_default_order_redirect() {
    global $pagenow;

    // Only run on the listing page for post types.
    if ( is_admin() && 'edit.php' === $pagenow && isset( $_GET['post_type'] ) ) {
        // Check if orderby is missing.
        if ( ! isset( $_GET['orderby'] ) ) {
            // Build the URL with default ordering.
            $redirect_url = add_query_arg( array(
                'orderby' => 'date',
                'order'   => 'desc'
            ), remove_query_arg( array( 'orderby', 'order' ) ) );

            // Redirect only if the current URL is different.
            if ( $redirect_url !== $_SERVER['REQUEST_URI'] ) {
                wp_safe_redirect( $redirect_url );
                exit;
            }
        }
    }
}
add_action( 'admin_init', 'my_admin_default_order_redirect' );


function my_event_dates_output() {
        // Retrieve raw ACF values
        $start_date = get_field('data_fillimit') ?? null;
        $end_date   = get_field('data_mbarimit') ?? null;

        // Convert dates using DateTime::createFromFormat to avoid parsing issues
        $start_date_obj = null;
        $end_date_obj   = null;

        if ( $start_date ) {
            $start_date_obj = DateTime::createFromFormat('d/m/Y H:i', $start_date);
        }

        if ( $end_date ) {
            $end_date_obj = DateTime::createFromFormat('d/m/Y H:i', $end_date);
        }

        // Format date output based on date and time comparison
        $date_output_start = '';
        $date_output_end   = '';

        if ( $start_date_obj && $end_date_obj ) {
            // Compare dates (ignoring time) by formatting to Y-m-d
            $start_only = $start_date_obj->format('Y-m-d');
            $end_only   = $end_date_obj->format('Y-m-d');

            if ( $start_only === $end_only ) {
                // Same date: check if times are the same
                $start_time = $start_date_obj->format('H:i');
                $end_time   = $end_date_obj->format('H:i');
                if ( $start_time === $end_time ) {
                    // Same time: show only start date and time
                    $date_output_start = $start_date_obj->format('d/m/Y H:i');
                } else {
                    // Different times: show start date with start time and end time
                    $date_output_start = $start_date_obj->format('d/m/Y H:i');
                    $date_output_end   = $end_date_obj->format('H:i');
                }
            } else {
                // Different dates: show start and end dates without times
                $date_output_start = $start_date_obj->format('d/m/Y');
                $date_output_end   = $end_date_obj->format('d/m/Y');
            }
        } elseif ( $start_date_obj ) {
            // Only start date available
            $date_output_start = $start_date_obj->format('d/m/Y H:i');
        } elseif ( $end_date_obj ) {
            // Only end date available
            $date_output_end = $end_date_obj->format('d/m/Y H:i');
        }

        // Build and return the final string
        $output = $date_output_start;
        if ( $date_output_end ) {
            $output .= ' – ' . $date_output_end;
        }

        return $output;
    }

    // Register [event_dates] shortcode
    add_shortcode( 'event_dates', 'my_event_dates_output' );


function trainer_certifications_badges_shortcode() {
    if (!function_exists('get_field')) {
        return '';
    }

    $certifications = get_field('certifications'); // change to your exact field name

    if (empty($certifications) || !is_array($certifications)) {
        return '';
    }

    $output = '<div class="trainer-certifications">';

    foreach ($certifications as $certification) {
        $output .= '<span class="trainer-certification-badge">' . esc_html($certification) . '</span>';
    }

    $output .= '</div>';

    return $output;
}
add_shortcode('trainer_certifications', 'trainer_certifications_badges_shortcode');


function trainer_categories_badges_shortcode() {
    $terms = get_the_terms(get_the_ID(), 'category'); // change taxonomy if needed

    if (empty($terms) || is_wp_error($terms)) {
        return '';
    }

    $output = '<div class="trainer-categories">';

    foreach ($terms as $term) {
        $output .= '<span class="trainer-category-badge">' . esc_html($term->name) . '</span>';
    }

    $output .= '</div>';

    return $output;
}
add_shortcode('trainer_categories', 'trainer_categories_badges_shortcode');

// global $language;
// $language = apply_filters('wpml_current_language', NULL); // Get the current WPML language

// Schedule Tabs Shortcode
function my_schedule_tabs_shortcode() {
    $taxonomy = 'schedulecategory'; // change to 'schedule_day' if you use custom taxonomy

// 	if ($language == 'en') {

    $days_order = [
        'monday'    => 'Monday',
        'tuesday'   => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday'  => 'Thursday',
        'friday'    => 'Friday',
        'saturday'  => 'Saturday',
        'sunday'    => 'Sunday',
    ];

    ob_start();

    // Get terms that match your day names
    $terms = get_terms([
        'taxonomy'   => $taxonomy,
        'hide_empty' => false,
    ]);

    if (is_wp_error($terms) || empty($terms)) {
        return '<p>No schedule days found.</p>';
    }

    // Reorder terms to Monday -> Sunday
    $ordered_terms = [];
    foreach ($days_order as $slug => $label) {
        foreach ($terms as $term) {
            if (strtolower($term->name) === strtolower($label) || strtolower($term->slug) === $slug) {
                $ordered_terms[$slug] = $term;
                break;
            }
        }
    }

    if (empty($ordered_terms)) {
        return '<p>No matching weekday categories found.</p>';
    }

    $first = true;
    ?>
    
    <div class="schedule-tabs-wrapper">
        <div class="schedule-tabs-buttons">
            <?php foreach ($ordered_terms as $slug => $term) : ?>
                <button 
                    class="schedule-tab-btn <?php echo $first ? 'active' : ''; ?>" 
                    data-day="<?php echo esc_attr($slug); ?>"
                    type="button"
                >
                    <?php echo esc_html($term->name); ?>
                </button>
                <?php $first = false; ?>
            <?php endforeach; ?>
        </div>

        <div class="schedule-tabs-content">
            <?php 
            $first_panel = true;
            foreach ($ordered_terms as $slug => $term) :

                $args = [
                    'post_type'      => 'events',
                    'posts_per_page' => -1,
                    'post_status'    => 'publish',
                    'tax_query'      => [
                        [
                            'taxonomy' => $taxonomy,
                            'field'    => 'term_id',
                            'terms'    => $term->term_id,
                        ]
                    ],
                    'meta_key'       => 'data_fillimit',
                    'orderby'        => 'meta_value',
                    'order'          => 'ASC',
                ];

                $query = new WP_Query($args);
                ?>
                <div class="schedule-tab-panel <?php echo $first_panel ? 'active' : ''; ?>" data-day="<?php echo esc_attr($slug); ?>">
                    <div class="schedule-panel-inner">
                        <h3 class="schedule-day-title"><?php echo esc_html($term->name); ?></h3>

                        <?php if ($query->have_posts()) : ?>
                            <div class="schedule-grid">
                                <?php while ($query->have_posts()) : $query->the_post(); 
                                    $instructor = get_field('instructor:');
                                    $start_time = get_field('data_fillimit');
                                    $end_time   = get_field('data_mbarimit');
                                    ?>
                                    <div class="schedule-card">
                                        <div class="schedule-card-flex">
                                            <div class="schedule-card-left">
                                                <h4 class="schedule-class-title"><?php the_title(); ?></h4>
                                                <?php if ($instructor) : ?>
                                                    <p class="schedule-instructor">Instructor: <?php echo esc_html($instructor); ?></p>
                                                <?php endif; ?>
                                            </div>

                                            <?php if ($start_time || $end_time) : ?>
                                                <span class="schedule-time">
                                                    <?php echo esc_html($start_time); ?><?php echo ($start_time && $end_time) ? ' - ' : ''; ?><?php echo esc_html($end_time); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else : ?>
                            <p class="schedule-empty">No classes for <?php echo esc_html($term->name); ?>.</p>
                        <?php endif; ?>

                        <?php wp_reset_postdata(); ?>
                    </div>
                </div>
                <?php 
                $first_panel = false;
            endforeach; 
            ?>
        </div>
    </div>

    <style>
        .schedule-tabs-wrapper {
            width: 100%;
        }

        .schedule-tabs-buttons {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            margin-bottom: 32px;
        }

        .schedule-tab-btn {
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            background: #1f2937;
            color: #fff;
            transition: all 0.25s ease;
        }

        .schedule-tab-btn.active {
            background: #FFFDF2;
            color: #000;
        }

	   .schedule-tab-btn:hover {
            background: #FFFDF2;
            color: #000;
        }

        .schedule-tab-panel {
            display: none;
            background: #111827;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .schedule-tab-panel.active {
            display: block;
        }

        .schedule-panel-inner {
            padding: 24px;
        }

        .schedule-day-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 24px;
            color: #FFFDF2;
        }

        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 16px;
        }

        @media (min-width: 768px) {
            .schedule-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .schedule-card {
            background: #1f2937;
            padding: 16px;
            border-radius: 12px;
        }

        .schedule-card-flex {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        @media (min-width: 640px) {
            .schedule-card-flex {
                flex-direction: row;
                justify-content: space-between;
                align-items: flex-start;
            }
        }

        .schedule-class-title {
            font-size: 18px;
            font-weight: 700;
            color: #FFFDF2;
            margin: 0 0 6px 0;
        }

        .schedule-instructor {
            color: #9ca3af;
            margin: 0;
        }

        .schedule-time {
            background: #000;
            color: #FFFDF2;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 14px;
            width: fit-content;
            white-space: nowrap;
        }

        .schedule-empty {
            color: #d1d5db;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const wrappers = document.querySelectorAll('.schedule-tabs-wrapper');

            wrappers.forEach(function(wrapper) {
                const buttons = wrapper.querySelectorAll('.schedule-tab-btn');
                const panels = wrapper.querySelectorAll('.schedule-tab-panel');

                buttons.forEach(function(button) {
                    button.addEventListener('click', function() {
                        const day = this.getAttribute('data-day');

                        buttons.forEach(btn => btn.classList.remove('active'));
                        panels.forEach(panel => panel.classList.remove('active'));

                        this.classList.add('active');

                        const target = wrapper.querySelector('.schedule-tab-panel[data-day="' + day + '"]');
                        if (target) {
                            target.classList.add('active');
                        }
                    });
                });
            });
        });
    </script>

    <?php
    return ob_get_clean();
}
add_shortcode('schedule_tabs', 'my_schedule_tabs_shortcode');
