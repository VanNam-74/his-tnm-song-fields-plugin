<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Public API used by [tnm_song_begin] and the order plugins.
 * Same function names and return shapes as the old Forminator-based module,
 * so the shortcode keeps working without changes.
 *
 * Only enabled choices are returned; hidden choices stay available to
 * tnm_song_field_label() so older orders still show their label.
 */

/** ['value' => '', 'label' => '', $extra_key => ''] — $extra_key omitted when empty */
function tnm_song_fields_choices(string $key, string $extra_key = 'desc'): array
{
    return array_map(static function ($row) use ($extra_key) {
        $choice = array('value' => (string) $row['value'], 'label' => (string) $row['label']);
        if (($row['extra'] ?? '') !== '') {
            $choice[$extra_key] = (string) $row['extra'];
        }
        return $choice;
    }, TNM_Song_Fields::rows($key));
}

/** Label of a stored value, for order details, emails and the CRM */
function tnm_song_field_label(string $key, string $value): string
{
    return TNM_Song_Fields::label($key, $value);
}
