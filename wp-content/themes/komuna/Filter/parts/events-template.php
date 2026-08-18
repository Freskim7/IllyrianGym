<?php
$location = get_field('location') ?? null;
$start_date = get_field('data_fillimit') ?? null;
$end_date = get_field('data_mbarimit') ?? null;

// Convert dates using DateTime::createFromFormat to avoid parsing issues
$start_date_obj = null;
$end_date_obj = null;

if ($start_date) {
    $start_date_obj = DateTime::createFromFormat('d/m/Y H:i', $start_date);
}

if ($end_date) {
    $end_date_obj = DateTime::createFromFormat('d/m/Y H:i', $end_date);
}

// Format date output based on date and time comparison
$date_output_start = '';
$date_output_end = '';

if ($start_date_obj && $end_date_obj) {
    // Compare dates (ignoring time) by formatting to Y-m-d
    $start_date_only = $start_date_obj->format('Y-m-d');
    $end_date_only = $end_date_obj->format('Y-m-d');
    
    if ($start_date_only === $end_date_only) {
        // Same date: check if times are the same
        $start_time = $start_date_obj->format('H:i');
        $end_time = $end_date_obj->format('H:i');
        if ($start_time === $end_time) {
            // Same time: show only start date and time
            $date_output_start = $start_date_obj->format('d/m/Y H:i');
            $date_output_end = '';
        } else {
            // Different times: show start date with start time and end time
            $date_output_start = $start_date_obj->format('d/m/Y H:i');
            $date_output_end = $end_date_obj->format('H:i');
        }
    } else {
        // Different dates: show start and end dates without times
        $date_output_start = $start_date_obj->format('d/m/Y');
        $date_output_end = $end_date_obj->format('d/m/Y');
    }
} elseif ($start_date_obj) {
    // Only start date available
    $date_output_start = $start_date_obj->format('d/m/Y H:i');
    $date_output_end = '';
} elseif ($end_date_obj) {
    // Only end date available (unlikely, but handled)
    $date_output_start = '';
    $date_output_end = $end_date_obj->format('d/m/Y H:i');
}
?>

<a href="<?php the_permalink(); ?>">
    <div class="eventItem-body">
		<?php if (has_post_thumbnail()) : ?>
			<div class="event-info">
				<img class='event-img' src="<?php echo get_the_post_thumbnail_url(); ?>" alt="<?php the_title_attribute(); ?>">
			</div>
		<?php else : ?>
			<div class="event-info">
				<img class='event-img' src="http://192.168.2.209:100/dpn-qkktgj/wp-content/uploads/2025/05/abbee1fc031c9af668accbbee24b70813b4fa426.png" alt="Default Event Image">
			</div>
		<?php endif; ?>
        <div class="event-description">
            <div class="event-textBody">
                <div class="event-title">
                    <?php the_title(); ?>
                </div>
                
                <?php if ($location) : ?>
                    <div class="event-location">
                        <span class="location"><?php echo $location; ?></span>
                    </div>
                <?php endif; ?>
                
                <div>
                    <span class="start-end-date">
                        <?php 
                            // start and optional end already calculated above
                            echo $date_output_start;
                            if ( $date_output_end ) {
                                echo ' - ' . $date_output_end;
                            }
                        ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</a>

