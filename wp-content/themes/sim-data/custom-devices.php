<?php
// ============================================
// HÀM CHUNG: Load tất cả dữ liệu thiết bị
// ============================================
function get_all_devices_data_shared() {
    static $cached_data = null;
    
    if ($cached_data !== null) {
        return $cached_data;
    }
    
    $data = array();
    
    // Query tất cả parent categories
    $parent_companies = get_terms(array(
        'taxonomy' => 'company-devices',
        'hide_empty' => true,
        'parent' => 0,
    ));
    
    if (!empty($parent_companies) && !is_wp_error($parent_companies)) {
        foreach ($parent_companies as $parent) {
            // Lấy logo của parent
            $parent_logo = get_field('logo_company_device', 'company-devices_' . $parent->term_id);
            
            // Lấy các child categories
            $child_categories = get_terms(array(
                'taxonomy' => 'company-devices',
                'hide_empty' => true,
                'parent' => $parent->term_id,
            ));
            
            if (empty($child_categories) || is_wp_error($child_categories)) {
                continue;
            }
            
            foreach ($child_categories as $child) {
                // Lấy logo từ child category
                $child_logo = get_field('logo_company_device', 'company-devices_' . $child->term_id);
                $logo = $child_logo ? $child_logo : $parent_logo;
                
                // Query các thiết bị thuộc child category
                $devices_args = array(
                    'post_type' => 'compatible-devices',
                    'posts_per_page' => -1,
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'company-devices',
                            'field' => 'term_id',
                            'terms' => $child->term_id,
                        ),
                    ),
                    'orderby' => 'title',
                    'order' => 'ASC',
                );
                
                $devices_query = new WP_Query($devices_args);
                
                if (!$devices_query->have_posts()) {
                    continue;
                }
                
                $devices_array = array();
                while ($devices_query->have_posts()) {
                    $devices_query->the_post();
                    $devices_array[] = array(
                        'title' => get_the_title(),
                        'id' => get_the_ID()
                    );
                }
                wp_reset_postdata();
                
                $data[$child->name] = array(
                    'parent_name' => $parent->name,
                    'parent_slug' => $parent->slug,
                    'child_slug' => $child->slug,
                    'logo' => is_array($logo) ? $logo['url'] : $logo,
                    'description' => $child->description,
                    'devices' => $devices_array
                );
            }
        }
    }
    
    $cached_data = $data;
    return $data;
}

