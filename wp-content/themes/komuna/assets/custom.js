/**

 * Custom JS code goes here, this file has the highest priority from other scripts

 * version: 1.0

 */


jQuery(document).ready(function ($) {

    // console.log($('.e-n-tab-title'));



    $('.e-n-tab-title').on('click tap', function (e) {

        if ($(this).has('a.navLink').length > 0) {

            e.stopPropagation();

            e.preventDefault();

            $(this).removeAttr('role');

            $(this).removeAttr('aria-controls');

            let link = $($(this).find('a.navLink')).attr('href');

            let target = $($(this).find('a.navLink')).attr('target');

            let el_id = $(this).attr('id');



            if (target === '_blank') {

                window.open(link, '_blank');

            } else {

                window.location.href = link;

            }

        }

    })

    //     $('.e-n-tab-title').has('a.navLink').each(function() {

    //         // console.log($(this));

    //         $(this).removeAttr('role');

    //         $(this).removeAttr('aria-controls');

    //         let link = $($(this).find('a.navLink')).attr('href');

    //         let target = $($(this).find('a.navLink')).attr('target');

    //         let el_id = $(this).attr('id');

    // 		console.log($(this));

    //         $(document).on('click', '#'+el_id, function (e){

    //             if (target === '_blank') {

    //                 window.open(link, '_blank');

    //             } else {

    //                 window.location.href = link;

    //             }

    //         });

    //         $(this).on('tap', function (e){

    //             console.log($(this));

    //             e.stopPropagation();

    //             e.preventDefault();

    //             if (target === '_blank') {

    //                 window.open(link, '_blank');

    //             } else {

    //                 window.location.href = link;

    //             }

    //         })

    //     });

});

