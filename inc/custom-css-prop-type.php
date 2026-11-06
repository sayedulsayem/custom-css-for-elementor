<?php

namespace Custom_Css_FEle\Inc;

use Elementor\Modules\AtomicWidgets\PropTypes\Primitives\String_Prop_Type;

// If this file is called directly, abort.
defined( 'ABSPATH' ) || exit;

/**
 * Prop type for the Custom CSS fields on Atomic Elements.
 *
 * Behaves like Elementor's `String_Prop_Type` (same `string` kind, so the
 * editor treats the value as a plain string prop) but skips
 * `sanitize_text_field()`, which would collapse whitespace and strip
 * tag-like sequences from the CSS. The CSS itself is sanitized later by
 * `Hook_Action::parse_css_to_remove_injecting_code()` before output.
 */
class Custom_Css_Prop_Type extends String_Prop_Type {

	protected function sanitize_value( $value ) {
		return $value;
	}
}
