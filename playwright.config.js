import { defineConfig, devices } from '@playwright/test';

const baseURL = process.env.E2E_BASE_URL || 'http://localhost:85';
const startLocalServer = process.env.E2E_START_SERVER === 'true';

export default defineConfig({
    testDir: './tests/e2e',
    fullyParallel: false,
    forbidOnly: Boolean(process.env.CI),
    retries: process.env.CI ? 2 : 0,
    workers: 1,
    reporter: [['list'], ['html', { open: 'never' }]],
    use: {
        baseURL,
        channel: process.env.E2E_BROWSER_CHANNEL || 'chrome',
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
        video: 'off',
        ...devices['Desktop Chrome'],
    },
    webServer: startLocalServer ? {
        command: 'php artisan serve --host=127.0.0.1 --port=8001',
        url: 'http://127.0.0.1:8001',
        reuseExistingServer: true,
        timeout: 120000,
    } : undefined,
});
