<?php

/**
 * Shortcode tìm kiếm FAQ
 * Sử dụng: [faq_search taxonomy="category" placeholder="Search..."]
 */
function faq_search_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'taxonomy' => 'category',
        'placeholder' => 'Tìm kiếm hỗ trợ...',
        'posts_per_category' => -1,
        'hide_empty' => true,
    ), $atts);

    // Lấy tất cả categories và posts một lần
    $categories = get_terms(array(
        'taxonomy' => $atts['taxonomy'],
        'hide_empty' => $atts['hide_empty'],
        'orderby' => 'name',
        'order' => 'ASC',
    ));

    $all_posts_data = array();

    if (!empty($categories) && !is_wp_error($categories)) {
        foreach ($categories as $category) {
            $posts = get_posts(array(
                'post_type' => 'post',
                'posts_per_page' => $atts['posts_per_category'],
                'tax_query' => array(
                    array(
                        'taxonomy' => $atts['taxonomy'],
                        'field' => 'term_id',
                        'terms' => $category->term_id,
                    ),
                ),
                'orderby' => 'menu_order title',
                'order' => 'ASC',
            ));

            foreach ($posts as $post) {
                $all_posts_data[] = array(
                    'id' => $post->ID,
                    'title' => $post->post_title,
                    'excerpt' => wp_trim_words(strip_tags($post->post_content), 30, '...'),
                    'url' => get_permalink($post->ID),
                    'category' => $category->name,
                    'category_url' => get_term_link($category),
                );
            }
        }
    }

    // Chuyển data sang JSON để JavaScript sử dụng
    $posts_json = json_encode($all_posts_data);
    $unique_id = 'faq_search_' . uniqid();

    ob_start();
?>

    <div class="faq-search-wrapper">
        <div class="faq-search-container">
            <input
                type="text"
                class="faq-search-input"
                id="<?php echo esc_attr($unique_id); ?>"
                placeholder="<?php echo esc_attr($atts['placeholder']); ?>"
                autocomplete="off">
            <button class="faq-search-button" type="button" aria-label="Search">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" />
                </svg>
            </button>
        </div>

        <div class="faq-search-results" id="<?php echo esc_attr($unique_id); ?>_results">
            <div class="faq-search-header"></div>
            <div class="faq-search-results-list"></div>
            <div class="faq-show-all"></div>
        </div>
    </div>

    <script>
        (function() {
            const searchInput = document.getElementById('<?php echo esc_js($unique_id); ?>');
            const resultsContainer = document.getElementById('<?php echo esc_js($unique_id); ?>_results');
            const resultsHeader = resultsContainer.querySelector('.faq-search-header');
            const resultsList = resultsContainer.querySelector('.faq-search-results-list');
            const showAllContainer = resultsContainer.querySelector('.faq-show-all');
            const allPosts = <?php echo $posts_json; ?>;

            let displayLimit = 5;
            let currentResults = [];

            function highlightText(text, query) {
                if (!query) return text;
                const regex = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
                return text.replace(regex, '<span class="faq-result-highlight">$1</span>');
            }

            function searchPosts(query) {
                if (!query || query.length < 2) {
                    resultsContainer.classList.remove('active');
                    return;
                }

                const lowerQuery = query.toLowerCase();
                const filtered = allPosts.filter(post =>
                    post.title.toLowerCase().includes(lowerQuery) ||
                    post.excerpt.toLowerCase().includes(lowerQuery) ||
                    post.category.toLowerCase().includes(lowerQuery)
                );

                currentResults = filtered;
                displayResults(filtered, query, displayLimit);
            }

            function displayResults(results, query, limit) {
                if (results.length === 0) {
                    resultsHeader.textContent = 'No results found';
                    resultsList.innerHTML = '<div class="faq-no-results">Try searching with different keywords</div>';
                    showAllContainer.innerHTML = '';
                    resultsContainer.classList.add('active');
                    return;
                }

                resultsHeader.textContent = results.length === 1 ? '1 result found' : results.length + ' results found';

                const displayedResults = results.slice(0, limit);

                resultsList.innerHTML = displayedResults.map(post => `
                <div class="faq-search-result-item">
                    <a href="${post.category_url}" class="faq-result-category">${post.category}</a>
                    <div class="faq-result-title">
                        <a href="${post.url}">${highlightText(post.title, query)}</a>
                    </div>
                    <div class="faq-result-excerpt">${highlightText(post.excerpt, query)}</div>
                </div>
            `).join('');

                if (results.length > limit) {
                    showAllContainer.innerHTML = `
                    <button class="faq-show-all-button" onclick="this.parentElement.parentElement.dataset.showAll='true'; 
                        document.getElementById('<?php echo esc_js($unique_id); ?>').dispatchEvent(new Event('input'));">
                        Show all ${results.length} results
                    </button>
                `;
                } else {
                    showAllContainer.innerHTML = '';
                }

                resultsContainer.classList.add('active');
            }

            searchInput.addEventListener('input', function(e) {
                const showAll = resultsContainer.dataset.showAll === 'true';
                const limit = showAll ? 999 : displayLimit;
                searchPosts(e.target.value, limit);
            });

            searchInput.addEventListener('focus', function(e) {
                if (e.target.value.length >= 2) {
                    resultsContainer.classList.add('active');
                }
            });

            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
                    resultsContainer.classList.remove('active');
                    resultsContainer.dataset.showAll = 'false';
                }
            });

            const searchButton = searchInput.nextElementSibling;
            searchButton.addEventListener('click', function() {
                searchInput.focus();
                if (searchInput.value.length >= 2) {
                    resultsContainer.classList.add('active');
                }
            });
        })();
    </script>

