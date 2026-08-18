<a href="<?php echo the_permalink() ?>">
    <div class="ItemsFilter-item item-<?php the_ID() ?>">
        <div class="ItemsFilter-item-image">
            <?php the_post_thumbnail('medium'); ?>
        </div>
        <div class="ItemsFilter-item-content">
            <h2 class="ItemsFilter-item-content-title"><?php the_title() ?></h2>
            <div class="ItemsFilter-item-content-excerpt">
                <?php the_excerpt(); ?>
            </div>
        </div>
    </div>
</a>