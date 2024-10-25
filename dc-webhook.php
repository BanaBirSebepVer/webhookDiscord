<?php
/*
* Plugin Name: Discord Webhook Integration
* Plugin URI:        https://example.com/plugins/the-basics/
* Description: Sends post notifications to Discord via webhooks based on categories.
* Version: 1.0
* Author: Can Bekcan
* Author URI: https://canbekcan.com
* License: GPL v2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Plugin'in aktivasyon ve deaktivasyon hook'ları
register_activation_hook(__FILE__, 'discord_webhook_activate');
register_deactivation_hook(__FILE__, 'discord_webhook_deactivate');

function discord_webhook_activate() {
    // Aktivasyon işlemleri, gerekirse burada yapılır
}

function discord_webhook_deactivate() {
    // Deaktivasyon işlemleri, gerekirse burada yapılır
}

// Fonksiyonlarımızı içeren dosyayı dahil edelim
require_once plugin_dir_path(__FILE__) . 'functions.php';
