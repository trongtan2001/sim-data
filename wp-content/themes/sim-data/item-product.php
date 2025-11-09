<?php

/**
 * Shortcode hiển thị TẤT CẢ variations của sản phẩm SIM Data
 * Usage: [sim_data_pricing] - không cần truyền gì cả
 * Hoặc: [sim_data_pricing category="goi-khong-gioi-han"] - filter theo category
 */

// Đăng ký shortcode
add_shortcode('sim_data_pricing', 'sim_data_pricing_shortcode');

function sim_data_pricing_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'category' => '', // Nếu muốn filter theo category
        'limit' => -1,    // Số lượng sản phẩm, -1 = tất cả
    ), $atts);

    // Query tất cả sản phẩm variable
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => $atts['limit'],
        'post_status' => 'publish',
        'tax_query' => array(
            array(
                'taxonomy' => 'product_type',
                'field' => 'slug',
                'terms' => 'variable',
            ),
        ),
    );

    // Nếu có filter category
    if (!empty($atts['category'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => $atts['category'],
        );
    }

    $products = new WP_Query($args);

    if (!$products->have_posts()) {
        return '<p>Không tìm thấy sản phẩm nào.</p>';
    }

    ob_start();
?>

    <div class="sim-data-pricing-wrapper">

        <?php
        // Duyệt qua từng sản phẩm
        while ($products->have_posts()) : $products->the_post();
            $product = wc_get_product(get_the_ID());

            if (!$product || !$product->is_type('variable')) {
                continue;
            }

            // Lấy danh sách biến thể
            $variations = $product->get_available_variations();
            if (empty($variations)) continue;

            // Lấy tên sản phẩm
            $product_name = $product->get_name();
        ?>
            <div class="variation-group product-group">
                <h3 class="group-title"><?php echo esc_html($product_name); ?></h3>
                <?php
                foreach ($variations as $variation) {
                    echo render_variation_item($variation, $product);
                }
                ?>
            </div>
        <?php
        endwhile;
        wp_reset_postdata();
        ?>
    </div>
    <!-- Nút chọn gói -->
    <div class="select-package-wrapper">
        <div class="selected-info" style="display: none">
            <span class="selected-name">Chọn số lượng eSIM</span>
            <div class="quantity-controls">
                <button type="button" class="qty-btn minus">-</button>
                <input type="number" class="qty-input" value="1" min="1" max="100">
                <button type="button" class="qty-btn plus">+</button>
            </div>
        </div>
        <button type="button" class="checkout-btn" disabled>
            <span class="btn-text">Chọn gói cước</span>
        </button>
    </div>

    <script>
        jQuery(document).ready(function($) {
            var selectedVariationId = null;
            var selectedProductId = null;
            var selectedQuantity = 1;

            // Click vào variation
            $('.variation-item').on('click', function() {
                $('.variation-item').removeClass('selected');
                $(this).addClass('selected');

                selectedVariationId = $(this).data('variation-id');
                selectedProductId = $(this).data('product-id');
                var variationName = $(this).find('.variation-title').text();
                var variationPrice = $(this).data('price');
                var variationUnitPrice = $(this).data('unit-price');
                var variationUnit = $(this).data('unit');

                // Hiển thị section chọn gói
                $('.selected-info').show();

                // Reset quantity về 1
                $('.qty-input').val(1);
                selectedQuantity = 1;

                $('.select-package-wrapper').addClass('active');

                // Cập nhật thông tin
                updateCheckoutButton(variationName, variationPrice, variationUnitPrice, variationUnit);
                
                // Enable checkout button
                $('.checkout-btn').prop('disabled', false);
            });

            // Quantity controls
            $('.qty-btn.minus').on('click', function(e) {
                e.stopPropagation();
                var $input = $('.qty-input');
                var val = parseInt($input.val());
                if (val > 1) {
                    $input.val(val - 1);
                    selectedQuantity = val - 1;
                    updateTotalPrice();
                }
            });

            $('.qty-btn.plus').on('click', function(e) {
                e.stopPropagation();
                var $input = $('.qty-input');
                var val = parseInt($input.val());
                var max = parseInt($input.attr('max'));
                if (val < max) {
                    $input.val(val + 1);
                    selectedQuantity = val + 1;
                    updateTotalPrice();
                }
            });

            $('.qty-input').on('change', function() {
                selectedQuantity = parseInt($(this).val());
                updateTotalPrice();
            });

            // Checkout button
            $('.checkout-btn').on('click', function() {

                if (!selectedVariationId || !selectedProductId) {
                    alert('Vui lòng chọn gói cước');
                    return;
                }

                // Hiển thị loading
                $(this).prop('disabled', true).html('Đang xử lý...');

                // Add to cart và chuyển đến checkout
                var data = {
                    action: 'add_variation_to_cart',
                    product_id: selectedProductId,
                    variation_id: selectedVariationId,
                    quantity: selectedQuantity
                };

                $.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
                    if (response.success) {
                        // Chuyển đến trang checkout
                        window.location.href = '<?php echo wc_get_checkout_url(); ?>';
                    } else {
                        alert('Có lỗi xảy ra. Vui lòng thử lại.');
                        $('.checkout-btn').prop('disabled', false).html('<span class="btn-text">Chọn gói cước</span>');
                    }
                }).fail(function() {
                    alert('Lỗi kết nối. Vui lòng thử lại.');
                    $('.checkout-btn').prop('disabled', false).html('<span class="btn-text">Chọn gói cước</span>');
                });
            });

            function updateCheckoutButton(name, price, unitPrice, unit) {
                var total = price * selectedQuantity;
                var formattedTotal = formatCurrency(total);

                $('.selected-name').html('Chọn số lượng eSIM');
                $('.btn-text').html('Thanh toán — ' + formattedTotal);

                // Hiển thị addon notice nếu có
                // if (unitPrice && unitPrice > 0 && unit) {
                //     var addonText = formatCurrency(unitPrice) + ' / ' + unit;
                //     if ($('.addon-notice').length === 0) {
                //         $('.selected-info').before('<div class="addon-notice">' + addonText + '</div>');
                //     } else {
                //         $('.addon-notice').html(addonText);
                //     }
                // } else {
                //     $('.addon-notice').remove();
                // }
            }

            function updateTotalPrice() {
                var $selected = $('.variation-item.selected');
                if ($selected.length > 0) {
                    var price = $selected.data('price');
                    var name = $selected.find('.variation-title').text();
                    var unitPrice = $selected.data('unit-price');
                    var unit = $selected.data('unit');
                    updateCheckoutButton(name, price, unitPrice, unit);
                }
            }

            function formatCurrency(amount) {
                return new Intl.NumberFormat('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                }).format(amount);
            }
        });
    </script>

