<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Choice lists of the song order form, stored in one option (tnm_song_fields).
 *
 * Choice row:   ['value' => 'birthday', 'label' => 'Birthday', 'extra' => '', 'enabled' => true]
 * Question row: ['title' => '…', 'question' => '…', 'enabled' => true]
 *
 * "value" is what orders store, so it never changes once saved.
 */
final class TNM_Song_Fields
{
    public const OPTION   = 'tnm_song_fields';
    public const MAX_ROWS = 100;

    private static ?array $data = null;

    /**
     * Field definitions.
     * extra: name of the secondary line ('badge' / 'desc') or null when the field has none.
     * legacy: label of the matching field in the old Forminator form (for import).
     */
    public static function definitions(): array
    {
        return array(
            'occasion' => array(
                'title'       => __('Occasion', 'tnm-song-fields'),
                'help'        => __('What the song is for. The badge is a short note shown on the choice, for example a date to plan for.', 'tnm-song-fields'),
                'type'        => 'choices',
                'extra'       => 'badge',
                'extra_title' => __('Badge', 'tnm-song-fields'),
                'legacy'      => 'Occasion',
            ),
            'themes' => array(
                'title'       => __('Themes', 'tnm-song-fields'),
                'help'        => __('Customers can pick several themes.', 'tnm-song-fields'),
                'type'        => 'choices',
                'extra'       => null,
                'extra_title' => '',
                'legacy'      => 'Themes',
            ),
            'style' => array(
                'title'       => __('Music style', 'tnm-song-fields'),
                'help'        => __('The description appears under the style name.', 'tnm-song-fields'),
                'type'        => 'choices',
                'extra'       => 'desc',
                'extra_title' => __('Description', 'tnm-song-fields'),
                'legacy'      => 'Style',
            ),
            'tempo' => array(
                'title'       => __('Tempo', 'tnm-song-fields'),
                'help'        => __('The description appears under the tempo name.', 'tnm-song-fields'),
                'type'        => 'choices',
                'extra'       => 'desc',
                'extra_title' => __('Description', 'tnm-song-fields'),
                'legacy'      => 'Tempo',
            ),
            'voice' => array(
                'title'       => __('Vocal type', 'tnm-song-fields'),
                'help'        => __('The description appears under the vocal type.', 'tnm-song-fields'),
                'type'        => 'choices',
                'extra'       => 'desc',
                'extra_title' => __('Description', 'tnm-song-fields'),
                'legacy'      => 'Vocal Type',
            ),
            'language' => array(
                'title'       => __('Lyrics language', 'tnm-song-fields'),
                'help'        => __('Languages the team can write lyrics in.', 'tnm-song-fields'),
                'type'        => 'choices',
                'extra'       => 'desc',
                'extra_title' => __('Description', 'tnm-song-fields'),
                'legacy'      => 'Language',
            ),
            'story_questions' => array(
                'title'  => __('Story questions', 'tnm-song-fields'),
                'help'   => __('Prompts that help customers tell their story.', 'tnm-song-fields'),
                'type'   => 'questions',
                'legacy' => null,
            ),
        );
    }

    /* ---------------------------------------------------------------
     * Defaults (same lists as the Forminator form was created with)
     * ------------------------------------------------------------- */