// ============================================
// SHORTCODE 1: Chỉ có Search Box
// Sử dụng: [device_search_box]
// ============================================
function device_search_box_shortcode($atts) {
    $atts = shortcode_atts(array(
        'placeholder' => 'Tìm kiếm thiết bị',
        'target' => 'all', // 'all', 'grid', 'accordion'
    ), $atts);
    
    $all_devices_data = get_all_devices_data_shared();
    
    ob_start();
    ?>
    <div class="device-search-box-wrapper">
        <div class="device-search-form">
            <div class="search-input-wrapper">
                <svg class="search-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M9 17A8 8 0 1 0 9 1a8 8 0 0 0 0 16zM19 19l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <input 
                    type="text" 
                    class="device-search-input" 
                    data-target="<?php echo esc_attr($atts['target']); ?>"
                    placeholder="<?php echo esc_attr($atts['placeholder']); ?>"
                    autocomplete="off"
                />
                <button type="button" class="search-clear" style="display: none;">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M12 4L4 12M4 4l8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
            <div class="search-results-dropdown" style="display: none;"></div>
        </div>
    </div>
    
    <script>
    if (typeof window.allDevicesDataShared === 'undefined') {
        window.allDevicesDataShared = <?php echo json_encode($all_devices_data); ?>;
    }
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('device_search_box', 'device_search_box_shortcode');

// ============================================
// SHORTCODE 2: Hiển thị dạng Accordion (có collapse)
// Sử dụng: [devices_accordion]
// ============================================
function devices_accordion_shortcode($atts) {
    $atts = shortcode_atts(array(
        'show_tabs' => 'true',
    ), $atts);
    
    $all_devices_data = get_all_devices_data_shared();
    
    // Nhóm theo parent
    $grouped_by_parent = array();
    foreach ($all_devices_data as $child_name => $data) {
        $parent = $data['parent_slug'];
        if (!isset($grouped_by_parent[$parent])) {
            $grouped_by_parent[$parent] = array(
                'name' => $data['parent_name'],
                'children' => array()
            );
        }
        $grouped_by_parent[$parent]['children'][$child_name] = $data;
    }
    
    ob_start();
    ?>
    <div class="devices-accordion-wrapper" data-type="accordion">
        <?php if ($atts['show_tabs'] === 'true'): ?>
        <div class="device-tabs">
            <?php 
            $first = true;
            foreach ($grouped_by_parent as $slug => $parent_data): 
            ?>
            <button class="device-tab <?php echo $first ? 'active' : ''; ?>" data-parent="<?php echo esc_attr($slug); ?>">
                <?php echo esc_html($parent_data['name']); ?>
            </button>
            <?php 
            $first = false;
            endforeach; 
            ?>
        </div>
        <?php endif; ?>
        
        <div class="accordion-list">
            <?php foreach ($grouped_by_parent as $parent_slug => $parent_data): ?>
            <div class="accordion-parent-group" data-parent="<?php echo esc_attr($parent_slug); ?>">
                <?php foreach ($parent_data['children'] as $child_name => $child_data): ?>
                <div class="accordion-item" data-child="<?php echo esc_attr($child_data['child_slug']); ?>">
                    <div class="accordion-header">
                        <span class="accordion-title"><?php echo esc_html($child_name); ?></span>
                        <svg class="accordion-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-subtitle"><?php echo esc_html($child_data['description'] ?: 'Điện thoại'); ?></div>
                        <ul class="accordion-devices-list">
                            <?php foreach ($child_data['devices'] as $device): ?>
                            <li>
                                <svg class="checkmark-icon" width="18" height="18" viewBox="0 0 18 18" fill="currentColor">
                                    <path d="M15 4.5L6.75 13.5 3 9.75" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                                </svg>
                                <span><?php echo esc_html($device['title']); ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
    (function($) {
        $(document).ready(function() {
            // ===== XỬ LÝ SEARCH BOX =====
            $('.device-search-input').each(function() {
                var searchInput = $(this);
                var searchForm = searchInput.closest('.device-search-form');
                var searchResults = searchForm.find('.search-results-dropdown');
                var clearBtn = searchForm.find('.search-clear');
                var target = searchInput.data('target');
                var searchTimeout;

                searchInput.on('input', function() {
                    clearTimeout(searchTimeout);
                    var query = $(this).val().trim().toLowerCase();
                    
                    if (query.length > 0) {
                        clearBtn.attr("style", "display: flex !important;");
                    } else {
                        clearBtn.hide();
                        searchResults.hide();
                        resetDisplay(target);
                        return;
                    }
                    
                    if (query.length < 2) {
                        searchResults.hide();
                        return;
                    }
                    
                    searchTimeout = setTimeout(function() {
                        performSearch(query, searchResults, target);
                    }, 200);
                });
                
                clearBtn.on('click', function() {
                    searchInput.val('');
                    clearBtn.hide();
                    searchResults.hide();
                    resetDisplay(target);
                });
            });
            
            // Click vào kết quả dropdown
            // $(document).on('click', '.search-result-item', function() {
            //     var deviceName = $(this).data('device-name');
            //     var searchForm = $(this).closest('.device-search-form');
            //     var searchInput = searchForm.find('.device-search-input');
            //     var target = searchInput.data('target');
                
            //     searchInput.val(deviceName);
            //     searchForm.find('.search-results-dropdown').hide();
            //     filterDisplay(deviceName.toLowerCase(), target);
            // });
            
            // Đóng dropdown khi click bên ngoài
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.device-search-form').length) {
                    $('.search-results-dropdown').hide();
                }
            });
            
            function performSearch(query, resultsContainer, target) {
                var results = {};
                var hasResults = false;
                
                $.each(window.allDevicesDataShared, function(category, categoryData) {
                    var matchedDevices = [];
                    
                    $.each(categoryData.devices, function(index, device) {
                        if (device.title.toLowerCase().indexOf(query) !== -1) {
                            matchedDevices.push(device);
                            hasResults = true;
                        }
                    });
                    
                    if (matchedDevices.length > 0) {
                        results[category] = {
                            parent_name: categoryData.parent_name,
                            devices: matchedDevices
                        };
                    }
                });
                
                if (hasResults) {
                    var html = buildDropdownHtml(results);
                    resultsContainer.html(html).show();
                } else {
                    resultsContainer.html('<div class="no-results">Không tìm thấy kết quả</div>').show();
                }
            }
            
            function buildDropdownHtml(results) {
                var html = '';
                $.each(results, function(category, data) {
                    html += '<div class="search-category-group">';
                    html += '<div class="search-category-header">' + escapeHtml(category) + '</div>';
                    html += '<div class="search-category-subtitle">' + escapeHtml(data.parent_name) + '</div>';
                    
                    $.each(data.devices, function(index, device) {
                        html += '<div class="search-result-item" data-device-name="' + escapeHtml(device.title) + '">';
                        html += '<svg class="checkmark-icon" width="16" height="16" viewBox="0 0 16 16" fill="currentColor">';
                        html += '<path d="M13.5 4.5L6 12l-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>';
                        html += '</svg>';
                        html += '<span class="device-name">' + escapeHtml(device.title) + '</span>';
                        html += '</div>';
                    });
                    html += '</div>';
                });
                return html;
            }
            
            function filterDisplay(query, target) {
                var targets = target === 'all' ? ['accordion', 'grid'] : [target];
                
                $.each(targets, function(i, t) {
                    var containers = $('[data-type="' + t + '"]');
                    containers.each(function() {
                        var container = $(this);
                        var items = t === 'accordion' ? container.find('.accordion-item') : container.find('.company-section');
                        var hasVisible = false;
                        
                        items.each(function() {
                            var item = $(this);
                            var hasMatch = false;
                            
                            item.find('li').each(function() {
                                var text = $(this).text().toLowerCase();
                                if (text.indexOf(query) !== -1) {
                                    hasMatch = true;
                                    return false;
                                }
                            });
                            
                            if (hasMatch) {
                                item.show();
                                if (t === 'accordion') {
                                    item.find('.accordion-content').slideDown();
                                }
                                hasVisible = true;
                            } else {
                                item.hide();
                            }
                        });
                    });
                });
            }
            
            function resetDisplay(target) {
                var targets = target === 'all' ? ['accordion', 'grid'] : [target];
                
                $.each(targets, function(i, t) {
                    var containers = $('[data-type="' + t + '"]');
                    containers.each(function() {
                        var container = $(this);
                        var items = t === 'accordion' ? container.find('.accordion-item') : container.find('.company-section');
                        items.show();
                        if (t === 'accordion') {
                            container.find('.accordion-content').slideUp();
                        }
                    });
                });
            }
            
            function escapeHtml(text) {
                var map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
                return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
            }
            
            // ===== XỬ LÝ TABS =====
            $('.device-tab').on('click', function() {
                var parent = $(this).data('parent');
                var wrapper = $(this).closest('.devices-accordion-wrapper');
                
                $('.device-tab').removeClass('active');
                $(this).addClass('active');
                
                wrapper.find('.accordion-parent-group').hide();
                wrapper.find('[data-parent="' + parent + '"]').show();
            });
            
            // Ẩn các group không active ban đầu
            $('.accordion-parent-group').hide();
            $('.accordion-parent-group').first().show();
            
            // ===== XỬ LÝ ACCORDION =====
            $(document).off('click', '.accordion-header').on('click', '.accordion-header', function() {
                var item = $(this).closest('.accordion-item');
                var content = item.find('.accordion-content');
                var icon = $(this).find('.accordion-icon');
                
                if (content.is(':visible')) {
                    content.slideUp();
                    icon.removeClass('rotated');
                } else {
                    content.slideDown();
                    icon.addClass('rotated');
                }
            });
        });
    })(jQuery);
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('devices_accordion', 'devices_accordion_shortcode');

