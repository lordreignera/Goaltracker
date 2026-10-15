import { expect, test } from '@playwright/test';

const email = process.env.E2E_EMAIL || 'superadmin@arm.test';
const password = process.env.E2E_PASSWORD || 'Sysadmin2@@2';

async function signIn(page) {
    await page.goto('/login');
    await page.getByLabel('Email').fill(email);
    await page.getByLabel('Password').fill(password);
    await page.getByRole('button', { name: 'Login' }).click();
    await expect(page).toHaveURL(/dashboard/);
}

async function openGoalModal(page) {
    await page.getByRole('button', { name: 'Create Goal' }).click();
    const modal = page.locator('#dashboardCreateGoalModal');
    await expect(modal).toBeVisible();

    await modal.locator('select[name="quarter_id"]').selectOption({ index: 1 });
    await modal.locator('select[name="goal_pillar_id"]').selectOption({ index: 1 });
    await modal.locator('[data-department-picker-toggle]').click();
    await modal.locator('[data-department-select-all]').check();

    const objective = modal.locator('[data-dashboard-objective]').first();
    await objective.locator('input[name*="[title]"]').fill(`Browser workflow goal ${Date.now()}`);
    await objective.locator('input[name*="[weight]"]').fill('100');
    await objective.locator('select[data-dashboard-weeks]').selectOption('1');
    const quarterStart = await objective.locator('input[data-dashboard-start]').getAttribute('min');
    await objective.locator('input[data-dashboard-start]').fill(quarterStart);
    await objective.locator('input[name*="[key_activities]"]').first().fill('Complete the browser workflow check.');
    await objective.locator('textarea[name*="[specific_output]"]').fill('A saved goal that can be submitted and reported on.');
    const invalidFields = await modal.locator('form').evaluate((form) => Array.from(form.elements)
        .filter((field) => !field.checkValidity())
        .map((field) => ({ name: field.name, message: field.validationMessage })));
    expect(invalidFields).toEqual([]);

    return modal;
}

test('user can create a draft, submit it, and unlock reporting only after submission', async ({ page }) => {
    await signIn(page);
    await expect(page.getByText('Activity Pillars and Work Plan Goals')).toBeVisible();

    const modal = await openGoalModal(page);
    await modal.getByRole('button', { name: 'Save Draft' }).click();
    await expect(page).toHaveURL(/goals\/\d+$/);
    await expect(page.getByText('Draft')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Submit Report' })).toHaveCount(0);

    const submitResponse = page.waitForResponse((response) => response.url().match(/goals\/\d+\/submit$/) && response.request().method() === 'POST');
    await page.getByRole('button', { name: 'Submit Goal' }).click();
    const response = await submitResponse;
    expect(response.status()).toBe(302);
    await expect(page.locator('strong').filter({ hasText: 'Status:' }).locator('..')).toContainText('Submitted');
    await expect(page.getByRole('button', { name: 'Submit Report' })).toBeVisible();
});

test('dashboard exposes the notification center and goal creation entry point', async ({ page }) => {
    await signIn(page);
    await expect(page.getByRole('link', { name: 'Notifications' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Create Goal' })).toBeVisible();
    await page.getByRole('link', { name: 'Notifications' }).click();
    await expect(page).toHaveURL(/notifications/);
    await expect(page.getByRole('heading', { name: 'Notifications' })).toBeVisible();
});