<?php
    return ob_get_clean();
}

// Hàm render từng variation item
function render_variation_item($variation, $product)
{
    $variation_obj = wc_get_product($variation['variation_id']);

    if (!$variation_obj) {
        return '';
    }

    // ✅ Lấy primary attribute từ meta
    $primary_attribute = get_post_meta($variation['variation_id'], '_primary_attribute', true);

    // Lấy attributes
    $attributes = $variation['attributes'];
    
    // Khởi tạo biến
    $main_text = '';
    $sub_text = '';
    $unit = '';
    $main_value_numeric = 0;

    // ✅ Nếu có primary attribute
    if (!empty($primary_attribute) && isset($attributes[$primary_attribute])) {
        
        // ✅ Lấy VALUE và UNIT từ ACF của attribute chính
        // Cần lấy term_id của attribute value này
        $taxonomy = str_replace('attribute_', '', $primary_attribute);
        $term = get_term_by('slug', $attributes[$primary_attribute], $taxonomy);
        
        if ($term) {
            $main_text = $term->name;
            // Lấy ACF fields từ term
            $main_value_numeric = floatval(get_field('value_attribute', $taxonomy . '_' . $term->term_id));
            $unit = get_field('unit_attribute', $taxonomy . '_' . $term->term_id);
            
            // Nếu không có giá trị ACF, fallback về cách cũ
            if (!$main_value_numeric) {
                preg_match('/(\d+(?:\.\d+)?)/', $main_text, $matches);
                $main_value_numeric = isset($matches[1]) ? floatval($matches[1]) : 0;
            }
            if (!$unit) {
                $unit = 'GB'; // default
            }
        }
        
        // Lấy attribute còn lại làm sub text
        foreach ($attributes as $attr_key => $attr_value) {
            if ($attr_key !== $primary_attribute) {
                $sub_taxonomy = str_replace('attribute_', '', $attr_key);
                $sub_term = get_term_by('slug', $attr_value, $sub_taxonomy);
                
                if ($sub_term) {
                    $sub_text = $sub_term->name;
                } else {
                    $sub_text = $attr_value;
                }
                break;
            }
        }
    } else {
        // ✅ Fallback: Nếu không có primary attribute
        $duration = '';
        $capacity = '';

        foreach ($attributes as $attr_key => $attr_value) {
            $taxonomy = str_replace('attribute_', '', $attr_key);
            $term = get_term_by('slug', $attr_value, $taxonomy);
            $term_name = $term ? $term->name : $attr_value;
            
            if (stripos($attr_key, 'thoi-han') !== false ||
                stripos($attr_key, 'duration') !== false) {
                $duration = $term_name;
            }
            if (stripos($attr_key, 'dung-luong') !== false ||
                stripos($attr_key, 'capacity') !== false) {
                $capacity = $term_name;
                
                // Lấy ACF
                if ($term) {
                    $main_value_numeric = floatval(get_field('value_attribute', $taxonomy . '_' . $term->term_id));
                    $unit = get_field('unit_attribute', $taxonomy . '_' . $term->term_id);
                }
            }
        }

        $main_text = $capacity ? $capacity : 'N/A';
        $sub_text = $duration ? $duration : 'N/A';
        
        if (!$unit) {
            $unit = 'GB';
        }
    }

    // Giá
    $regular_price = $variation_obj->get_regular_price();
    $sale_price = $variation_obj->get_sale_price();
    $price = $variation_obj->get_price();

    // ✅ Tính giá trên đơn vị: Giá / Value của attribute chính
    $unit_price = 0;
    if ($main_value_numeric > 0 && $price > 0) {
        $unit_price = round($price / $main_value_numeric);
    }

    // Tính % giảm giá
    $discount_percent = 0;
    if ($regular_price && $sale_price && $regular_price > 0) {
        $discount_percent = round((($regular_price - $sale_price) / $regular_price) * 100);
    }

    // ✅ Lấy product_id từ variation
    $product_id = $variation_obj->get_parent_id();

    ob_start();
?>
    <div class="variation-item"
        data-variation-id="<?php echo esc_attr($variation['variation_id']); ?>"
        data-product-id="<?php echo esc_attr($product_id); ?>"
        data-price="<?php echo esc_attr($price); ?>"
        data-unit-price="<?php echo esc_attr($unit_price); ?>"
        data-unit="<?php echo esc_attr($unit); ?>">

        <div class="variation-content">
            <div class="variation-left">
                <div class="variation-title"><?php echo esc_html($main_text); ?></div>
                <div class="variation-subtitle"><?php echo esc_html($sub_text); ?></div>
            </div>
            <div class="variation-center">
                <div class="variation-price">
                    <?php echo wc_price($price); ?>
                    <?php if ($regular_price && $sale_price && $regular_price > $sale_price): ?>
                        <span class="variation-regular-price"><?php echo wc_price($regular_price); ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($unit_price > 0 && !empty($unit)): ?>
                    <div class="variation-unit-price">
                        <?php echo wc_price($unit_price); ?> / <?php echo esc_html($unit); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="variation-right">
                 <?php if ($discount_percent > 0): ?>
                    <div class="discount-badge">-<?php echo $discount_percent; ?>%</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}

// AJAX handler để add to cart
add_action('wp_ajax_add_variation_to_cart', 'add_variation_to_cart_handler');
add_action('wp_ajax_nopriv_add_variation_to_cart', 'add_variation_to_cart_handler');

function add_variation_to_cart_handler()
{
    $product_id = intval($_POST['product_id']);
    $variation_id = intval($_POST['variation_id']);
    $quantity = intval($_POST['quantity']);

    if (!$product_id || !$variation_id || $quantity < 1) {
        wp_send_json_error(array('message' => 'Dữ liệu không hợp lệ'));
        return;
    }

    // Xóa cart cũ (nếu muốn chỉ có 1 item)
    WC()->cart->empty_cart();

    // Add variation to cart
    $result = WC()->cart->add_to_cart($product_id, $quantity, $variation_id);

    if ($result) {
        wp_send_json_success(array('cart_url' => wc_get_checkout_url()));
    } else {
        wp_send_json_error(array('message' => 'Không thể thêm sản phẩm vào giỏ hàng'));
    }
}
