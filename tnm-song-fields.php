<?php
/**
 * Plugin Name: TNM Song Fields
 * Description: Edit the choice lists of the song order form (Occasion, Themes, Style, Tempo, Vocal Type, Language, Story questions). Replaces the Forminator-based "Song Order Fields" form.
 * Version: 1.0.0
 * Requires PHP: 8.1
 * Text Domain: tnm-song-fields
 */

if (! defined('ABSPATH')) {
    exit;
}

define('TNM_SONG_FIELDS_VERSION', '1.0.0');
define('TNM_SONG_FIELDS_PATH', plugin_dir_path(__FILE__));
define('TNM_SONG_FIELDS_URL', plugin_dir_url(__FILE__));

require_once TNM_SONG_FIELDS_PATH . 'includes/class-tnm-song-fields.php';
require_once TNM_SONG_FIELDS_PATH . 'includes/functions.php';

// Old function names: declared only after all plugins are loaded (see includes/compat.php)
add_action('plugins_loaded', static function () {
    require_once TNM_SONG_FIELDS_PATH . 'includes/compat.php';
}, 1);

/**
 * File that declared tnm_get_occasion_choices() when it is NOT this plugin
 * (the old Forminator module is still loaded), otherwise ''.
 */
function tnm_song_fields_conflict_file(): string
{
    if (! function_exists('tnm_get_occasion_choices')) {
        return '';
    }
    $file = (string) (new ReflectionFunction('tnm_get_occasion_choices'))->getFileName();
    return strpos(wp_normalize_path($file), wp_normalize_path(TNM_SONG_FIELDS_PATH)) === 0 ? '' : $file;
}

if (is_admin()) {
    require_once TNM_SONG_FIELDS_PATH . 'admin/class-tnm-song-fields-admin.php';
    TNM_Song_Fields_Admin::init();
}
