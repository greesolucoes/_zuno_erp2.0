(function () {
  'use strict';

  function toggleView(mode) {
    var tableWrapper = document.getElementById('vet-atendimentos-table-wrapper');
    var cardsWrapper = document.getElementById('vet-atendimentos-cards');
    var buttons = document.querySelectorAll('[data-view-mode]');

    if (!tableWrapper || !cardsWrapper) {
      return;
    }

    if (mode === 'cards') {
      tableWrapper.classList.add('d-none');
      cardsWrapper.classList.remove('d-none');
    } else {
      cardsWrapper.classList.add('d-none');
      tableWrapper.classList.remove('d-none');
    }

    buttons.forEach(function (button) {
      if (button.getAttribute('data-view-mode') === mode) {
        button.classList.add('active');
      } else {
        button.classList.remove('active');
      }
    });
  }

  function bindViewSwitch() {
    document.querySelectorAll('[data-view-mode]').forEach(function (button) {
      button.addEventListener('click', function () {
        toggleView(button.getAttribute('data-view-mode'));
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    bindViewSwitch();
  });
})();