(function ($) {
 	let previousTax = '';
	let previousTaxonomy = '';

    $(document).ready(function () {
        // Filter Items AJAX
	     $('#website-loader').hide();
        if ($('.ItemsFilter').length > 0 && $('.ItemsFilter').hasClass('ItemsFilter-Ajax')) {

            const template_id = $('.ItemsFilter').data('template') ?? 0;

            const pt = $('.ItemsFilter').data('pt') ?? 'post';

            const per_page = $('.ItemsFilter').data('perpage');

            const taxonomy = $('.ItemsFilter').data('taxonomy');

            const prefilter = $('.ItemsFilter').data('prefilter');
			

            const date_field = $('.ItemsFilter').data('datefield');

            const custom_tpl = $('.ItemsFilter').data('customtpl');
			
			const type = $('.ItemsFilter').data('type');
			
			const yearall = $('.ItemsFilter').data('yearall');

            let tax = '', page = 1, form_data, date_from, date_to;


 			const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('tax')) {
                tax = urlParams.get('tax');
                addParameter('tax', tax);
            }
			
            $('.ItemsFilter a.filter-item').click(function (e) {

                e.preventDefault();

                tax = $(this).data('slug');

                param = $(this).data('param');



                tax_param = '';

                if (tax == 'all') {

                    tax_param = '';

                } else {

                    tax_param = tax;

                }

                addParameter(param, tax_param);
				updateActiveClass(param, tax_param);

                // Reset form data on taxonomy change
                form_data = null;

                // Reset page on taxonomy change
                page = 1;

                getData();

            });



            $(document).on('click', '.ItemsFilter a.page-numbers', function (e) {

                e.preventDefault();

                if ($(this).text().replace(/\s+/g, '') == '>') {

                    page = Number($('.page-numbers.current').text()) + 1;

                } else if ($(this).text().replace(/\s+/g, '') == '<') {

                    page = Number($('.page-numbers.current').text()) - 1;

                } else {

                    page = Number($(this).text());

                }



                getData();

            });



            $('.ItemsFilter #searchform').submit(function (e) {

                e.preventDefault();

                let data = $(this).serializeArray();

                search_text = $('input[name="search_text"]').val();

                year = $('select[name="date_year"]').val()



                form_data = {

                    search_text,

                    date_year: year,

                };

                page = 1;

                getData();

            });



            $('.ItemsFilter .input-from').change(function (e) {

                date_from = e.target.value;

//                 getData();

            });

            $('.ItemsFilter .input-to').change(function (e) {

                date_to = e.target.value;

//                 getData();

            });

			
            function addParameter(param, tax) {
				const urlParams = new URLSearchParams(window.location.search);
				const currentTax = urlParams.get(param);

				if (currentTax !== tax) {
					urlParams.set(param, tax);
					const newUrl = '?' + urlParams.toString();

					window.history.pushState(null, null, newUrl);
// 					location.reload();
				}
			}

             function updateYears(years, selectedYear) {
                let year_select = $('.year-selectBody');
                let years_html = '';
    			years_html += '<option value="all">' + yearall + '</option>'; 
                years.forEach(year => {
                    years_html += '<option value="' + year + '"' + (year == selectedYear ? ' selected' : '') + '>' + year + '</option>';
                });

                year_select.html(years_html);
            }

            function getData() {
				$('#website-loader').show();
				$('.docItem-body').css('opacity', '0.5');
				$('.newsItem-body').css('opacity', '0.5'); 
				$('.eventItem-body').css('opacity', '0.5'); 
				$('.pubItem-body').css('opacity', '0.5'); 
				window.scrollTo(0, 0);
 				 const selectedYear = $('select[name="date_year"]').val(); 
                $.ajax({

                    type: 'POST',

                    url: '/dpn-agencytemplate/wp-admin/admin-ajax.php',

                    dataType: 'html',

                    data: {

                        action: 'items_filter_ajax',

                        tax,

                        page,

                        template_id,

                        custom_tpl,

                        form_data,

                        pt,

                        per_page,

                        taxonomy,

                        prefilter,

                        date_from,

                        date_to,

                        date_field,
						
						type

                    },



                    success: function (res) {

                        let parsed = JSON.parse(res);
                        $('#website-loader').hide();
           				$('.docItem-body').css('opacity', '1'); 
						$('.newsItem-body').css('opacity', '1'); 
						$('.eventItem-body').css('opacity', '1'); 
						$('.pubItem-body').css('opacity', '1'); 
						
                        $('.posts-wrapper-inner').html(parsed.data);

                        $('.dpn-pagination').html(parsed.pagination);

                       if (parsed.years) {
							const taxChanged = tax !== previousTax;
							const TaxonomyChanged = taxonomy !== previousTaxonomy;

							if (taxChanged || TaxonomyChanged) {
								if (taxChanged) {
									previousTax = tax;
								}

								if (TaxonomyChanged) {
									previousTaxonomy = taxonomy;
								}

								updateYears(parsed.years, selectedYear);
							}
						}

                        if (parsed.term) {

                            $('.ItemsFilter-title h1').html(parsed.term.name);

                        } else {

                            $('.ItemsFilter-title h1').html($('.ItemsFilter-title h1').data('page'));

                        }

                    }
// 					 complete: function() {
//                         $('.loader').hide();
//                     }

                })

            }

			function updateActiveClass(param_name, param_value) {
				if (param_name === 'tax') {
					if (param_value === '') {
						$('.taxonomies-list > .taxonomies-list-item:first-child > .filter-item').addClass('active');
						$('.taxonomies-list > .taxonomies-list-item:not(:first-child) > .filter-item').removeClass('active');
					} else {
						$('.filter-item').each(function () {
							$(this).removeClass('active');
							if ($(this).data('slug') === param_value) {
								$(this).addClass('active');
								let param_label = $(this).text();
								$('.ItemsFilter-title h1').text(param_label);
							}
						});
					}
				}
			}

        }
    })

})(jQuery);


jQuery(document).ready(function ($) {
    $('.sidebar-box .menu-item-has-children > a').on('click', function (e) {
        // Check if the sub-arrow is clicked
        if ($(e.target).closest('.sub-arrow').length) {
            e.preventDefault();
            e.stopPropagation();

            var $this = $(this);
            $this.next('.sub-menu').slideToggle();
            $this.closest('.menu-item-has-children').toggleClass('open');
        }
    });

    $('.sidebar-box .menu-item-has-children > a').on('click', function (e) {
        // Prevent the default action if the sub-arrow is clicked
        if ($(e.target).closest('.sub-arrow').length) {
            return false;
        }

        var $this = $(this);
        // If the sub-menu is visible, allow the default action
        if ($this.next('.sub-menu').is(':visible')) {
            return true;
        }

        e.preventDefault();
        // Open the dropdown
        $this.next('.sub-menu').slideDown();
        $this.closest('.menu-item-has-children').addClass('open');
        // Redirect to the href
        window.location.href = $this.attr('href');
    });

    // Open current menu items on page load
    if ($('.sidebar-box .current-menu-item, .sidebar-box .current-menu-parent').length) {
        $('.sidebar-box .current-menu-item, .sidebar-box .current-menu-parent')
            .closest('.menu-item-has-children')
            .addClass('open');
        $('.sidebar-box .current-menu-item, .sidebar-box .current-menu-parent')
            .find('.sub-menu')
            .show();
    }
});
