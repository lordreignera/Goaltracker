import './bootstrap';
import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;

function setupReportPeriodPicker(picker) {
    const form = picker.closest('form');
    const frequencySelect = form?.querySelector('[data-report-frequency]');
    const periodSelect = picker.querySelector('[data-report-period]');
    const reportDateInput = picker.querySelector('[data-report-date]');
    const helpText = picker.querySelector('[data-report-period-help]');
    const periods = JSON.parse(picker.dataset.reportPeriods || '{}');
    const selectedPeriod = picker.dataset.selectedPeriod || '';

    if (!frequencySelect || !periodSelect || !reportDateInput) {
        return;
    }

    const syncPeriod = (preferredPeriod = '') => {
        const frequency = frequencySelect.value;
        const options = Array.isArray(periods[frequency]) ? periods[frequency] : [];

        periodSelect.innerHTML = '';

        options.forEach((period) => {
            const option = document.createElement('option');
            option.value = period.start;
            option.textContent = period.label;
            option.dataset.end = period.end;
            option.dataset.reportDate = period.report_date;
            periodSelect.appendChild(option);
        });

        if (!options.length) {
            periodSelect.required = false;
            reportDateInput.value = '';

            if (helpText) {
                helpText.textContent = 'No reporting periods are available for this cadence.';
            }

            return;
        }

        const periodToSelect = options.some((period) => period.start === preferredPeriod)
            ? preferredPeriod
            : options[0].start;

        periodSelect.value = periodToSelect;
        periodSelect.required = true;

        const selected = options.find((period) => period.start === periodSelect.value) || options[0];
        reportDateInput.value = selected.report_date;

        if (helpText) {
            helpText.textContent = `${selected.label} selected. The report date is set automatically.`;
        }
    };

    frequencySelect.addEventListener('change', () => syncPeriod());
    periodSelect.addEventListener('change', () => syncPeriod(periodSelect.value));
    syncPeriod(selectedPeriod);
}

document.querySelectorAll('[data-report-period-picker]').forEach(setupReportPeriodPicker);
