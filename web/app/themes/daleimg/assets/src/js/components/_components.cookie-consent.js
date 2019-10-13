(function() {

  function cookieConsentBanner() {

    function $(selector) {
      return document.querySelector(selector);
    }
  
    var $cookieConsentBanner = $('.js-cookie-consent');

    // bail if the consent banner isnt in the HTML.
    if (!$cookieConsentBanner) {
      return;
    }
  
    /**
     * Check if the user has consented.
     */
    function getConsented() {
      return Cookies.get('cookie-consent');
    }
  
    /**
     * Set the cookie to say the user has consented to cookies. Expires after a year.
     * And dismiss the banner.
     */
    function setConsented() {
      Cookies.set('cookie-consent', true, { expires: 365 });
    }
  
    function showBanner() {
      var $cookieConsentBanner = $('.js-cookie-consent');
  
      $cookieConsentBanner.style.display = '';
      $('.js-cookie-consent-dismiss').addEventListener('click', dismissBanner);
    }
  
    function dismissBanner() {
      $cookieConsentBanner.style.display = 'none';
      setConsented();
    }
  
    /**
     * Function to run on page load to check if user has
     * accepted cookies and show banner if not.
     */
    function init() {
      if (getConsented()) {
        return;
      }
  
      showBanner();
    }

    init();

  }

  window.addEventListener('load', cookieConsentBanner);

})();
