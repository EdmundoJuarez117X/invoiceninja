/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!******************************************************!*\
  !*** ./resources/js/clients/payments/stripe-acss.js ***!
  \******************************************************/
var _document$querySelect, _document$querySelect2, _document$querySelect3, _document$querySelect4;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/**
 * Invoice Ninja (https://invoiceninja.com)
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2021. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://www.elastic.co/licensing/elastic-license 
 */
var ProcessACSS = /*#__PURE__*/function () {
  function ProcessACSS(key, stripeConnect) {
    var _this = this;

    _classCallCheck(this, ProcessACSS);

    _defineProperty(this, "setupStripe", function () {
      if (_this.stripeConnect) {
        // this.stripe.stripeAccount = this.stripeConnect;
        _this.stripe = Stripe(_this.key, {
          stripeAccount: _this.stripeConnect
        });
      } else {
        _this.stripe = Stripe(_this.key);
      }

      return _this;
    });

    _defineProperty(this, "handle", function () {
      document.getElementById('pay-now').addEventListener('click', function (e) {
        var errors = document.getElementById('errors');

        if (document.getElementById('acss-name').value === "") {
          document.getElementById('acss-name').focus();
          errors.textContent = document.querySelector('meta[name=translation-name-required]').content;
          errors.hidden = false;
          return;
        }

        if (document.getElementById('acss-email-address').value === "") {
          document.getElementById('acss-email-address').focus();
          errors.textContent = document.querySelector('meta[name=translation-email-required]').content;
          errors.hidden = false;
          return;
        }

        document.getElementById('pay-now').disabled = true;
        document.querySelector('#pay-now > svg').classList.remove('hidden');
        document.querySelector('#pay-now > span').classList.add('hidden');

        _this.stripe.confirmAcssDebitPayment(document.querySelector('meta[name=pi-client-secret').content, {
          payment_method: {
            billing_details: {
              name: document.getElementById("acss-name").value,
              email: document.getElementById("acss-email-address").value
            }
          }
        }).then(function (result) {
          if (result.error) {
            return _this.handleFailure(result.error.message);
          }

          return _this.handleSuccess(result);
        });
      });
    });

    this.key = key;
    this.errors = document.getElementById('errors');
    this.stripeConnect = stripeConnect;
  }

  _createClass(ProcessACSS, [{
    key: "handleSuccess",
    value: function handleSuccess(result) {
      document.querySelector('input[name="gateway_response"]').value = JSON.stringify(result.paymentIntent);
      document.getElementById('server-response').submit();
    }
  }, {
    key: "handleFailure",
    value: function handleFailure(message) {
      var errors = document.getElementById('errors');
      errors.textContent = '';
      errors.textContent = message;
      errors.hidden = false;
      document.getElementById('pay-now').disabled = false;
      document.querySelector('#pay-now > svg').classList.add('hidden');
      document.querySelector('#pay-now > span').classList.remove('hidden');
    }
  }]);

  return ProcessACSS;
}();

var publishableKey = (_document$querySelect = (_document$querySelect2 = document.querySelector('meta[name="stripe-publishable-key"]')) === null || _document$querySelect2 === void 0 ? void 0 : _document$querySelect2.content) !== null && _document$querySelect !== void 0 ? _document$querySelect : '';
var stripeConnect = (_document$querySelect3 = (_document$querySelect4 = document.querySelector('meta[name="stripe-account-id"]')) === null || _document$querySelect4 === void 0 ? void 0 : _document$querySelect4.content) !== null && _document$querySelect3 !== void 0 ? _document$querySelect3 : '';
new ProcessACSS(publishableKey, stripeConnect).setupStripe().handle();
/******/ })()
;