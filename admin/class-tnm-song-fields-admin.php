<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Admin screen: Song Orders → Form choices.
 */
final class TNM_Song_Fields_Admin
{
    private const SLUG  = 'tnm-song-fields';
    private const NONCE = 'tnm_song_fields_save';

    private static string $hook = '';

    public static function init(): void
    {
        add_action('admin_menu', array(__CLASS__, 'menu'), 20);
        add_action('admin_enqueue_scripts', array(__CLASS__, 'assets'));
    }

    public static function capability(): string
    {
        $cap = class_exists('WooCommerce') ? 'manage_woocommerce' : 'manage_options';
        return (string) apply_filters('tnm_song_fields_capability', $cap);
    }

    public static function menu(): void
    {
        global $admin_page_hooks;
        $title = __('Form choices', 'tnm-song-fields');

        // Under "Song Orders" when the CRM is active, otherwise its own menu
        self::$hook = isset($admin_page_hooks['tnm-song-orders'])
            ? (string) add_submenu_page('tnm-song-orders', $title, $title, self::capability(), self::SLUG, array(__CLASS__, 'render'))
            : (string) add_menu_page($title, __('Song form', 'tnm-song-fields'), self::capability(), self::SLUG, array(__CLASS__, 'render'), 'dashicons-feedback', 59);

        add_action('load-' . self::$hook, array(__CLASS__, 'handle_post'));
    }

    public static function assets(string $hook): void
    {
        if ($hook !== self::$hook) {
            return;
        }
        $css = TNM_SONG_FIELDS_PATH . 'assets/admin.css';
        $js  = TNM_SONG_FIELDS_PATH . 'assets/admin.js';
        wp_enqueue_style('tnm-song-fields', TNM_SONG_FIELDS_URL . 'assets/admin.css', array(), (string) filemtime($css));
        wp_enqueue_script('tnm-song-fields', TNM_SONG_FIELDS_URL . 'assets/admin.js', array(), (string) filemtime($js), true);
    }

    private static function current_key(): string
    {
        $key  = isset($_GET['field']) ? sanitize_key(wp_unslash($_GET['field'])) : '';
        $defs = TNM_Song_Fields::definitions();
        return isset($defs[$key]) ? $key : (string) array_key_first($defs);
    }

    private static function url(string $key, array $args = array()): string
    {
        return add_query_arg(array_merge(array('page' => self::SLUG, 'field' => $key), $args), admin_url('admin.php'));
    }

    /* ---------------------------------------------------------------
     * Save / reset / import (POST → redirect → GET)
     * ------------------------------------------------------------- */

    public static function handle_post(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || empty($_POST['tsf_action'])) {
            return;
        }
        if (! current_user_can(self::capability())) {
            wp_die(esc_html__('You do not have permission to change the form choices.', 'tnm-song-fields'), 403);
        }
        check_admin_referer(self::NONCE);

        $key    = isset($_POST['tsf_field']) ? sanitize_key(wp_unslash($_POST['tsf_field'])) : '';
        $action = sanitize_key(wp_unslash($_POST['tsf_action']));
        $defs   = TNM_Song_Fields::definitions();
        $notice = 'saved';

        if ($action === 'import') {
            $result = TNM_Song_Fields::import_from_forminator();
            $notice = is_wp_error($result) ? 'import_failed' : 'imported';
            $key    = isset($defs[$key]) ? $key : (string) array_key_first($defs);
        } elseif (isset($defs[$key]) && $action === 'reset') {
            TNM_Song_Fields::reset_field($key);
            $notice = 'reset';
        } elseif (isset($defs[$key]) && $action === 'save') {
            $rows = isset($_POST['rows']) && is_array($_POST['rows']) ? $_POST['rows'] : array(); // sanitised per field in save_field()
            TNM_Song_Fields::save_field($key, $rows);
        } else {
            return;
        }

