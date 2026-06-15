<?php

namespace Tests\Feature;

use App\Models\CompanySetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CompanySettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_update_company_settings(): void
    {
        Storage::fake('public');
        Role::findOrCreate('Super Admin');

        $user = User::factory()->create(['role' => 'admin']);
        $user->assignRole('Super Admin');

        $response = $this->actingAs($user)->put(route('settings.company.update'), [
            'company_name' => 'Acme Holdings',
            'company_short_name' => 'Acme',
            'brand_mark' => 'AC',
            'logo' => UploadedFile::fake()->image('logo.png', 120, 80),
            'product_name' => 'Acme Goals',
            'tagline' => 'Track measurable work across teams.',
            'email' => 'hello@acme.test',
            'phone' => '+256 700 000000',
            'website' => 'https://acme.test',
            'address' => 'Kampala',
        ]);

        $response->assertRedirect();

        $this->assertSame('Acme Holdings', CompanySetting::current()->company_name);
        $this->assertSame('AC', CompanySetting::current()->brand_mark);
        $this->assertNotNull(CompanySetting::current()->logo_path);
        Storage::disk('public')->assertExists(CompanySetting::current()->logo_path);
    }
}
