<?php
// Kategori ekranına webhook URL alanları eklemek için kancalar
add_action('category_add_form_fields', 'add_discord_webhook_field', 10, 2);
add_action('category_edit_form_fields', 'edit_discord_webhook_field', 10, 2);
add_action('created_category', 'save_discord_webhook_url', 10, 2);
add_action('edited_category', 'save_discord_webhook_url', 10, 2);

// Yeni kategori formuna webhook URL alanı ekleme
function add_discord_webhook_field($taxonomy) {
    ?>
    <div class="form-field term-group">
        <label for="discord_webhook_url">Discord Webhook URL</label>
        <input type="text" id="discord_webhook_url" name="discord_webhook_url">
        <p class="description">Enter the Discord webhook URL for this category.</p>
    </div>
    <?php
}

// Kategori düzenleme formuna webhook URL alanı ekleme
function edit_discord_webhook_field($term, $taxonomy) {
    $webhook_url = get_term_meta($term->term_id, 'discord_webhook_url', true);
    ?>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label for="discord_webhook_url">Discord Webhook URL</label></th>
        <td>
            <input type="text" id="discord_webhook_url" name="discord_webhook_url" value="<?php echo esc_attr($webhook_url); ?>">
            <p class="description">Enter the Discord webhook URL for this category.</p>
        </td>
    </tr>
    <?php
}

// Kategoriye ait webhook URL'sini kaydetme
function save_discord_webhook_url($term_id) {
    if (isset($_POST['discord_webhook_url'])) {
        update_term_meta($term_id, 'discord_webhook_url', sanitize_text_field($_POST['discord_webhook_url']));
    }
}

// Yazı yayımlandığında webhook'a bildirim gönderme
add_action('publish_post', 'discord_webhook_send_notification');

function discord_webhook_send_notification($post_id) {
    $post = get_post($post_id);
    if (!$post) return;
    
    $categories = get_the_category($post_id);
    $featured_image = get_the_post_thumbnail_url($post_id, 'full');
    $publish_date = get_the_date('d.m.Y', $post_id); // Gün.Ay.Yıl formatında tarih
    $publish_time = get_the_time('H:i', $post_id); // Saat ve dakika
    $excerpt = wp_trim_words($post->post_content, 50, '...');

    foreach ($categories as $category) {
        $webhook_url = get_term_meta($category->term_id, 'discord_webhook_url', true);
        if (!empty($webhook_url)) {
            $payload = json_encode([
                'content' => '',
                'embeds' => [
                    [
                        'title' => get_the_title($post_id),
                        'url' => get_permalink($post_id),
                        'description' => $excerpt,
                        'image' => [
                            'url' => $featured_image
                        ],
                        'footer' => [
                            'text' => '📅 ' . $publish_date . '  ⏰ ' . $publish_time . ' | ' . get_the_author_meta('display_name', $post->post_author)
                        ]
                    ]
                ]
            ]);

            $ch = curl_init($webhook_url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $response = curl_exec($ch);
            curl_close($ch);
        }
    }
}


// Menü öğesi eklemek ve webhook sayfasını oluşturmak için fonksiyon
add_action('admin_menu', 'add_webhook_menu_page');

function add_webhook_menu_page() {
    add_menu_page(
        'Webhooks',               // Sayfa başlığı
        'Webhooks',               // Menü başlığı
        'manage_options',         // Gereken yetki
        'webhook-list',           // Menü slug'ı
        'webhook_list_page',      // Görüntüleme fonksiyonu
        'dashicons-rest-api',     // Menü ikonu (Dashicons içerisinden seçildi)
        6                         // Menü pozisyonu
    );
}

// Webhook listeleme sayfası
function webhook_list_page() {
    ?>
    <div class="wrap">
        <h1>Webhooks</h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col" id="category" class="manage-column" width="10%">Category</th>
                    <th scope="col" id="webhook" class="manage-column" width="80%">Webhook URL</th>
                    <th scope="col" id="count" class="manage-column" width="10%">Post Count</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $categories = get_categories(array('hide_empty' => 0));
                foreach ($categories as $category) {
                    $webhook_url = get_term_meta($category->term_id, 'discord_webhook_url', true);
                    echo '<tr>';
                    echo '<td>' . esc_html($category->name) . '</td>';
                    echo '<td>' . esc_html($webhook_url) . '</td>';
                    echo '<td>' . intval($category->count) . '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}
