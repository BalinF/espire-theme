<?php
/**
 * inc/acf-rows.php — add-a-row lists (Journey steps, Available Fits,
 * Size Notes, Quick Answers, Pillar Cards) on the free version of ACF.
 *
 * ACF's "Repeater" field is ACF Pro only; on free ACF it shows as an
 * empty box. espire_acf_rows() turns each repeater in a field group into
 * a Group of numbered rows ("Step 1", "Step 2"…), as many as its 'max'.
 * The rows save as journey_steps_row1_step_tag and so on, and get_field()
 * returns the filled-in rows as a list, so the templates read them just
 * like a repeater.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Rows to offer when a repeater doesn't set 'max'. */
define( 'ESPIRE_ACF_ROWS_DEFAULT', 5 );

/** A field group with its repeaters (including nested ones) turned into row groups. */
function espire_acf_rows( $group ) {
	if ( ! empty( $group['fields'] ) ) {
		$group['fields'] = array_map( 'espire_acf_rows_field', $group['fields'] );
	}
	return $group;
}

/** One field: a repeater becomes a Group holding one Group per row. */
function espire_acf_rows_field( $field ) {
	if ( ! empty( $field['sub_fields'] ) ) {
		$field['sub_fields'] = array_map( 'espire_acf_rows_field', $field['sub_fields'] );
	}
	if ( empty( $field['type'] ) || 'repeater' !== $field['type'] ) {
		return $field;
	}

	$count = ! empty( $field['max'] ) ? (int) $field['max'] : ESPIRE_ACF_ROWS_DEFAULT;
	// "Add Step" → "Step 1", "Step 2"…
	$noun = ! empty( $field['button_label'] ) ? trim( preg_replace( '/^Add\s+/i', '', $field['button_label'] ) ) : 'Row';
	$rows = array();
	for ( $i = 0; $i < $count; $i++ ) {
		$sub = array();
		foreach ( $field['sub_fields'] as $child ) {
			$child['key'] = $child['key'] . '_' . $i;
			$sub[]        = espire_acf_rows_rekey( $child, $i );
		}
		$rows[] = array(
			'key'        => $field['key'] . '_' . $i,
			'label'      => $noun . ' ' . ( $i + 1 ),
			'name'       => 'row' . ( $i + 1 ), // not "0": ACF treats that as no name
			'type'       => 'group',
			'layout'     => ( isset( $field['layout'] ) && 'table' === $field['layout'] ) ? 'table' : 'block',
			'sub_fields' => $sub,
		);
	}

	return array(
		'key'          => $field['key'],
		'label'        => $field['label'],
		'name'         => $field['name'],
		'type'         => 'group',
		'layout'       => 'block',
		'instructions' => trim( ( isset( $field['instructions'] ) ? $field['instructions'] . ' ' : '' ) . 'Fill in as many as you need; empty ones are skipped.' ),
		'sub_fields'   => $rows,
		'espire_rows'  => true, // see the format filter below
	);
}

/** Row copies of nested fields need unique keys too. */
function espire_acf_rows_rekey( $field, $i ) {
	if ( ! empty( $field['sub_fields'] ) ) {
		foreach ( $field['sub_fields'] as $n => $child ) {
			$child['key']               = $child['key'] . '_' . $i;
			$field['sub_fields'][ $n ] = espire_acf_rows_rekey( $child, $i );
		}
	}
	return $field;
}

/** True if a row has nothing filled in. */
function espire_acf_row_is_empty( $value ) {
	if ( is_array( $value ) ) {
		foreach ( $value as $v ) {
			if ( ! espire_acf_row_is_empty( $v ) ) {
				return false;
			}
		}
		return true;
	}
	return null === $value || false === $value || '' === $value;
}

/** get_field() on a row group: the filled-in rows, in order (like a repeater). */
add_filter( 'acf/format_value/type=group', function ( $value, $post_id, $field ) {
	if ( empty( $field['espire_rows'] ) || ! is_array( $value ) ) {
		return $value;
	}
	return array_values( array_filter( $value, function ( $row ) {
		return is_array( $row ) && ! espire_acf_row_is_empty( $row );
	} ) );
}, 20, 3 );