        wp_safe_redirect(self::url($key, array('tsf_notice' => $notice)));
        exit;
    }

    /* ---------------------------------------------------------------
     * Screen
     * ------------------------------------------------------------- */

    public static function render(): void
    {
        if (! current_user_can(self::capability())) {
            return;
        }

        $defs   = TNM_Song_Fields::definitions();
        $key    = self::current_key();
        $def    = $defs[$key];
        $rows   = TNM_Song_Fields::rows($key, false);
        $notice = isset($_GET['tsf_notice']) ? sanitize_key(wp_unslash($_GET['tsf_notice'])) : '';

        $messages = array(
            'saved'         => __('Choices saved. The song form shows them now.', 'tnm-song-fields'),
            'reset'         => __('Default choices restored.', 'tnm-song-fields'),
            'imported'      => __('Choices imported from the Forminator form. Check each list, then deactivate Forminator if nothing else uses it.', 'tnm-song-fields'),
            'import_failed' => __('Import failed: the Forminator "Song Order Fields" form could not be read.', 'tnm-song-fields'),
        );
        ?>
        <div class="wrap tsf">
            <h1 class="tsf-title"><?php esc_html_e('Song form choices', 'tnm-song-fields'); ?></h1>
            <p class="tsf-lede"><?php esc_html_e('Edit what customers can pick when they order a song. Changes appear on the form as soon as you save.', 'tnm-song-fields'); ?></p>

            <?php $conflict = tnm_song_fields_conflict_file(); if ($conflict !== '') : ?>
                <div class="notice notice-warning">
                    <p><strong><?php esc_html_e('The song form still reads its choices from the old Forminator module.', 'tnm-song-fields'); ?></strong></p>
                    <p><?php esc_html_e('Changes made here are saved but not used yet. Remove the old module (or the line that loads it) from this file:', 'tnm-song-fields'); ?></p>
                    <p><code><?php echo esc_html(str_replace(wp_normalize_path(ABSPATH), '', wp_normalize_path($conflict))); ?></code></p>
                </div>
            <?php endif; ?>

            <?php if (isset($messages[$notice])) : ?>
                <div class="notice <?php echo $notice === 'import_failed' ? 'notice-error' : 'notice-success'; ?> is-dismissible"><p><?php echo esc_html($messages[$notice]); ?></p></div>
            <?php endif; ?>

            <div class="tsf-layout">
                <nav class="tsf-rail" aria-label="<?php esc_attr_e('Form fields', 'tnm-song-fields'); ?>">
                    <?php foreach ($defs as $k => $d) :
                        $all   = TNM_Song_Fields::rows($k, false);
                        $shown = count(array_filter($all, fn($r) => ! empty($r['enabled'])));
                        ?>
                        <a class="tsf-rail__item<?php echo $k === $key ? ' is-current' : ''; ?>" href="<?php echo esc_url(self::url($k)); ?>"<?php echo $k === $key ? ' aria-current="page"' : ''; ?>>
                            <span class="tsf-rail__name"><?php echo esc_html($d['title']); ?></span>
                            <span class="tsf-rail__count"><?php
                                /* translators: %d: number of choices shown to customers */
                                echo esc_html(sprintf(_n('%d shown', '%d shown', $shown, 'tnm-song-fields'), $shown));
                                if (count($all) > $shown) {
                                    /* translators: %d: number of hidden choices */
                                    echo esc_html(sprintf(__(', %d hidden', 'tnm-song-fields'), count($all) - $shown));
                                }
                            ?></span>
                        </a>
                    <?php endforeach; ?>

                    <?php if (TNM_Song_Fields::forminator_form_id()) : ?>
                        <form method="post" class="tsf-import">
                            <?php wp_nonce_field(self::NONCE); ?>
                            <input type="hidden" name="tsf_action" value="import">
                            <input type="hidden" name="tsf_field" value="<?php echo esc_attr($key); ?>">
                            <p><?php esc_html_e('Your old choices are still in the Forminator form.', 'tnm-song-fields'); ?></p>
                            <button type="submit" class="tsf-btn tsf-btn--quiet" data-confirm="<?php esc_attr_e('Replace these lists with the choices from the Forminator form?', 'tnm-song-fields'); ?>"><?php esc_html_e('Import from Forminator', 'tnm-song-fields'); ?></button>
                        </form>
                    <?php endif; ?>
                </nav>

                <form method="post" class="tsf-editor" id="tsf-editor" data-type="<?php echo esc_attr($def['type']); ?>">
                    <?php wp_nonce_field(self::NONCE); ?>
                    <input type="hidden" name="tsf_field" value="<?php echo esc_attr($key); ?>">

                    <header class="tsf-editor__head">
                        <h2><?php echo esc_html($def['title']); ?></h2>
                        <p><?php echo esc_html($def['help']); ?></p>
                    </header>

                    <?php $def['type'] === 'questions' ? self::questions_table($rows) : self::choices_table($def, $rows); ?>

                    <button type="button" class="tsf-add" id="tsf-add">
                        <?php echo esc_html($def['type'] === 'questions' ? __('Add a question', 'tnm-song-fields') : __('Add a choice', 'tnm-song-fields')); ?>
                    </button>

                    <?php if ($def['type'] === 'choices') : ?>
                        <section class="tsf-preview" aria-live="polite">
                            <h3><?php esc_html_e('Customers see', 'tnm-song-fields'); ?></h3>
                            <div class="tsf-preview__chips" id="tsf-preview"></div>
                        </section>
                    <?php endif; ?>

                    <footer class="tsf-editor__foot">
                        <button type="submit" name="tsf_action" value="save" class="tsf-btn tsf-btn--primary"><?php esc_html_e('Save changes', 'tnm-song-fields'); ?></button>
                        <button type="submit" name="tsf_action" value="reset" class="tsf-btn tsf-btn--quiet" data-confirm="<?php esc_attr_e('Replace this list with the default choices? Your edits to this list will be lost.', 'tnm-song-fields'); ?>"><?php esc_html_e('Restore defaults', 'tnm-song-fields'); ?></button>
                        <span class="tsf-dirty" id="tsf-dirty" hidden><?php esc_html_e('Unsaved changes', 'tnm-song-fields'); ?></span>
                    </footer>
                </form>
            </div>
        </div>
        <?php
    }

    private static function choices_table(array $def, array $rows): void
    {
        $extra = (bool) $def['extra'];
        ?>
        <table class="tsf-table" id="tsf-rows">
            <thead>
                <tr>
                    <th class="tsf-col-move"><span class="screen-reader-text"><?php esc_html_e('Order', 'tnm-song-fields'); ?></span></th>
                    <th><?php esc_html_e('Label', 'tnm-song-fields'); ?></th>
                    <?php if ($extra) : ?><th><?php echo esc_html($def['extra_title']); ?></th><?php endif; ?>
                    <th class="tsf-col-value"><?php esc_html_e('Saved as', 'tnm-song-fields'); ?></th>
                    <th class="tsf-col-shown"><?php esc_html_e('Shown', 'tnm-song-fields'); ?></th>
                    <th class="tsf-col-remove"><span class="screen-reader-text"><?php esc_html_e('Remove', 'tnm-song-fields'); ?></span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $i => $row) {
                    self::choice_row((string) $i, $row, $extra, false);
                } ?>
            </tbody>
        </table>
        <template id="tsf-template"><?php self::choice_row('__i__', array('value' => '', 'label' => '', 'extra' => '', 'enabled' => true), $extra, true); ?></template>
        <?php
    }

    private static function choice_row(string $i, array $row, bool $extra, bool $is_new): void
    {
        $name = 'rows[' . $i . ']';
        ?>
        <tr class="tsf-row<?php echo empty($row['enabled']) ? ' is-hidden' : ''; ?>" draggable="true">
            <td class="tsf-col-move"><button type="button" class="tsf-move" aria-label="<?php esc_attr_e('Move (use arrow keys)', 'tnm-song-fields'); ?>"><span aria-hidden="true">⋮⋮</span></button></td>
            <td><input type="text" class="tsf-input tsf-label" name="<?php echo esc_attr($name); ?>[label]" value="<?php echo esc_attr($row['label']); ?>" maxlength="120" aria-label="<?php esc_attr_e('Label', 'tnm-song-fields'); ?>"></td>
            <?php if ($extra) : ?>
                <td><input type="text" class="tsf-input tsf-extra" name="<?php echo esc_attr($name); ?>[extra]" value="<?php echo esc_attr($row['extra']); ?>" maxlength="160" aria-label="<?php esc_attr_e('Secondary line', 'tnm-song-fields'); ?>"></td>
            <?php endif; ?>
            <td class="tsf-col-value">
                <?php if ($is_new) : ?>
                    <input type="text" class="tsf-input tsf-value" name="<?php echo esc_attr($name); ?>[value]" value="" maxlength="60" placeholder="<?php esc_attr_e('from label', 'tnm-song-fields'); ?>" aria-label="<?php esc_attr_e('Saved as', 'tnm-song-fields'); ?>">
                <?php else : ?>
                    <code class="tsf-code" title="<?php esc_attr_e('Orders store this value, so it cannot change.', 'tnm-song-fields'); ?>"><?php echo esc_html($row['value']); ?></code>
                    <input type="hidden" name="<?php echo esc_attr($name); ?>[original]" value="<?php echo esc_attr($row['value']); ?>">
                <?php endif; ?>
            </td>
            <td class="tsf-col-shown">
                <label class="tsf-switch">
                    <input type="checkbox" class="tsf-enabled" name="<?php echo esc_attr($name); ?>[enabled]" value="1" <?php checked(! empty($row['enabled'])); ?>>
                    <span class="tsf-switch__track" aria-hidden="true"></span>
                    <span class="screen-reader-text"><?php esc_html_e('Shown to customers', 'tnm-song-fields'); ?></span>
                </label>
            </td>
            <td class="tsf-col-remove"><button type="button" class="tsf-remove" data-new="<?php echo $is_new ? '1' : '0'; ?>" aria-label="<?php esc_attr_e('Remove', 'tnm-song-fields'); ?>">×</button></td>
        </tr>
        <?php
    }

    private static function questions_table(array $rows): void
    {
        ?>
        <table class="tsf-table tsf-table--questions" id="tsf-rows">
            <thead>
                <tr>
                    <th class="tsf-col-move"><span class="screen-reader-text"><?php esc_html_e('Order', 'tnm-song-fields'); ?></span></th>
                    <th><?php esc_html_e('Title', 'tnm-song-fields'); ?></th>
                    <th><?php esc_html_e('Question', 'tnm-song-fields'); ?></th>
                    <th class="tsf-col-shown"><?php esc_html_e('Shown', 'tnm-song-fields'); ?></th>
                    <th class="tsf-col-remove"><span class="screen-reader-text"><?php esc_html_e('Remove', 'tnm-song-fields'); ?></span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $i => $row) {
                    self::question_row((string) $i, $row, false);
                } ?>
            </tbody>
        </table>
        <template id="tsf-template"><?php self::question_row('__i__', array('title' => '', 'question' => '', 'enabled' => true), true); ?></template>
        <?php
    }

    private static function question_row(string $i, array $row, bool $is_new): void
    {
        $name = 'rows[' . $i . ']';
        ?>
        <tr class="tsf-row<?php echo empty($row['enabled']) ? ' is-hidden' : ''; ?>" draggable="true">
            <td class="tsf-col-move"><button type="button" class="tsf-move" aria-label="<?php esc_attr_e('Move (use arrow keys)', 'tnm-song-fields'); ?>"><span aria-hidden="true">⋮⋮</span></button></td>
            <td><input type="text" class="tsf-input" name="<?php echo esc_attr($name); ?>[title]" value="<?php echo esc_attr($row['title']); ?>" maxlength="120" aria-label="<?php esc_attr_e('Title', 'tnm-song-fields'); ?>"></td>
            <td><textarea class="tsf-input" name="<?php echo esc_attr($name); ?>[question]" rows="2" maxlength="400" aria-label="<?php esc_attr_e('Question', 'tnm-song-fields'); ?>"><?php echo esc_textarea($row['question']); ?></textarea></td>
            <td class="tsf-col-shown">
                <label class="tsf-switch">
                    <input type="checkbox" class="tsf-enabled" name="<?php echo esc_attr($name); ?>[enabled]" value="1" <?php checked(! empty($row['enabled'])); ?>>
                    <span class="tsf-switch__track" aria-hidden="true"></span>
                    <span class="screen-reader-text"><?php esc_html_e('Shown to customers', 'tnm-song-fields'); ?></span>
                </label>
            </td>
            <td class="tsf-col-remove"><button type="button" class="tsf-remove" data-new="1" aria-label="<?php esc_attr_e('Remove', 'tnm-song-fields'); ?>">×</button></td>
        </tr>
        <?php
    }
}
