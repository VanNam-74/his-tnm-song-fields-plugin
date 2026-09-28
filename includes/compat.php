<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Old function names used by [tnm_song_begin] (same names and return shapes as
 * the Forminator-based "Song Fields Provider" module).
 *
 * Loaded on plugins_loaded, AFTER every plugin file is included, so an old copy of
 * the module that is still loaded never causes a "Cannot redeclare" fatal error:
 * in that case the old functions win and the admin screen shows a notice.
 */

if (! function_exists('tnm_get_occasion_choices')) {
    function tnm_get_occasion_choices()
    {
        return tnm_song_fields_choices('occasion', 'badge');
    }
}

if (! function_exists('tnm_get_theme_choices')) {
    function tnm_get_theme_choices()
    {
        // Same shape as before: ['label' => '', 'value' => ''], no secondary line
        return array_map(fn($c) => array('label' => $c['label'], 'value' => $c['value']), tnm_song_fields_choices('themes'));
    }
}

if (! function_exists('tnm_get_style_choices')) {
    function tnm_get_style_choices()
    {
        return tnm_song_fields_choices('style', 'desc');
    }
}

if (! function_exists('tnm_get_tempo_choices')) {
    function tnm_get_tempo_choices()
    {
        return tnm_song_fields_choices('tempo', 'desc');
    }
}

if (! function_exists('tnm_get_voice_choices')) {
    function tnm_get_voice_choices()
    {
        return tnm_song_fields_choices('voice', 'desc');
    }
}

if (! function_exists('tnm_get_language_choices')) {
    function tnm_get_language_choices()
    {
        return tnm_song_fields_choices('language', 'desc');
    }
}

if (! function_exists('tnm_get_story_question')) {
    function tnm_get_story_question()
    {
        return array_map(
            fn($q) => array('title' => (string) $q['title'], 'question' => (string) $q['question']),
            TNM_Song_Fields::rows('story_questions')
        );
    }
}

if (! function_exists('tnm_get_field_options')) {
    /**
     * Old Forminator-style access by field label: ['label' => 'Label || Extra', 'value' => ''].
     * Kept for any code that still calls it.
     */
    function tnm_get_field_options($field_label)
    {
        foreach (TNM_Song_Fields::definitions() as $key => $def) {
            if (($def['legacy'] ?? null) === $field_label) {
                return array_map(fn($r) => array(
                    'label' => $r['extra'] !== '' ? $r['label'] . ' || ' . $r['extra'] : $r['label'],
                    'value' => $r['value'],
                ), TNM_Song_Fields::rows($key));
            }
        }
        return array();
    }
}

if (! function_exists('tnm_parse_choice_option')) {
    /** Kept for compatibility: splits "Label || Extra" */
    function tnm_parse_choice_option($option, $extra_key = 'desc')
    {
        $label = $option['label'] ?? '';
        $extra = '';
        if (strpos($label, '||') !== false) {
            [$label, $extra] = array_map('trim', explode('||', $label, 2));
        }
        $choice = array('value' => $option['value'] ?? '', 'label' => $label);
        if ($extra !== '') {
            $choice[$extra_key] = $extra;
        }
        return $choice;
    }
}
