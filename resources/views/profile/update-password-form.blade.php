<x-form-section submit="updatePassword" class="account-settings-section-grid">
    <x-slot name="title">
        {{ __('Update Password') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Ensure your account is using a long, random password to stay secure.') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-label for="current_password" value="{{ __('Current Password') }}" />
            <div class="profile-password-wrap mt-1">
                <x-input id="current_password" type="password" class="profile-password-input block w-full" wire:model="state.current_password" autocomplete="current-password" />
                <button type="button" class="profile-password-toggle" data-password-toggle="current_password" aria-label="Show password" aria-pressed="false" title="Show password">
                    <span data-password-icon="show">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" /><circle cx="12" cy="12" r="2.5" /></svg>
                    </span>
                    <span data-password-icon="hide" hidden>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 3 21 21" /><path d="M10.6 6.2A10.6 10.6 0 0 1 12 6c6 0 9.5 6 9.5 6a16.8 16.8 0 0 1-3.2 3.6M6.2 6.3C3.8 8.1 2.5 12 2.5 12s3.5 6 9.5 6a9.7 9.7 0 0 0 3.2-.5" /><path d="M9.9 9.9a2.5 2.5 0 0 0 3.5 3.5" /></svg>
                    </span>
                    <span class="visually-hidden">Show password</span>
                </button>
            </div>
            <x-input-error for="current_password" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-label for="password" value="{{ __('New Password') }}" />
            <div class="profile-password-wrap mt-1">
                <x-input id="password" type="password" class="profile-password-input block w-full" wire:model="state.password" autocomplete="new-password" />
                <button type="button" class="profile-password-toggle" data-password-toggle="password" aria-label="Show password" aria-pressed="false" title="Show password">
                    <span data-password-icon="show">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" /><circle cx="12" cy="12" r="2.5" /></svg>
                    </span>
                    <span data-password-icon="hide" hidden>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 3 21 21" /><path d="M10.6 6.2A10.6 10.6 0 0 1 12 6c6 0 9.5 6 9.5 6a16.8 16.8 0 0 1-3.2 3.6M6.2 6.3C3.8 8.1 2.5 12 2.5 12s3.5 6 9.5 6a9.7 9.7 0 0 0 3.2-.5" /><path d="M9.9 9.9a2.5 2.5 0 0 0 3.5 3.5" /></svg>
                    </span>
                    <span class="visually-hidden">Show password</span>
                </button>
            </div>
            <div class="profile-field-hint">Use at least 8 characters with a mix of letters, numbers, and symbols.</div>
            <x-input-error for="password" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
            <div class="profile-password-wrap mt-1">
                <x-input id="password_confirmation" type="password" class="profile-password-input block w-full" wire:model="state.password_confirmation" autocomplete="new-password" />
                <button type="button" class="profile-password-toggle" data-password-toggle="password_confirmation" aria-label="Show password" aria-pressed="false" title="Show password">
                    <span data-password-icon="show">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" /><circle cx="12" cy="12" r="2.5" /></svg>
                    </span>
                    <span data-password-icon="hide" hidden>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 3 21 21" /><path d="M10.6 6.2A10.6 10.6 0 0 1 12 6c6 0 9.5 6 9.5 6a16.8 16.8 0 0 1-3.2 3.6M6.2 6.3C3.8 8.1 2.5 12 2.5 12s3.5 6 9.5 6a9.7 9.7 0 0 0 3.2-.5" /><path d="M9.9 9.9a2.5 2.5 0 0 0 3.5 3.5" /></svg>
                    </span>
                    <span class="visually-hidden">Show password</span>
                </button>
            </div>
            <x-input-error for="password_confirmation" class="mt-2" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="me-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button>
            {{ __('Save') }}
        </x-button>
    </x-slot>
</x-form-section>