    public static function defaults(): array
    {
        $c = fn(string $value, string $label, string $extra = '') => array('value' => $value, 'label' => $label, 'extra' => $extra, 'enabled' => true);

        return array(
            'occasion' => array(
                $c('just_because', 'Just Because'),
                $c('fathers_day', "Father's Day", 'Plan ahead for June 21'),
                $c('anniversary', 'Anniversary'),
                $c('birthday', 'Birthday'),
                $c('graduation', 'Graduation'),
                $c('wedding', 'Wedding'),
                $c('memorial', 'Memorial'),
                $c('personal_worship', 'Worship'),
                $c('dedication', 'Baby Dedication'),
                $c('encouragement', 'Encouragement'),
                $c('mothers_day', "Mother's Day"),
                $c('retirement', 'Retirement'),
                $c('baptism', 'Baptism'),
                $c('baby', 'Baby/Lullaby'),
                $c('engagement', 'Engagement'),
                $c('other', 'Other'),
            ),
            'themes' => array_map(fn($p) => $c($p[0], $p[1]), array(
                array('faith', 'Faith'), array('hope', 'Hope'), array('love', 'Love'), array('peace', 'Peace'),
                array('joy', 'Joy'), array('gratitude', 'Gratitude'), array('healing', 'Healing'), array('comfort', 'Comfort'),
                array('strength', 'Strength'), array('guidance', 'Guidance'), array('forgiveness', 'Forgiveness'), array('grace', 'Grace'),
                array('family', 'Family'), array('legacy', 'Legacy'), array('new_beginnings', 'New Beginnings'), array('remembrance', 'Remembrance'),
                array('celebration', 'Celebration'), array('courage', 'Courage'), array('devotion', 'Devotion'),
            )),
            'style' => array(
                $c('worship', 'Worship', 'Reverent and devotional'),
                $c('gospel', 'Gospel', 'Soulful and uplifting'),
                $c('contemporary', 'Contemporary', 'Modern and accessible'),
                $c('acoustic', 'Acoustic', 'Warm and intimate'),
                $c('country', 'Country', 'Warm and heartfelt'),
                $c('rnb_soul', 'R&B/Soul', 'Rich and emotive'),
                $c('pop_ballad', 'Pop Ballad', 'Modern and melodic'),
                $c('cinematic', 'Cinematic', 'Epic and orchestral'),
                $c('lullaby', 'Lullaby', 'Gentle and soothing'),
            ),
            'tempo' => array(
                $c('slow', 'Slow', 'Reflective & peaceful'),
                $c('medium', 'Medium', 'Steady & balanced'),
                $c('upbeat', 'Upbeat', 'Joyful & energetic'),
            ),
            'voice' => array(
                $c('female', 'Female', 'Solo female singer'),
                $c('male', 'Male', 'Solo male singer'),
            ),
            'language' => array(
                $c('english', 'English'),
            ),
            'story_questions' => array(
                array('title' => 'Meaningful Places & Memories', 'question' => 'Where did your story happen, and why does that place still matter?', 'enabled' => true),
                array('title' => 'Special Moments', 'question' => 'What moment would you love for them to hear remembered in the song?', 'enabled' => true),
                array('title' => 'What Makes Them Special', 'question' => 'What is something about them that feels uniquely theirs?', 'enabled' => true),
                array('title' => 'Personality & Interests', 'question' => 'Share their habits, interests, nicknames, pets, favorite things, or phrases they often say.', 'enabled' => true),
            ),
        );
    }

    /* ---------------------------------------------------------------
     * Read / write
     * ------------------------------------------------------------- */

    public static function all(): array
    {
        if (self::$data === null) {
            $stored     = get_option(self::OPTION, null);
            $defaults   = self::defaults();
            self::$data = array();
            foreach (self::definitions() as $key => $def) {
                self::$data[$key] = (is_array($stored) && isset($stored[$key]) && is_array($stored[$key]))
                    ? $stored[$key]
                    : $defaults[$key];
            }
        }
        return self::$data;
    }

    /** Rows of one field; $only_enabled = what customers see */
    public static function rows(string $key, bool $only_enabled = true): array
    {
        $rows = self::all()[$key] ?? array();
        return $only_enabled ? array_values(array_filter($rows, fn($r) => ! empty($r['enabled']))) : $rows;
    }

    /** Label of a stored value (hidden choices included, so old orders keep their labels) */
    public static function label(string $key, string $value): string
    {
        foreach (self::rows($key, false) as $row) {
            if (($row['value'] ?? '') === $value) {
                return (string) $row['label'];
            }
        }
        return ucwords(str_replace(array('_', '-'), ' ', $value));
    }

    /**
     * Save one field.
     * @param array $rows      rows as posted by the admin screen
     * @param array $old_values values that existed before (they keep their value)
     */
    public static function save_field(string $key, array $rows): void
    {
        $defs = self::definitions();
        if (! isset($defs[$key])) {
            return;
        }

        $data       = self::all();
        $data[$key] = $defs[$key]['type'] === 'questions'
            ? self::clean_questions($rows)
            : self::clean_choices($rows, $data[$key], (bool) $defs[$key]['extra']);

        update_option(self::OPTION, $data, true);
        self::$data = $data;
        do_action('tnm_song_fields_saved', $key, $data[$key]);
    }