<?php
    return ob_get_clean();
}
add_shortcode('faq_search', 'faq_search_shortcode');

function faq_categories_shortcode($atts)
{
    // Thiết lập các tham số mặc định
    $atts = shortcode_atts(array(
        'taxonomy' => 'category',
        'posts_per_category' => -1,
        'hide_empty' => true,
        'orderby' => 'name',
        'order' => 'ASC',
        'exclude' => '', // ID categories muốn loại trừ, cách nhau bởi dấu phẩy
    ), $atts);

    // Lấy danh sách categories
    $categories = get_terms(array(
        'taxonomy' => $atts['taxonomy'],
        'hide_empty' => $atts['hide_empty'],
        'orderby' => $atts['orderby'],
        'order' => $atts['order'],
        'exclude' => $atts['exclude'],
    ));

    if (empty($categories) || is_wp_error($categories)) {
        return '<p>Không có danh mục nào.</p>';
    }

    ob_start();
?>

    <div class="faq-categories-grid">
        <?php
        $card_index = 0;
        foreach ($categories as $category) :
            // Lấy icon từ ACF
            $icon = get_field('icon_category', $category);

            // Lấy posts trong category này
            $posts = get_posts(array(
                'post_type' => 'post',
                'posts_per_page' => $atts['posts_per_category'],
                'tax_query' => array(
                    array(
                        'taxonomy' => $atts['taxonomy'],
                        'field' => 'term_id',
                        'terms' => $category->term_id,
                    ),
                ),
                'orderby' => 'menu_order title',
                'order' => 'ASC',
            ));

            // URL archive của category
            $category_url = get_term_link($category);

            $card_index++;
        ?>

            <div class="faq-category-card">
                <?php if ($icon) : ?>
                    <div class="faq-category-icon">
                        <?php if (is_array($icon)) : ?>
                            <img src="<?php echo esc_url($icon['url']); ?>" alt="<?php echo esc_attr($category->name); ?>">
                        <?php else : ?>
                            <img src="<?php echo esc_url($icon); ?>" alt="<?php echo esc_attr($category->name); ?>">
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <h3 class="faq-category-title">
                    <a href="<?php echo esc_url($category_url); ?>">
                        <?php echo esc_html($category->name); ?>
                    </a>
                </h3>

                <?php if (!empty($posts)) : ?>
                    <ul class="faq-posts-list">
                        <?php foreach ($posts as $post) : ?>
                            <li>
                                <a href="<?php echo esc_url(get_permalink($post->ID)); ?>">
                                    <?php echo esc_html($post->post_title); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <a href="<?php echo esc_url($category_url); ?>" class="faq-category-arrow" aria-label="Xem tất cả <?php echo esc_attr($category->name); ?>">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z" />
                    </svg>
                </a>
            </div>

        <?php endforeach; ?>
    </div>

<?php
    return ob_get_clean();
}
add_shortcode('faq_categories', 'faq_categories_shortcode');
