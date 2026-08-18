<?php
$page_url        = get_permalink();
$post_type       = isset($args[0]['post_type']) ? $args[0]['post_type'] : 'post';
$per_page        = (int) isset($args[0]['per_page']) ? $args[0]['per_page'] : 12;
$date_filter     = (bool) isset($args[0]['filter_date']) ? $args[0]['filter_date'] : false;
$show_taxonomies = (bool) isset($args[0]['show_taxonomies']) ? $args[0]['show_taxonomies'] : false;
$show_search     = (bool) isset($args[0]['show_search']) ? $args[0]['show_search'] : false;
$show_pagination     = (bool) isset($args[0]['show_pagination']) ? $args[0]['show_pagination'] : true;
$show_loader     = (bool) isset($args[0]['show_loader']) ? $args[0]['show_loader'] : true;
$nr_years        = (bool) isset($args[0]['nr_years']) ? $args[0]['nr_years'] : 5;
$prefiltered_tax = isset($args[0]['pre_filter_term']) ? $args[0]['pre_filter_term'] : false;
$pre_tax_name     = isset($args[0]['pre_filter_tax']) ? $args[0]['pre_filter_tax'] : false;
$use_parent      = (bool) isset($args[0]['pre_filter_parent']) ? $args[0]['pre_filter_parent'] : false;
$tax_by_query    = (bool) isset($args[0]['tax_by_query']) ? $args[0]['tax_by_query'] : false;
$template_id     = isset($args[0]['template_id']) ? $args[0]['template_id'] : false;
$date_field      = isset($args[0]['date_field']) ? $args[0]['date_field'] : false;
$ajax            = (bool) isset($args[0]['ajax']) ? $args[0]['ajax'] : false;
$custom_template = (string) isset($args[0]['custom_tpl']) ? $args[0]['custom_tpl'] : null;
$type            = isset($args[0]['type']) ? $args[0]['type'] : 'page';
$taxonomies_all       = isset($args[0]['taxonomies_all']) ? $args[0]['taxonomies_all'] : 'All';
$year_all       = isset($args[0]['year_all']) ? $args[0]['year_all'] : 'All';
$search_button   = isset($args[0]['search_button']) ? $args[0]['search_button'] : 'Search';
$search_placeholder       = isset($args[0]['search_placeholder']) ? $args[0]['search_placeholder'] : 'Search...';

global $paged;
if (get_query_var('paged')) {
    $paged = get_query_var('paged');
} elseif (get_query_var('page')) {
    $paged = get_query_var('page');
} else {
    $paged = 1;
}

// $taxonomy = $post_type == 'post' ? 'categories' : $post_type . '-category';

$tax_name = $post_type == 'post' ? 'category' : $post_type . '-category';

$taxonomy = $pre_tax_name ? $pre_tax_name : $tax_name;

$curr_tax = isset($_GET['tax']) ? $_GET['tax'] : null;

$date_year = isset($_GET['date-year']) ? $_GET['date-year'] : null;
$search = isset($_GET['search_text']) ? $_GET['search_text'] : null;


// Get Available Years
/* 
    Get Available Years - start
*/

$terms_year = array(
    'post_type'         => $post_type,
    'posts_per_page'    => -1
);

if ($prefiltered_tax) {
    $terms_year['tax_query'] = array(
        'relation' => 'AND',
        array(
            'taxonomy' => $taxonomy,
            'field'    => 'slug',
            'terms'    => array($prefiltered_tax),
        ),
    );
}

