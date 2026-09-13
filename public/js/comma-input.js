/**
 * Auto-formats any input[data-comma-input] with thousand separators as
 * the user types, and strips them back out right before its form
 * submits (or on demand via CommaInput.strip) so the server always
 * receives a plain numeric string.
 */
(function () {
    function stripCommas(str) {
        return (str || '').toString().replace(/,/g, '');
    }

    function formatWithCommas(str) {
        str = (str || '').toString();
        if (!str) return str;

        var negative = str.charAt(0) === '-';
        if (negative) str = str.slice(1);

        var parts = str.split('.');
        var intPart = parts[0].replace(/[^\d]/g, '');
        intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

        var result = intPart;
        if (parts.length > 1) {
            result += '.' + parts[1].replace(/[^\d]/g, '');
        }

        return (negative ? '-' : '') + result;
    }

    function initInput(input) {
        if (input.dataset.commaInit) return;
        input.dataset.commaInit = '1';

        input.type = 'text';
        input.setAttribute('inputmode', 'decimal');
        input.setAttribute('autocomplete', 'off');

        if (input.value) {
            input.value = formatWithCommas(input.value);
        }

        input.addEventListener('input', function () {
            var cursorFromEnd = input.value.length - (input.selectionStart || 0);
            input.value = formatWithCommas(input.value);
            var pos = Math.max(0, input.value.length - cursorFromEnd);
            input.setSelectionRange(pos, pos);
        });

        var form = input.closest('form');
        if (form && !form.dataset.commaSubmitBound) {
            form.dataset.commaSubmitBound = '1';
            form.addEventListener('submit', function () {
                form.querySelectorAll('input[data-comma-input]').forEach(function (i) {
                    i.value = stripCommas(i.value);
                });
            });
        }
    }

    function init() {
        document.querySelectorAll('input[data-comma-input]').forEach(initInput);
    }

    document.addEventListener('DOMContentLoaded', init);

    window.CommaInput = {
        init: init,
        format: formatWithCommas,
        strip: stripCommas,
    };
})();
