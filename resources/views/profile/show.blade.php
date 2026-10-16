<x-app-layout title="Account Settings">
    <x-slot name="header">
        <div class="account-settings-heading">
            <div>
                <div class="account-settings-eyebrow">Account settings</div>
                <h1>Keep your account ready</h1>
                <p>Update your personal details, password, security options, and active sessions in one place.</p>
            </div>
            <span class="account-status-badge">
                <span class="account-status-dot" aria-hidden="true"></span>
                Account active
            </span>
        </div>
    </x-slot>

    <div class="account-settings-page">
        <div class="account-settings-guide" aria-labelledby="account-settings-guide-title">
            <div class="account-guide-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M12 3 20 6v5c0 5.1-3.4 8.6-8 10-4.6-1.4-8-4.9-8-10V6l8-3Z" />
                    <path d="m8.5 12 2.2 2.2 4.8-5" />
                </svg>
            </div>
            <div class="account-guide-copy">
                <div class="account-guide-kicker">Quick setup guide</div>
                <h2 id="account-settings-guide-title">Complete these four checks</h2>
                <p>Confirm your profile, choose a strong password, optionally enable MFA, then review other active sessions.</p>
                <div class="account-guide-steps" aria-label="Account settings sections">
                    <a href="#profile-information"><span>1</span>Profile</a>
                    <a href="#password-security"><span>2</span>Password</a>
                    <a href="#multi-factor-security"><span>3</span>MFA optional</a>
                    <a href="#browser-sessions"><span>4</span>Sessions</a>
                </div>
            </div>
            <a class="account-guide-link" href="{{ route('help.user-guide') }}">
                Open user guide
                <span aria-hidden="true">&rarr;</span>
            </a>
        </div>

        <div class="account-settings-forms">
            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                <section id="profile-information" class="account-settings-section">
                    @livewire('profile.update-profile-information-form')
                </section>

                <x-section-border />
            @endif

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                <section id="password-security" class="account-settings-section">
                    @livewire('profile.update-password-form')
                </section>

                <x-section-border />
            @endif

            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <section id="multi-factor-security" class="account-settings-section">
                    @livewire('profile.two-factor-authentication-form')
                </section>

                <x-section-border />
            @endif

            <section id="browser-sessions" class="account-settings-section">
                @livewire('profile.logout-other-browser-sessions-form')
            </section>

            @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                <x-section-border />

                <section class="account-settings-section">
                    @livewire('profile.delete-user-form')
                </section>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('click', (event) => {
            const toggle = event.target.closest('[data-password-toggle]');

            if (!toggle || !toggle.closest('.account-settings-page')) {
                return;
            }

            const input = document.getElementById(toggle.dataset.passwordToggle);

            if (!input) {
                return;
            }

            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            toggle.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
            toggle.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
            toggle.setAttribute('title', isHidden ? 'Hide password' : 'Show password');
            toggle.querySelector('[data-password-icon="show"]')?.toggleAttribute('hidden', isHidden);
            toggle.querySelector('[data-password-icon="hide"]')?.toggleAttribute('hidden', !isHidden);
        });
    </script>
</x-app-layout>
