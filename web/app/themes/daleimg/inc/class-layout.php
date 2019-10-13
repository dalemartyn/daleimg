<?php
/**
 * Sockman Layout.
 *
 * Grid settings from `_settings.layout.scss`.
 *
 * @package Sockman
 */

namespace Sockman;

/**
 * Sockman Layout class.
 */
class Layout {
	/**
	 * Base font size
	 *
	 * @var string
	 */
	public $font_size_base = '16px';

	/**
	 * Base line height
	 *
	 * @var string
	 */
	public $line_height_base = '24px';

	/**
	 * Breakpoints
	 *
	 * @var array
	 */
	public $breakpoints = array(
		'null' => 'null',
		'w600' => '600px',
		'w960' => '960px',
	);

	/**
	 * Breakpoint names used on helper classes. e.g. .o-layout__cell--span-4@c8
	 *
	 * @var array
	 */
	public $breakpoint_names = array(
		'null' => 'null',
		'w600' => 'c8',
		'w960' => 'c12',
	);

	/**
	 * Number of columns and their breakpoints.
	 *
	 * @var array
	 */
	public $columns = array(
		'null' => '4',
		'w600' => '8',
		'w960' => '12',
	);

	/**
	 * Margins used at breakpoints.
	 *
	 * @var array
	 */
	public $margins = array(
		'null' => '16px',
		'w600' => '48px',
		'w960' => '48px',
	);

	/**
	 * Gutters used at the breakpoints.
	 *
	 * @var array
	 */
	public $gutters = array(
		'null' => '24px',
		'w600' => '24px',
		'w960' => '32px',
	);

	/**
	 * Default column widths.
	 *
	 * @var array
	 */
	public $column_widths = array(
		'null' => '50px',
		'w600' => '50px',
		'w960' => '50px',
	);

	/**
	 * Get the max width of the layout.
	 *
	 * @return $max_width
	 */
	public function max_width() {
		$upper_breakpoint_val = end( $this->breakpoints );
		$upper_breakpoint     = key( $this->breakpoints );
		$col_width            = $this->column_widths[ $upper_breakpoint ];
		$margins              = $this->margins[ $upper_breakpoint ];
		$gutters              = $this->gutters[ $upper_breakpoint ];
		$max_cols             = $this->columns[ $upper_breakpoint ];
		$max_width            = ( (int) $max_cols * (int) $col_width ) + ( ( (int) $max_cols - 1 ) * (int) $gutters ) + ( (int) $margins * 2 );

		return $max_width;
	}

	/**
	 * Get the width of columns + gutters on desktop (no margins).
	 *
	 * @return $width
	 */
	public function content_width() {
		$upper_breakpoint_val = end( $this->breakpoints );
		$upper_breakpoint     = key( $this->breakpoints );
		$max_cols             = $this->columns[ $upper_breakpoint ];

		return $this->fixed_width( $max_cols );
	}

	/**
	 * Get the width of wrapper on desktop (columns + gutters and margins).
	 *
	 * @param int $cols The number of cols to span.
	 * @return $width
	 */
	public function fixed_width( $cols ) {
		$upper_breakpoint_val = end( $this->breakpoints );
		$upper_breakpoint     = key( $this->breakpoints );
		$col_width            = $this->column_widths[ $upper_breakpoint ];
		$gutters              = $this->gutters[ $upper_breakpoint ];
		$width                = ( (int) $cols * (int) $col_width ) + ( ( (int) $cols - 1 ) * (int) $gutters );

		return $width;
	}

	/**
	 * Get the max size for the srcset.
	 *
	 * @param int $span The number of cols to span.
	 * @return $max_size
	 */
	public function max_size( $span ) {
		$breakpoint_value = end( $this->breakpoints );
		// Breakpoint name.
		$breakpoint = key( $this->breakpoints );

		$margin_size = $this->margins[ $breakpoint ];
		$gutter_size = $this->gutters[ $breakpoint ];
		$cols = $this->columns[ $breakpoint ];

		$max_container_width = $this->max_width();
		$max_container_width_in_ems = $this->px_to_em( $max_container_width );
		$calc = $this->calc( $span, $cols, $max_container_width_in_ems, $margin_size, $gutter_size );
		$max_size = '(min-width: ' . $max_container_width_in_ems . ') ' . $calc;

		return $max_size;
	}


	/**
	 * Get a single width
	 *
	 * @param array $size The span of cols. e.g. [3, 12] is 3 of 12.
	 * @return $width
	 */
	public function flexible_width( $size ) {
		$span = $size[0];
		$cols = $size[1];

		// breakpoint name.
		$breakpoint = array_search( $cols, $this->columns );
		$breakpoint_value = $this->breakpoints[ $breakpoint ];

		if ( 'null' === $breakpoint_value ) {
			$min_width = '';
		} else {
			$min_width = '(min-width: ' . $this->px_to_em( $breakpoint_value ) . ') ';
		}

		$margin_size = $this->margins[ $breakpoint ];
		$gutter_size = $this->gutters[ $breakpoint ];

		$width_value = $this->calc( $span, $cols, '100vw', $margin_size, $gutter_size );

		$width = $min_width . $width_value;
		return $width;
	}

	/**
	 * Get the css calc()
	 *
	 * @param string $span The number of cols to span.
	 * @param string $cols The number of cols in the layout.
	 * @param string $container_width The conatiner width in px or vw.
	 * @param string $margin_size The margin size in px.
	 * @param string $gutter_size The gutter size in px.
	 * @return $calc
	 */
	public function calc( $span, $cols, $container_width, $margin_size, $gutter_size ) {
		$both_margins = (int) $margin_size * 2;
		$margins_minus_a_gutter = $both_margins - (int) $gutter_size;

		$calc = 'calc(((' . $container_width . ' - ' . $margins_minus_a_gutter . 'px) * ' . $span . ' / ' . $cols . ') - ' . $gutter_size . ')';

		return $calc;
	}

	/**
	 * Get a px value in ems.
	 *
	 * @param String $px The px value.
	 * @return $em_value
	 */
	public function px_to_em( $px ) {

		$em_value = (int) $px / (int) $this->font_size_base;

		return $em_value . 'em';
	}

}
