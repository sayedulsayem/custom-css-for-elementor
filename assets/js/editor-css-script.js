jQuery(window).on('elementor/frontend/init', function () {
  function addCustomCss(css, context) {
    if (!context) {
      return;
    }
    var customCss = '';

    var model = context.model,
      customCssDesktop = model.get('settings').get('_custom_css_f_ele_css_desktop'),
      customCssTablet = model.get('settings').get('_custom_css_f_ele_css_tablet'),
      customCssMobile = model.get('settings').get('_custom_css_f_ele_css_mobile');

    customCss += customCssDesktop ? customCssDesktop : '';
    customCss += customCssTablet ? ' @media (max-width: 768px) { ' + customCssTablet + '}' : '';
    customCss += customCssMobile ? ' @media (max-width: 425px) { ' + customCssMobile + '}' : '';

    if (!customCss) {
      return;
    }

    var selector = '.elementor-' + modelData.postID + ' .elementor-element.elementor-element-' + model.get('id');

    if ('document' === model.get('elType')) {
      selector = elementor.config.document.settings.cssWrapperSelector;
    }

    if (customCss) {
      css += customCss.replaceAll('selector', selector);
    }

    return DOMPurify.sanitize(css, { CSS: true });
  }

  if ((typeof elementor) !== 'undefined') {
    elementor.hooks.addFilter('editor/style/styleText', addCustomCss);
  }
});