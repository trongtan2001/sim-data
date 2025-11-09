<?php

/**
 * @snippet Add "Primary Attribute" Field to Product Variations - WooCommerce
 * @description Thêm trường chọn attribute chính và lưu vào database
 */

// -----------------------------------------
// 1. Thêm field "Primary Attribute" vào từng variation

add_action( 'woocommerce_variation_options', 'add_primary_attribute_field_to_variations', 10, 3 );

function add_primary_attribute_field_to_variations( $loop, $variation_data, $variation ) {
    // Lấy tất cả attributes của variation này
    $variation_obj = wc_get_product( $variation->ID );
    $attributes = $variation_obj->get_variation_attributes();
    
    // Tạo options cho dropdown
    $options = array( '' => __( '-- Chọn attribute chính --', 'woocommerce' ) );
    
    foreach ( $attributes as $attr_name => $attr_value ) {
        // Lấy tên attribute (bỏ prefix "attribute_")
        $clean_attr_name = str_replace( 'attribute_', '', $attr_name );
        
        // Lấy label của attribute (VD: pa_thoi-han -> Thời hạn)
        $attr_label = wc_attribute_label( $clean_attr_name );
        
        // Tạo option: "Thời hạn: 1 ngày"
        $options[$attr_name] = $attr_label . ': ' . $attr_value;
    }
    
    // Hiển thị dropdown
    woocommerce_wp_select( array(
        'id'          => 'primary_attribute[' . $loop . ']',
        'class'       => 'short',
        'label'       => __( 'Attribute chính', 'woocommerce' ),
        'value'       => get_post_meta( $variation->ID, '_primary_attribute', true ),
        'options'     => $options,
        'desc_tip'    => true,
        'description' => __( 'Chọn attribute nào sẽ hiển thị đầu tiên', 'woocommerce' ),
    ) );
    
    echo '<br>';
}

// -----------------------------------------
// 2. Lưu dữ liệu "Primary Attribute" vào database

add_action( 'woocommerce_save_product_variation', 'save_primary_attribute_field_variations', 10, 2 );

function save_primary_attribute_field_variations( $variation_id, $i ) {
    // Kiểm tra xem có dữ liệu được gửi lên không
    if ( isset( $_POST['primary_attribute'][$i] ) ) {
        $primary_attribute = sanitize_text_field( $_POST['primary_attribute'][$i] );
        
        // Lưu vào database với meta_key là '_primary_attribute'
        update_post_meta( $variation_id, '_primary_attribute', $primary_attribute );
    }
}