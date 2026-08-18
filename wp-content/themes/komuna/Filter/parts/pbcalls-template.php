<?php

$type  = get_field('file_type') ?? null;

$start_date = get_field('data_fillimit') ?? null;
$end_date = get_field('data_mbarimit') ?? null;

if ($start_date) {
    $date_parts = explode(' ', $start_date);
    if (count($date_parts) > 1) {
        $date_part = $date_parts[0];
        $time_part = $date_parts[1];
        $date_components = explode('/', $date_part);
        $formatted_date_string = $date_components[2] . '-' . $date_components[1] . '-' . $date_components[0] . ' ' . $time_part;
        $formatted_startDate = date('d/m/Y', strtotime($formatted_date_string));
    } else {
        $formatted_startDate = date('d/m/Y', strtotime($start_date));
    }
} else {
    $formatted_startDate = null;
}

if ($end_date) {
    $date_parts = explode(' ', $end_date);
    if (count($date_parts) > 1) {
        $date_part = $date_parts[0];
        $time_part = $date_parts[1];
        $date_components = explode('/', $date_part);
        $formatted_date_string = $date_components[2] . '-' . $date_components[1] . '-' . $date_components[0] . ' ' . $time_part;
        $formatted_endDate = date('d/m/Y', strtotime($formatted_date_string));
    } else {
        $formatted_endDate = date('d/m/Y', strtotime($end_date));
    }
} else {
    $formatted_endDate = null;
}

?>



<a href="<?php the_permalink(); ?>">

    <div class="pubItem-body">

         <?php if ($type == 'pdf' && !has_post_thumbnail()) : ?>
            <div class="pub-svg">
                <img class="pub-icone" src="<?php echo get_stylesheet_directory_uri() . '/assets/images/pdf.png'; ?>" alt="PDF Icon">
            </div>
        <?php elseif (($type == 'doc' || $type == 'docx') && !has_post_thumbnail()) : ?>
            <div class="pub-svg">
                <img class="pub-icone" src="<?php echo get_stylesheet_directory_uri() . '/assets/images/docx.png'; ?>" alt="DOCX Icon">
            </div>
        <?php elseif (($type == 'xls' || $type == 'xlsx') && !has_post_thumbnail()) : ?>
            <div class="pub-svg">
                <img class="pub-icone" src="<?php echo get_stylesheet_directory_uri() . '/assets/images/xls.png'; ?>" alt="XLS Icon">
            </div>
        <?php elseif (has_post_thumbnail()) : ?>
            <div class="pub-image">
                <img class="pub-img" src="<?php echo get_the_post_thumbnail_url(); ?>" alt="<?php the_title_attribute(); ?>">
            </div>
        <?php endif; ?>

        <div class="pub-description">

            <div class="pub-textBody">

                <div class="pub-title">

                    <?php the_title(); ?>

                </div>
				
				<div class="pub-content">

                    <?php

                    $content = get_the_content();

                    $trimmed_content = wp_trim_words(wp_strip_all_tags($content), 24, '...');

                    echo $trimmed_content;

                    ?>

                </div>

            </div>

            <?php if ($start_date || $end_date) : ?>
                  <div class="pub-info <?php echo $end_date ? 'pub-info-fromTo' : ''; ?>">
                    <svg aria-hidden="true" class="e-font-icon-svg e-fas-calendar" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 192h424c6.6 0 12 5.4 12 12v260c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V204c0-6.6 5.4-12 12-12zm436-44v-36c0-26.5-21.5-48-48-48h-48V12c0-6.6-5.4-12-12-12h-40c-6.6 0-12 5.4-12 12v52H160V12c0-6.6-5.4-12-12-12h-40c-6.6 0-12 5.4-12 12v52H48C21.5 64 0 85.5 0 112v36c0 6.6 5.4 12 12 12h424c6.6 0 12-5.4 12-12z"></path>
                    </svg>
                    <span class="pub-date">
                        <?php
                        echo $formatted_startDate;
                        if ($end_date) {
                            echo ' - ' . $formatted_endDate;
                        }
                        ?>
                    </span>
                </div>
            <?php endif; ?>



        </div>

    </div>

</a>