// ============================================
// SHORTCODE 3: Hiển thị dạng Grid (không collapse)
// Sử dụng: [devices_grid]
// ============================================
function devices_grid_shortcode($atts) {
    $all_devices_data = get_all_devices_data_shared();
    
    ob_start();
    ?>
    <div class="devices-grid-wrapper" data-type="grid">
        <div class="devices-by-company-wrapper">
            <?php foreach ($all_devices_data as $child_name => $data): ?>
            <div class="company-section" data-child="<?php echo esc_attr($data['child_slug']); ?>">
                <div class="company-header">
                    <div class="company-header-left">
                        <h2 class="company-name"><?php echo esc_html($child_name); ?></h2>
                        <?php if (!empty($data['description'])): ?>
                        <div class="company-description"><?php echo esc_html($data['description']); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php if ($data['logo']): ?>
                    <img src="<?php echo esc_url($data['logo']); ?>" alt="<?php echo esc_attr($child_name); ?>" class="company-logo" />
                    <?php endif; ?>
                </div>
                <ul class="devices-list">
                    <?php foreach ($data['devices'] as $device): ?>
                    <li><?php echo esc_html($device['title']); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
    (function($) {
        $(document).ready(function() {
            // ===== XỬ LÝ SEARCH BOX =====
            $('.device-search-input').each(function() {
                var searchInput = $(this);
                var searchForm = searchInput.closest('.device-search-form');
                var searchResults = searchForm.find('.search-results-dropdown');
                var clearBtn = searchForm.find('.search-clear');
                var target = searchInput.data('target');
                var searchTimeout;

                searchInput.on('input', function() {
                    clearTimeout(searchTimeout);
                    var query = $(this).val().trim().toLowerCase();
                    
                    if (query.length > 0) {
                        clearBtn.attr("style", "display: flex !important;");
                    } else {
                        clearBtn.hide();
                        searchResults.hide();
                        resetDisplay(target);
                        return;
                    }
                    
                    if (query.length < 2) {
                        searchResults.hide();
                        return;
                    }
                    
                    searchTimeout = setTimeout(function() {
                        performSearch(query, searchResults, target);
                    }, 200);
                });
                
                clearBtn.on('click', function() {
                    searchInput.val('');
                    clearBtn.hide();
                    searchResults.hide();
                    resetDisplay(target);
                });
            });
            
            // Click vào kết quả dropdown
            // $(document).on('click', '.search-result-item', function() {
            //     var deviceName = $(this).data('device-name');
            //     var searchForm = $(this).closest('.device-search-form');
            //     var searchInput = searchForm.find('.device-search-input');
            //     var target = searchInput.data('target');
                
            //     searchInput.val(deviceName);
            //     searchForm.find('.search-results-dropdown').hide();
            //     filterDisplay(deviceName.toLowerCase(), target);
            // });
            
            // Đóng dropdown khi click bên ngoài
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.device-search-form').length) {
                    $('.search-results-dropdown').hide();
                }
            });
            
            function performSearch(query, resultsContainer, target) {
                var results = {};
                var hasResults = false;
                
                $.each(window.allDevicesDataShared, function(category, categoryData) {
                    var matchedDevices = [];
                    
                    $.each(categoryData.devices, function(index, device) {
                        if (device.title.toLowerCase().indexOf(query) !== -1) {
                            matchedDevices.push(device);
                            hasResults = true;
                        }
                    });
                    
                    if (matchedDevices.length > 0) {
                        results[category] = {
                            parent_name: categoryData.parent_name,
                            devices: matchedDevices
                        };
                    }
                });
                
                if (hasResults) {
                    var html = buildDropdownHtml(results);
                    resultsContainer.html(html).show();
                } else {
                    resultsContainer.html('<div class="no-results">Không tìm thấy kết quả</div>').show();
                }
            }
            
            function buildDropdownHtml(results) {
                var html = '';
                $.each(results, function(category, data) {
                    html += '<div class="search-category-group">';
                    html += '<div class="search-category-header">' + escapeHtml(category) + '</div>';
                    html += '<div class="search-category-subtitle">' + escapeHtml(data.parent_name) + '</div>';
                    
                    $.each(data.devices, function(index, device) {
                        html += '<div class="search-result-item" data-device-name="' + escapeHtml(device.title) + '">';
                        html += '<svg class="checkmark-icon" width="16" height="16" viewBox="0 0 16 16" fill="currentColor">';
                        html += '<path d="M13.5 4.5L6 12l-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>';
                        html += '</svg>';
                        html += '<span class="device-name">' + escapeHtml(device.title) + '</span>';
                        html += '</div>';
                    });
                    html += '</div>';
                });
                return html;
            }
            
            function filterDisplay(query, target) {
                var targets = target === 'all' ? ['accordion', 'grid'] : [target];
                
                $.each(targets, function(i, t) {
                    var containers = $('[data-type="' + t + '"]');
                    containers.each(function() {
                        var container = $(this);
                        var items = t === 'accordion' ? container.find('.accordion-item') : container.find('.company-section');
                        var hasVisible = false;
                        
                        items.each(function() {
                            var item = $(this);
                            var hasMatch = false;
                            
                            item.find('li').each(function() {
                                var text = $(this).text().toLowerCase();
                                if (text.indexOf(query) !== -1) {
                                    hasMatch = true;
                                    return false;
                                }
                            });
                            
                            if (hasMatch) {
                                item.show();
                                if (t === 'accordion') {
                                    item.find('.accordion-content').slideDown();
                                }
                                hasVisible = true;
                            } else {
                                item.hide();
                            }
                        });
                    });
                });
            }
            
            function resetDisplay(target) {
                var targets = target === 'all' ? ['accordion', 'grid'] : [target];
                
                $.each(targets, function(i, t) {
                    var containers = $('[data-type="' + t + '"]');
                    containers.each(function() {
                        var container = $(this);
                        var items = t === 'accordion' ? container.find('.accordion-item') : container.find('.company-section');
                        items.show();
                        if (t === 'accordion') {
                            container.find('.accordion-content').slideUp();
                        }
                    });
                });
            }
            
            function escapeHtml(text) {
                var map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
                return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
            }
        });
    })(jQuery);
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('devices_grid', 'devices_grid_shortcode');


