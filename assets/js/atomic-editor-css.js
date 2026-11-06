/**
 * Atomic Elements support for Custom CSS for Elementor.
 *
 * 1. Registers a `custom-css-code` control type (ACE editor, same engine as
 *    the classic Custom CSS control) in the atomic controls registry and
 *    injects a "Custom CSS for Elementor" section into the Atomic "Style"
 *    tab. Values are stored as regular element props through Elementor's own
 *    settings pipeline (undo/history/persistence), and the matching props
 *    are registered in the props schema on the PHP side so they survive
 *    `Props_Parser` validation on save.
 *
 * 2. Mirrors the saved CSS into the editor canvas as a live <style> tag.
 *    Atomic elements render client-side, so the server-generated post CSS
 *    only refreshes on preview reload; this keeps the preview in sync while
 *    editing. The frontend/preview output is still produced by
 *    `elementor/element/parse_css` on the PHP side.
 */
(function () {
	'use strict';

	var injected = false;
	var attempts = 0;
	var MAX_ATTEMPTS = 200; // ~60 seconds at 300ms intervals
	var CONTROL_TYPE = 'custom-css-code';
	var STYLE_TAG_ID = 'custom-css-for-elementor-atomic';

	var FIELDS = [
		{ bind: '_custom_css_f_ele_css_desktop', labelKey: 'desktop', iconClass: 'eicon-device-desktop' },
		{ bind: '_custom_css_f_ele_css_tablet', labelKey: 'tablet', iconClass: 'eicon-device-tablet' },
		{ bind: '_custom_css_f_ele_css_mobile', labelKey: 'mobile', iconClass: 'eicon-device-mobile' }
	];

	var PROPS_KEYS = FIELDS.map(function (field) {
		return field.bind;
	});

	function t(key) {
		return (window.atomicEditorCssData && window.atomicEditorCssData[key]) || key;
	}

	function getPanel() {
		return window.elementorV2 && window.elementorV2.editorEditingPanel
			? window.elementorV2.editorEditingPanel
			: null;
	}

	function getV2(name) {
		return window.elementorV2 ? window.elementorV2[name] : null;
	}

	/* ---------------------------------------------------------------------
	 * Code editor control (ACE, same setup as the classic code control)
	 * ------------------------------------------------------------------ */

	function isDarkTheme() {
		try {
			var prefs = window.elementor && window.elementor.settings && window.elementor.settings.editorPreferences;
			var uiTheme = prefs && prefs.model ? prefs.model.get('ui_theme') : '';
			var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

			return 'dark' === uiTheme || ('auto' === uiTheme && prefersDark);
		} catch (e) {
			return false;
		}
	}

	function AceCssControl(props) {
		var editorControls = getV2('editorControls');
		var editorProps = getV2('editorProps');
		var ReactLib = window.React;
		var bound = editorControls.useBoundProp(editorProps.stringPropTypeUtil);
		var hostRef = ReactLib.useRef(null);
		var editorRef = ReactLib.useRef(null);
		var value = bound.value || '';
		var disabled = !!bound.disabled;

		ReactLib.useEffect(function () {
			var host = hostRef.current;

			if (!host || 'undefined' === typeof window.ace) {
				return;
			}

			var uiTheme = isDarkTheme();

			try {
				var langTools = window.ace.require('ace/ext/language_tools');
				var editor = window.ace.edit(host);

				editor.container.classList.add('e-input-style', 'elementor-code-editor', 'elementor-control-tag-area');
				editor.setOptions({
					mode: 'ace/mode/css',
					minLines: 10,
					maxLines: Infinity,
					showGutter: true,
					useWorker: true,
					enableBasicAutocompletion: true,
					enableLiveAutocompletion: true
				});

				if (uiTheme) {
					editor.setTheme('ace/theme/merbivore_soft');
				}

				editor.getSession().setUseWrapMode(true);

				langTools.addCompleter({
					getCompletions: function (editorInstance, session, pos, prefix, callback) {
						var list = [];
						var token = session.getTokenAt(pos.row, pos.column);

						if (0 < prefix.length && 'selector'.match(prefix) && 'constant' === token.type) {
							list = [{
								name: 'selector',
								value: 'selector',
								score: 1,
								meta: 'Elementor'
							}];
						}

						callback(null, list);
					}
				});

				editor.setValue(value, -1);
				editor.setReadOnly(disabled);
				editor.on('change', function () {
					bound.setValue(editor.getValue());
				});

				editorRef.current = editor;
			} catch (e) {
				return;
			}

			function onWindowResize() {
				if (editorRef.current) {
					editorRef.current.resize();
				}
			}

			window.addEventListener('resize', onWindowResize);

			return function () {
				window.removeEventListener('resize', onWindowResize);

				if (editorRef.current) {
					editorRef.current.destroy();
					editorRef.current = null;
				}
			};
			// eslint-disable-next-line react-hooks/exhaustive-deps
		}, []);

		// Keep the editor in sync when the value changes externally
		// (undo/redo, element switch, programmatic updates).
		ReactLib.useEffect(function () {
			var editor = editorRef.current;

			if (editor && editor.getValue() !== value) {
				var cursor = editor.getCursorPosition();
				editor.setValue(value, -1);
				editor.moveCursorToPosition(cursor);
				editor.clearSelection();
			}

			if (editor) {
				editor.setReadOnly(disabled);
			}
		}, [value, disabled]);

		if ('undefined' === typeof window.ace) {
			return ReactLib.createElement(editorControls.TextAreaControl, props);
		}

		return ReactLib.createElement('div', { ref: hostRef, style: { minHeight: '120px' } });
	}

	function registerControlType() {
		var panel = getPanel();
		var editorControls = getV2('editorControls');
		var editorProps = getV2('editorProps');

		if (!panel || !panel.controlsRegistry || !editorControls || !editorProps) {
			return false;
		}

		var registry = panel.controlsRegistry;

		if (registry.registry && registry.registry()[CONTROL_TYPE]) {
			return true;
		}

		try {
			registry.register(
				CONTROL_TYPE,
				editorControls.createControl(AceCssControl),
				'full',
				editorProps.stringPropTypeUtil
			);
		} catch (e) {
			return !!(registry.registry && registry.registry()[CONTROL_TYPE]);
		}

		return true;
	}

	/* ---------------------------------------------------------------------
	 * Live preview: mirror the custom CSS into the canvas iframe
	 * ------------------------------------------------------------------ */

	function unwrapProp(value) {
		if (value && typeof value === 'object' && 'value' in value) {
			value = value.value;
		}

		return 'string' === typeof value ? value : '';
	}

	function getDocumentId() {
		var editorElements = getV2('editorElements');

		try {
			if (editorElements && editorElements.getCurrentDocumentId) {
				return editorElements.getCurrentDocumentId();
			}
		} catch (e) {}

		return (window.atomicEditorCssData && window.atomicEditorCssData.postID) || null;
	}

	function isTransformableProp(value) {
		return !!(value && typeof value === 'object' && 'value' in value);
	}

	function isAtomicContainer(container) {
		var editorElements = getV2('editorElements');
		var model = container && container.model;

		if (!editorElements || !editorElements.getWidgetsCache || !model) {
			return false;
		}

		var type = model.get('widgetType') || model.get('elType');
		var config = type ? editorElements.getWidgetsCache()[type] : null;

		return !!(config && config.atomic);
	}

	function buildPreviewCss() {
		var editorElements = getV2('editorElements');
		var postId = getDocumentId();

		if (!editorElements || !editorElements.getElements || !postId) {
			return '';
		}

		var data = window.atomicEditorCssData || {};
		var breakpoints = data.breakpoints || {};
		var css = '';

		try {
			editorElements.getElements().forEach(function (container) {
				var model = container.model;
				var settings = container.settings;

				if (!model || !settings || 'function' !== typeof settings.get) {
					return;
				}

				var rawDesktop = settings.get('_custom_css_f_ele_css_desktop');
				var rawTablet = settings.get('_custom_css_f_ele_css_tablet');
				var rawMobile = settings.get('_custom_css_f_ele_css_mobile');

				var desktop = unwrapProp(rawDesktop);
				var tablet = unwrapProp(rawTablet);
				var mobile = unwrapProp(rawMobile);

				var elementCss = '';

				elementCss += desktop.trim();

				if (tablet.trim() && breakpoints.tablet) {
					elementCss += ' @media (max-width: ' + breakpoints.tablet + 'px) { ' + tablet + ' }';
				}

				if (mobile.trim() && breakpoints.mobile) {
					elementCss += ' @media (max-width: ' + breakpoints.mobile + 'px) { ' + mobile + ' }';
				}

				if (!elementCss.trim()) {
					return;
				}

				var elementId = model.get('id');
				var selector;

				// Atomic elements don't render `.elementor-element-{id}` on
				// widgets; they always output `data-interaction-id` instead.
				// Classic elements keep the classic wrapper selector. The
				// transformable-prop shape is used as a fallback so atomic
				// elements are still detected if the widgets cache lookup
				// misses their type.
				if (isAtomicContainer(container) || isTransformableProp(rawDesktop) || isTransformableProp(rawTablet) || isTransformableProp(rawMobile)) {
					var originId = model.get('origin_id');
					var interactionId = originId || elementId;

					selector = '.elementor-' + postId + ' [data-interaction-id="' + interactionId + '"]';
				} else {
					selector = '.elementor-' + postId + ' .elementor-element.elementor-element-' + elementId;
				}

				css += elementCss.replaceAll('selector', selector);
			});
		} catch (e) {
			return '';
		}

		if (!css.trim()) {
			return '';
		}

		if (window.DOMPurify) {
			css = window.DOMPurify.sanitize(css, { CSS: true });
		}

		// Prevent the CSS text from terminating the <style> tag, mirroring
		// Elementor's own `sanitize_inline_css_output()`.
		css = css.replace(/<\/(style)/gi, '<\\/$1');

		return css;
	}

	function getStyleTag() {
		var adapters = getV2('editorV1Adapters');

		if (!adapters || !adapters.getCanvasIframeDocument) {
			return null;
		}

		var doc = adapters.getCanvasIframeDocument();

		if (!doc || !doc.head) {
			return null;
		}

		var tag = doc.getElementById(STYLE_TAG_ID);

		if (!tag) {
			tag = doc.createElement('style');
			tag.id = STYLE_TAG_ID;
			doc.head.appendChild(tag);
		}

		return tag;
	}

	var updateScheduled = false;

	function updatePreviewCss() {
		if (updateScheduled) {
			return;
		}

		updateScheduled = true;

		window.requestAnimationFrame(function () {
			updateScheduled = false;

			var tag = getStyleTag();

			if (tag) {
				tag.textContent = buildPreviewCss();
			}
		});
	}

	var PREVIEW_COMMANDS = [
		'document/elements/set-settings',
		'document/elements/settings',
		'document/elements/create',
		'document/elements/delete',
		'document/elements/duplicate',
		'document/elements/move',
		'document/elements/update',
		'document/elements/import',
		'document/elements/paste',
		'document/elements/reset-style',
		'document/elements/paste-style'
	];

	var previewSyncAttempts = 0;

	function initPreviewSync() {
		var adapters = getV2('editorV1Adapters');

		if (!adapters || !adapters.__privateListenTo || !adapters.commandEndEvent) {
			if (++previewSyncAttempts < MAX_ATTEMPTS) {
				window.setTimeout(initPreviewSync, 300);
			}
			return false;
		}

		var events = PREVIEW_COMMANDS.map(function (name) {
			return adapters.commandEndEvent(name);
		});

		events.push(adapters.commandEndEvent('editor/documents/attach-preview'));

		adapters.__privateListenTo(events, updatePreviewCss);
		updatePreviewCss();

		return true;
	}

	/* ---------------------------------------------------------------------
	 * Style tab injection
	 * ------------------------------------------------------------------ */

	function init() {
		if (injected) {
			return;
		}

		var panel = getPanel();
		var ReactLib = window.React;

		if (
			!panel ||
			!panel.injectIntoStyleTab ||
			!panel.StyleTabSection ||
			!panel.SettingsControl ||
			!panel.useElement ||
			!ReactLib ||
			!registerControlType()
		) {
			if (++attempts < MAX_ATTEMPTS) {
				window.setTimeout(init, 300);
			}
			return;
		}

		initPreviewSync();

		var editorUi = getV2('editorUi');

		function CustomCssFields() {
			var tabState = ReactLib.useState('_custom_css_f_ele_css_desktop');
			var activeBind = tabState[0];
			var setActiveBind = tabState[1];
			var activeField = FIELDS.filter(function (field) {
				return field.bind === activeBind;
			})[0] || FIELDS[0];

			return ReactLib.createElement(
				ReactLib.Fragment,
				null,
				// Usage description, mirroring the classic RAW_HTML descriptor.
				ReactLib.createElement('div', {
					style: {
						fontSize: '12px',
						lineHeight: '1.5',
						color: 'var(--e-a-color-txt-muted, #a4afb7)',
						marginBottom: '8px'
					}
				}, t('description')),
				// Device tabs, mirroring the classic desktop/tablet/mobile tabs.
				ReactLib.createElement('div', {
					role: 'tablist',
					style: {
						display: 'flex',
						gap: '4px',
						marginBottom: '8px',
						borderBottom: '1px solid var(--e-a-color-divider, #d5dadf)'
					}
				}, FIELDS.map(function (field) {
					var isActive = field.bind === activeBind;

					return ReactLib.createElement('button', {
						key: field.bind,
						type: 'button',
						role: 'tab',
						'aria-selected': isActive,
						title: t(field.labelKey),
						onClick: function () {
							setActiveBind(field.bind);
						},
						style: {
							display: 'inline-flex',
							alignItems: 'center',
							justifyContent: 'center',
							width: '32px',
							height: '32px',
							border: 'none',
							background: 'transparent',
							cursor: 'pointer',
							color: isActive
								? 'var(--e-a-color-txt-accent, #39b54a)'
								: 'var(--e-a-color-txt-muted, #a4afb7)',
							borderBottom: isActive
								? '2px solid var(--e-a-color-txt-accent, #39b54a)'
								: '2px solid transparent',
							marginBottom: '-1px'
						}
					}, ReactLib.createElement('i', {
						className: 'eicon ' + field.iconClass,
						'aria-hidden': 'true'
					}));
				})),
				// Only the active device's field is mounted.
				ReactLib.createElement(panel.SettingsControl, {
					key: activeField.bind,
					control: {
						type: 'control',
						value: {
							type: CONTROL_TYPE,
							bind: activeField.bind,
							label: t(activeField.labelKey),
							description: null,
							props: {},
							meta: null
						}
					}
				}),
				// Info notice, mirroring the classic panel alert.
				editorUi && editorUi.InfoAlert
					? ReactLib.createElement(editorUi.InfoAlert, null, t('notice'))
					: ReactLib.createElement('div', {
						style: {
							fontSize: '12px',
							lineHeight: '1.5',
							color: 'var(--e-a-color-txt-muted, #a4afb7)',
							marginTop: '8px'
						}
					}, t('notice'))
			);
		}

		function CustomCssSection() {
			var context;

			try {
				context = panel.useElement();
			} catch (e) {
				return null;
			}

			// Only render for Atomic Elements: the props are injected into the
			// atomic props schema on the PHP side.
			if (
				!context ||
				!context.elementType ||
				!context.elementType.propsSchema ||
				!context.elementType.propsSchema._custom_css_f_ele_css_desktop
			) {
				return null;
			}

			return ReactLib.createElement(panel.StyleTabSection, {
				section: {
					name: 'custom-css-for-elementor',
					title: t('title'),
					component: CustomCssFields
				}
			});
		}

		panel.injectIntoStyleTab({
			id: 'custom-css-for-elementor',
			component: CustomCssSection
		});

		injected = true;
	}

	if ('loading' === document.readyState) {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
}());
