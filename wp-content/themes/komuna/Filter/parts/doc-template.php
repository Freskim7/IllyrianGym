<?php
$ngarko = get_field('ngarko') ?? null;
$lloji  = get_field('lloji_i_dokumentit') ?? 'pdf';
$ngarko_final = get_field('ngarko_dokumentin') ?? null;
$data_doc = get_field('data_e_dokumentit') ?? null;

$data_doc_time = str_replace('/', '-', $data_doc);
$formatted_data_doc = strtotime($data_doc_time) ? date('d/m/Y', strtotime($data_doc_time)) : null;
$formatted_data_doc_final = $data_doc ? date('d/m/Y', strtotime($data_doc)) : null;
?>

<a target="_blank" href="<?php echo $ngarko ? $ngarko : ($ngarko_final ? $ngarko_final : get_the_permalink()) ?>">
    <div class="docItem-body">
         <div class="doc-image">
            <?php
			  if ($lloji == 'pdf') : ?>
				<img src="<?php echo get_stylesheet_directory_uri() . '/assets/images/pdf.png'; ?>" alt="PDF Icon">
			  <?php elseif ($lloji == 'doc'|| $lloji == 'docx') : ?>
				<img src="<?php echo get_stylesheet_directory_uri() . '/assets/images/docx.png'; ?>" alt="DOCX Icon">
			  <?php elseif ($lloji == 'xls' || $lloji == 'xlsx') : ?>
				<img src="<?php echo get_stylesheet_directory_uri() . '/assets/images/xls.png'; ?>" alt="XLS Icon">
			  <?php else : ?>
				<img src="<?php echo get_stylesheet_directory_uri() . '/assets/images/default.png'; ?>" alt="DEFAULT Icon">
			 <?php endif; ?>
        </div>
        <div class="doc-title">
            <?php the_title() ?>
        </div>
        <div class="doc-data">
            <?php echo $formatted_data_doc ?>
        </div>
    </div>
</a>
