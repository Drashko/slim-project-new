import $ from 'jquery';
import moment from 'moment';

window.$ = $;
window.jQuery = $;
window.moment = moment;

import 'jquery-ui-dist/jquery-ui.min.js';
import 'select2/dist/js/select2.min.js';
import 'daterangepicker/daterangepicker.js';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

import { OverlayScrollbars } from 'overlayscrollbars';

window.OverlayScrollbars = OverlayScrollbars;

(function registerOverlayScrollbarsPlugin(jq) {
  if (!jq || jq.fn.overlayScrollbars) {
    return;
  }

  jq.fn.overlayScrollbars = function overlayScrollbarsPlugin(options) {
    if (options === undefined) {
      return this.first().data('overlayScrollbars') ?? null;
    }

    this.each(function attachInstance() {
      const instance = OverlayScrollbars(this, options);
      jq(this).data('overlayScrollbars', instance);
    });

    return this;
  };
})(window.jQuery);

import '@fortawesome/fontawesome-free/css/all.min.css';
import 'jquery-ui-dist/jquery-ui.min.css';
import 'overlayscrollbars/styles/overlayscrollbars.min.css';
import 'daterangepicker/daterangepicker.css';
import 'select2/dist/css/select2.min.css';
import 'bootstrap/dist/css/bootstrap.min.css';

import '../../public/digiboard/assets/css/style.css';
import '../../public/digiboard/assets/css/blue-color.css';
import '../../public/digiboard/assets/css/custom-overrides.css';

import '../../public/digiboard/assets/js/main.js';