    public static function reset_field(string $key): void
    {
        $defaults = self::defaults();
        if (isset($defaults[$key])) {
            self::save_field($key, $defaults[$key]);
        }
    }

    private static function clean_choices(array $rows, array $current, bool $has_extra): array
    {
        $existing = array_column($current, 'value');
        $out      = array();
        $seen     = array();

        foreach (array_slice($rows, 0, self::MAX_ROWS) as $row) {
            $label = trim(sanitize_text_field(wp_unslash((string) ($row['label'] ?? ''))));
            if ($label === '') {
                continue;
            }

            // Existing values are kept as they are; new ones come from the posted value or the label
            $posted = (string) ($row['original'] ?? '');
            $value  = in_array($posted, $existing, true)
                ? $posted
                : self::slug((string) ($row['value'] ?? '') ?: $label);

            if ($value === '') {
                continue;
            }
            $base = $value;
            $n    = 2;
            while (isset($seen[$value])) {
                $value = $base . '_' . $n++;
            }
            $seen[$value] = true;

            $out[] = array(
                'value'   => $value,
                'label'   => $label,
                'extra'   => $has_extra ? trim(sanitize_text_field(wp_unslash((string) ($row['extra'] ?? '')))) : '',
                'enabled' => ! empty($row['enabled']),
            );
        }
        return $out;
    }

    private static function clean_questions(array $rows): array
    {
        $out = array();
        foreach (array_slice($rows, 0, self::MAX_ROWS) as $row) {
            $title    = trim(sanitize_text_field(wp_unslash((string) ($row['title'] ?? ''))));
            $question = trim(sanitize_textarea_field(wp_unslash((string) ($row['question'] ?? ''))));
            if ($title === '' && $question === '') {
                continue;
            }
            $out[] = array('title' => $title, 'question' => $question, 'enabled' => ! empty($row['enabled']));
        }
        return $out;
    }

    /** "R&B / Soul" → "rb_soul" */
    public static function slug(string $text): string
    {
        $text = remove_accents(strtolower($text));
        $text = preg_replace('/[^a-z0-9]+/', '_', $text);
        return trim((string) $text, '_');
    }

    /* ---------------------------------------------------------------
     * Import from the old Forminator form
     * ------------------------------------------------------------- */

    public static function forminator_form_id(): int
    {
        return class_exists('Forminator_API') ? (int) get_option('tnm_song_fields_form_id') : 0;
    }

    /** @return array|WP_Error imported counts per field */
    public static function import_from_forminator()
    {
        $form_id = self::forminator_form_id();
        if (! $form_id) {
            return new WP_Error('no_form', __('The Forminator "Song Order Fields" form was not found.', 'tnm-song-fields'));
        }
        $fields = Forminator_API::get_form_fields($form_id);
        if (is_wp_error($fields)) {
            return $fields;
        }

        $by_label = array();
        foreach ((array) $fields as $field) {
            $arr = is_object($field) && method_exists($field, 'to_array') ? $field->to_array() : (array) $field;
            $by_label[(string) ($arr['field_label'] ?? '')] = (array) ($arr['options'] ?? array());
        }

        $counts = array();
        foreach (self::definitions() as $key => $def) {
            if (empty($def['legacy']) || ! isset($by_label[$def['legacy']])) {
                continue;
            }
            $rows = array();
            foreach ($by_label[$def['legacy']] as $option) {
                $label = (string) ($option['label'] ?? '');
                $extra = '';
                if (strpos($label, '||') !== false) {
                    [$label, $extra] = array_map('trim', explode('||', $label, 2));
                }
                $value  = (string) ($option['value'] ?? '');
                $rows[] = array('original' => $value, 'value' => $value, 'label' => $label, 'extra' => $extra, 'enabled' => 1);
            }
            // Imported values are trusted as "existing": they are what past orders stored
            $data       = self::all();
            $data[$key] = array_map(fn($r) => array('value' => $r['value'], 'label' => $r['label'], 'extra' => $r['extra'], 'enabled' => true), $rows);
            update_option(self::OPTION, $data, true);
            self::$data = null;
            self::save_field($key, $rows);
            $counts[$key] = count(self::rows($key, false));
        }
        return $counts;
    }
}
