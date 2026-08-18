<?php
$date = get_field('data_e_lajmit') ?? null;

if ($date) {
    $date_parts = explode(' ', $date);
    if (count($date_parts) > 1) {
        $date_part = $date_parts[0];
        $time_part = $date_parts[1];
        $date_components = explode('/', $date_part);
        $formatted_date_string = $date_components[2] . '-' . $date_components[1] . '-' . $date_components[0] . ' ' . $time_part;
        $formatted_date = date('d/m/Y', strtotime($formatted_date_string));
    } else {
        $formatted_date = date('d/m/Y', strtotime($date));
    }
} else {
    $formatted_date = null;
}
?>

<a href="<?php the_permalink(); ?>">

    <div class="newsItem-body">

        <?php if (has_post_thumbnail()) : ?>

            <div class="news-image">

                <img src="<?php echo get_the_post_thumbnail_url(); ?>" alt="<?php the_title_attribute(); ?>">

            </div>
		 <?php else: ?>
            <!-- Optional: Display a fallback image if no featured image -->
            <div class="news-image">
                <img src="http://192.168.2.209:100/dpn-agencytemplate/wp-content/uploads/2025/05/abbee1fc031c9af668accbbee24b70813b4fa426.png" alt="Fallback Image">
            </div>
        <?php endif; ?>

        <div class="news-textBody">

            <div class="news-title">

                <?php the_title(); ?>

            </div>

            <div class="news-info">

                <span class="news-date"><?php echo $formatted_date ?></span>

            </div>

            <div class="news-content">

                <?php

                $content = get_the_content();

                $trimmed_content = wp_trim_words(wp_strip_all_tags($content), 20, '...');

                echo $trimmed_content;

                ?>

            </div>

        </div>

    </div>

</a>