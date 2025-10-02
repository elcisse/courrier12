(function () {
    function ready(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

    ready(function () {
        var typeSelect = document.querySelector('select[name="type"]');
        if (!typeSelect) {
            return;
        }

        function toggleField(fieldName, isVisible) {
            var field = document.querySelector('[data-field="' + fieldName + '"]');
            if (!field) {
                return;
            }

            var input = field.querySelector('input');
            field.style.display = isVisible ? '' : 'none';

            if (!isVisible && input) {
                input.value = '';
            }
        }

        function syncDateFields() {
            var currentType = typeSelect.value;
            toggleField('date_reception', currentType === 'ENTRANT');
            toggleField('date_envoi', currentType === 'SORTANT');
        }

        typeSelect.addEventListener('change', syncDateFields);
        syncDateFields();
    });
})();