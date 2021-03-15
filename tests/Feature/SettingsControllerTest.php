<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class SettingsControllerTest extends TestCase
{
    use RefreshDatabase;


    /**
     * See if page loads when logged in
     *
     * @return void
     */
    public function testSettings()
    {
        $user = User::factory()->make();
        $response = $this->actingAs($this->createUserWithPermissions(['settings.edit']))->get('/admin/settings');
        $response->assertOk();
    }

    /**
     * See if page is shown when not logged in
     *
     * @return void
     */
    public function testSettingsNotLoggedIn()
    {
        $response = $this->get('/admin/settings');
        $response->assertStatus(302);
    }


    public function testFormUpdateCreateAppName()
    {
        $user = $this->createUserWithPermissions(['settings.edit']);
        $random = Str::random(40);
        //test if fucntion creates if no option is there
        $this->assertDatabaseCount('settings', 0);
        $this->actingAs($user)->post('/admin/settings/app_name', [
            'label' => 'app_name',
            'app_name' => $random,
        ]);

        $this->assertDatabaseCount('settings', 1);

        $this->assertDatabaseHas('settings', [
            'slug' => 'app_name',
            'data' => json_encode(['name' => $random]),
        ]);
        $random = Str::random(40);
        //test if function edits if data is already there

        $this->actingAs($user)->post('/admin/settings/app_name', [
            'label' => 'app_name',
            'app_name' => $random,
        ]);

        $this->assertDatabaseCount('settings', 1);

        $this->assertDatabaseHas('settings', [
            'slug' => 'app_name',
            'data' => json_encode(['name' => $random]),
        ]);
    }

    /**
     * @test
     */
    public function testFormUpdateCreateAppLogo()
    {
        Carbon::setTestNow(now());
        Storage::fake('public');
        $user = $this->createUserWithPermissions(['settings.edit']);
        $random = Str::random(10);
        //test if function creates if no option is there
        $file = UploadedFile::fake()->image($random . '.png');
        $this->assertDatabaseCount('settings', 0);
        $this->actingAs($user)->post('/admin/settings/app_logo', [
            'label' => 'app_logo',
            'app_logo' => $file,
        ]);
        Storage::disk('public')->assertExists('logos/' . $file->hashName());
        $this->assertDatabaseCount('settings', 1);
        $this->assertDatabaseHas('settings', [
            'slug' => 'app_logo',
            'data' => json_encode(['path' => '/storage/logos/' . $file->hashName()]),
        ]);
        $random = Str::random(10);
        Carbon::setTestNow(now());
        //test if function overwrites if option is already there
        $file = UploadedFile::fake()->image($random . '.jpg');
        $this->actingAs($user)->post('/admin/settings/app_logo', [
            'label' => 'app_logo',
            'app_logo' => $file,
        ]);
        Storage::disk('public')->assertExists('logos/' . $file->hashName());
        $this->assertDatabaseCount('settings', 1);
        $this->assertDatabaseHas('settings', [
            'slug' => 'app_logo',
            'data' => json_encode(['path' => '/storage/logos/' . $file->hashName()]),
        ]);
    }

    public function testFormUpdateCreateAppConfig()
    {
        $user = $this->createUserWithPermissions(['settings.edit']);
        $random = Str::random(40);
        //test if fucntion creates if no option is there
        $this->assertDatabaseCount('settings', 0);
        $this->actingAs($user)->post('/admin/settings/app_config', [
            'label' => 'app_config',
            'client_secret' => $random,
            'client_id' => $random,
            'redirect_uri' => $random,
            'tenant' => $random
        ]);

        $this->assertDatabaseCount('settings', 1);

        $this->assertDatabaseHas('settings', [
            'slug' => 'app_config',
            'data' => json_encode([
                'secret' => $random,
                'id' => $random,
                'uri' => $random,
                'tenant' => $random
            ]),
        ]);
        $random = Str::random(40);
        //test if function edits if data is already there

        $this->actingAs($user)->post('/admin/settings/app_config', [
            'label' => 'app_config',
            'client_secret' => $random,
            'client_id' => $random,
            'redirect_uri' => $random,
            'tenant' => $random
        ]);

        $this->assertDatabaseCount('settings', 1);

        $this->assertDatabaseHas('settings', [
            'slug' => 'app_config',
            'data' => json_encode([
                'secret' => $random,
                'id' => $random,
                'uri' => $random,
                'tenant' => $random
            ]),
        ]);
    }
}