if ($curr_tax) {
    $terms_year['tax_query'] = array(
        array(
            'taxonomy' => $taxonomy,
            'field'    => 'slug',
            'terms'    => array($curr_tax)
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
       } else if ($type === 'events') {
			$data_doc = get_field('data_fillimit') ?? null;
		} elseif ($type === 'public-calls') {
            $data_doc = get_field('data_fillimit') ?? null;
        } elseif ($type === 'video') {
            $data_doc = get_field('data_e_videos') ?? null;
        }  elseif ($type === 'documents') {
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
// var_dump($years);
// Sort the years in descending order
rsort($years);

$current_year = date("Y");
$nr_years = count($years) > 0 ? count($years) : null;
/* 
    Get Available Years - end
*/

$tax_query = array();

// taxonomies variable
$taxonomies = array();

$query_args = array(
    'post_type'      => $post_type,
    'posts_per_page' => $per_page,
    'paged'          => $paged,
    's'              => $search,
	'post_status'    => 'publish',
    'meta_key' => ($type === 'page') 
		? 'data_e_dokumentit' 
		 : (($type === 'news') 
            ? 'data_e_lajmit' 
            : (($type === 'events') 
                ? 'data_fillimit' 
                : (($type === 'public-calls') 
                    ? 'data_fillimit' 
                    : (($type === 'video') 
                        ? 'data_e_videos' 
                        : (($type === 'documents')
                            ? 'data_e_dokumentit'
                            : ''
                        )
                    )
                )
            )
        ),
    'orderby'        => 'meta_value',
    'order'          => 'DESC',
    'meta_type'      => 'DATETIME'
);


// If query needs to be pre filtered
if ($prefiltered_tax) {
    $query_args['tax_query'][] = array(
        'relation' => 'AND',
        array(
            'taxonomy' => $taxonomy,
            'field' => 'slug',
            'terms' => array($prefiltered_tax)
        )
    );
}

// Taxonomy to filter with
if ($curr_tax) {
    $query_args['tax_query'][] = array(
        array(
            'taxonomy' => $taxonomy,
            'field'    => 'slug',
            'terms'    => array($curr_tax)
        )
    );
}

if ($date_year) {
    $query_args['year'] = $date_year;
}

// var_dump($curr_tax);

$query = new WP_Query($query_args);
// var_dump($query->found_posts);
// check if taxonomies should be shown
if ($show_taxonomies) {
    // check if taxonomy exists
    if (get_taxonomy($taxonomy)) {
        // fill $taxonomies
        // Get taxonomies from parent
        if ($use_parent && $use_parent != 'false') {
            $term = get_term_by('slug', $prefiltered_tax, $taxonomy);
            $taxonomies = get_term_children($term->term_id, $taxonomy);
        } else {
            // Get taxonomies from the query posts if query has posts
            if ($tax_by_query && $query->have_posts()) {
                $taxonomies = get_terms([
                    'taxonomy'   => $taxonomy,
                    'object_ids' => wp_list_pluck($query->posts, 'ID'),
                    'hide_empty' => true,
                ]);
            } else {
                $taxonomies = get_terms(array(
                    'taxonomy' => $taxonomy,
                    'hide_empty' => true,
                ));
            }
        }
    }
}

?>
<div <?php echo $template_id ? "data-template=$template_id" : '' ?> data-customtpl="<?php echo $custom_template ?>" data-perpage="<?php echo $per_page ?>" data-pt='<?php echo $post_type ?? 'post' ?>' data-taxonomy="<?php echo $taxonomy ?>" data-yearall="<?php echo $year_all ?>" data-prefilter="<?php echo $prefiltered_tax ?>" data-datefield="<?php echo $date_field ?>" class="ItemsFilter docs-page docs-pageSection<?php echo $show_taxonomies && $taxonomies ? 'Tax' : '' ?>  <?php echo $ajax ? 'ItemsFilter-Ajax' : '' ?>" <?php echo $type ? "data-type='$type'" : '' ?>>
    <?php if ($show_taxonomies && $taxonomies) : ?>
        <div class="taxonomies-wrapper">
            <div class="taxonomies-body">
                <ul class="taxonomies-list">
                    <li class="taxonomies-list-item">
                        <a class="filter-item" data-param="tax" data-slug="all" href="<?php echo $page_url ?>?tax="><?php echo $taxonomies_all; ?></a>
                    </li>
                    <?php foreach ($taxonomies as $tax) :
                        if (!isset($tax->slug)) :
                            $tax = get_term_by('id', $tax, $taxonomy);
                        endif;
                    ?>
                        <li class="taxonomies-list-item">
                            <a class="filter-item" data-param="tax" data-slug="<?php echo $tax->slug ?>" href="<?php echo $page_url ?>?tax=<?php echo $tax->slug ?>"><?php echo $tax->name ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>
    <div class="page-container-docs <?php echo ($type == 'page') ? 'page-blockList' : (($type == 'news' || $type == 'events' || $type == 'public-calls') ? 'news-blockList' : ''); ?>">
        <?php if ($show_taxonomies && $taxonomies) : ?>
            <div class="ItemsFilter-title">
                <h1 data-page=<?php the_title() ?>><?php the_title() ?></h1>
            </div>
        <?php endif; ?>
        <div class="ItemsFilter-wrapper flex">
            <?php if ($show_search) : ?>
                <div class="search-filters-body">
                    <?php if ($show_search) : ?>
                        <div class="search-wrapper">
                            <form role="search" method="get" action="<?php echo $page_url ?>" id="searchform">
                                <div class="search-grid">
                                    <?php if ($type === "news" || $type === "events" || $type === "public-calls") : ?>
                                        <div class="ItemsFilter-dateFromTo">
                                            <input type="date" name="date-from" class="input-from">
                                            <input type="date" name="date-to" class="input-to">
                                        </div>
                                    <?php endif; ?>
                                    <input type="text" class="search-input" name="search_text" placeholder="<?php echo $search_placeholder; ?>" value="<?php echo isset($_GET['search_text']) ? esc_attr($_GET['search_text']) : ''; ?>" />
                                    <?php if ($date_filter && $type === "page") : ?>
                                        <div class="date-filters">
                                           <select class="year-selectBody" name="date_year">
                                                <option value=""><?php echo $year_all; ?></option>
                                                <?php
                                                if (count($years) > 0) :
                                                    foreach ($years as $year) :
                                                    ?>
                                                        <option value="<?php echo $year; ?>" <?php selected($date_year, $year); ?>><?php echo $year; ?></option>
                                                <?php
                                                    endforeach;
                                                endif;
                                                ?>
                                            </select>
                                        </div>
                                    <?php endif; ?>
                                    <button type="submit" class="btn-searchf" id="searchsubmit"><?php echo $search_button; ?></button>
                                </div>
                                <noscript><input type="submit" value="Filter"></noscript>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
		  <?php if ($show_loader) : ?>
                                        <div id="website-loader" class="website-loader">
                                                <div class="loader-spinner"></div>
                                        </div>
                        <?php endif; ?>
			<div class="posts-wrapper-inner <?php echo ($type == 'page') ? 'docs-list' : (($type == 'news' || $type == 'public-calls') ? 'news-list' : (($type == 'events') ? 'events-list' : '')); ?>">
            <?php

            while ($query->have_posts()) : $query->the_post();
            ?>
                <?php if ($template_id) : ?>
                    <div class="ItemsFilter-item item-<?php the_ID() ?>">
                        <?php echo do_shortcode("[elementor-template id=$template_id]") ?>
                    </div>
                <?php elseif ($custom_template) : ?>
                    <div class="ItemsFilter-item item-<?php the_ID() ?>">
                        <?php include(get_stylesheet_directory() . '/Filter/parts/' . $custom_template . '.php'); ?>
                    </div>
                <?php else : ?>
                    <?php include(get_stylesheet_directory() . '/Filter/parts/default.php'); ?>
                <?php endif; ?>

            <?php
            endwhile;
            ?>
            <?php wp_reset_postdata() ?>
        </div>
		<?php if ($show_pagination) : ?>
        <div class="pagination">
            <div class='dpn-pagination'>
                <?php
                // echo paginate_links(array(
                //     'total' => $query->max_num_pages,
                //     'current' => max( 1, $paged ),
                //     'prev_text' => __('<div class="preious-page">Prev</div>'),
                //     'next_text' => __('<div class="next-page">Next</div>')
                // )) ;

                echo paginate_links(array(
                    'base'         => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                    'total'        => $query->max_num_pages,
                    'current'      => max(1, get_query_var('paged')),
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
                ?>
            </div>
        </div>
			<?php endif; ?>
    </div>
</div>