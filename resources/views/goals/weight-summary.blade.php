@once
    <style>
        .objective-weight-summary {
            border: 1px solid #f59e0b;
            border-radius: 8px;
            background: #fffbeb;
            padding: 12px 14px;
        }

        .objective-weight-summary.is-valid {
            border-color: #10b981;
            background: #ecfdf5;
        }

        .objective-weight-summary.is-over {
            border-color: #ef4444;
            background: #fef2f2;
        }

        .objective-weight-summary .progress {
            height: 7px;
        }

        .objective-weight-summary.is-over .progress-bar {
            background: #ef4444;
        }

        .objective-weight-summary.is-valid .progress-bar {
            background: #10b981;
        }
    </style>
@endonce

<div class="objective-weight-summary mb-3" data-weight-summary role="status" aria-live="polite">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-2">
        <div class="fw-bold">Objective Weight Total</div>
        <strong data-weight-total>0%</strong>
    </div>
    <div class="progress mb-2">
        <div class="progress-bar" data-weight-progress style="width: 0%"></div>
    </div>
    <div class="small" data-weight-message>
        If there is one objective, give it 100%. If there are several, distribute their weights so the total is exactly 100%.
    </div>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-weight-form]').forEach((form) => {
            const summary = form.querySelector('[data-weight-summary]');
            const totalLabel = form.querySelector('[data-weight-total]');
            const progress = form.querySelector('[data-weight-progress]');
            const message = form.querySelector('[data-weight-message]');
            const submitButtons = form.querySelectorAll('[data-weight-submit]');

            if (! summary || ! totalLabel || ! progress || ! message || submitButtons.length === 0) {
                return;
            }

            function objectiveWeights() {
                return Array.from(form.querySelectorAll('input[name^="objectives"][name$="[weight]"]'));
            }

            function syncWeightTotal() {
                const inputs = objectiveWeights();
                const total = inputs.reduce((sum, input) => sum + Math.max(0, Number(input.value) || 0), 0);
                const difference = Math.abs(100 - total);

                totalLabel.textContent = `${total}%`;
                progress.style.width = `${Math.min(total, 100)}%`;
                summary.classList.toggle('is-valid', total === 100);
                summary.classList.toggle('is-over', total > 100);
                submitButtons.forEach((button) => {
                    button.disabled = total !== 100;
                });

                if (total === 100) {
                    message.textContent = 'Ready to save. The objective weights total exactly 100%.';
                } else if (total > 100) {
                    message.textContent = `The total is ${difference}% over. Reduce the objective weights before saving.`;
                } else if (inputs.length === 1) {
                    message.textContent = `A single objective must weigh 100%. Add the remaining ${difference}%.`;
                } else {
                    message.textContent = `${difference}% remains. Distribute it across the objectives so the total reaches 100%.`;
                }
            }

            form.addEventListener('input', (event) => {
                if (event.target.matches('input[name^="objectives"][name$="[weight]"]')) {
                    syncWeightTotal();
                }
            });

            form.addEventListener('click', (event) => {
                if (event.target.matches('[data-add-objective], [data-remove-objective], [data-dashboard-add-objective], [data-dashboard-remove-objective]')) {
                    setTimeout(syncWeightTotal, 0);
                }
            });

            syncWeightTotal();
            });
        });
    </script>
@endonce
