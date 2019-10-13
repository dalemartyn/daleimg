(function () {
  if (navigator.userAgent.indexOf('Android') > -1) {
    document.documentElement.classList.add('t-android');
  }

  function createButton() {
    var button = document.createElement('button');
    button.classList.add('c-site-navigation__menu-button');
    button.setAttribute('aria-expanded', false);
    button.setAttribute('aria-label', 'Main Menu');
    button.setAttribute('aria-controls', 'site-menu');
    button.setAttribute('id', 'menu-menu-toggle');
    return button;
  }

  function createOverlay() {
    var overlay = document.createElement('label');
    overlay.classList.add('c-site-navigation__menu-button-label');
    overlay.setAttribute('aria-hidden', true);
    overlay.setAttribute('for', 'menu-menu-toggle');
    return overlay;
  }

  var menuLink = document.querySelector('.js-site-nav-menu-link');
  var button = createButton();
  var overlay = createOverlay();

  var menuLinkParent = menuLink.parentElement;
  var navList = menuLinkParent.nextElementSibling;
  menuLinkParent.replaceChild(button, menuLink);
  menuLinkParent.appendChild(overlay);

  var transitioning = false;
  var expanded = false;

  function toggleMenu() {
    if (!transitioning) {
      transitioning = true;

      if (expanded) {
        hideMenu();
      } else {
        showMenu();
      }
    }
  }

  function hideMenu() {
    navList.classList.add('is-offscreen');
    document.body.classList.remove('menu-active');
    expanded = false;
    button.classList.remove('is-open');

    navList.addEventListener('transitionend', function onHidden(event) {
      if (event.propertyName == 'opacity') {
        transitioning = false;
        navList.classList.add('is-inactive');
        navList.removeEventListener('transitionend', onHidden);
        button.setAttribute('aria-expanded', false);
        button.setAttribute('aria-label', 'Main Menu');
      }
    });
  }

  function showMenu() {
    navList.classList.remove('is-inactive');
    button.classList.add('is-open');
    window.scroll({
      top: 0,
      behavior: 'smooth'
    });

    requestAnimationFrame(function () {
      requestAnimationFrame(function () {
        navList.classList.remove('is-offscreen');
        button.setAttribute('aria-expanded', true);
        document.body.classList.add('menu-active');
        expanded = true;
        button.setAttribute('aria-label', 'Close Menu');

        navList.addEventListener('transitionend', function onShown(event) {
          if (event.propertyName == 'opacity') {
            transitioning = false;
            navList.removeEventListener('transitionend', onShown);
          }
        });
      });
    });
  }

  // navList.addEventListener('transitionend', function(event) {

  //   var expanded = button.getAttribute('aria-expanded') === 'true' || false;
  //   button.setAttribute('aria-expanded', !expanded);

  // });

  button.addEventListener('click', toggleMenu);

})();
