<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * Seed default site settings without overwriting edited values.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'site_name', 'value' => 'Chapung Art', 'type' => 'text', 'group' => 'general'],
            ['key' => 'site_description', 'value' => 'Galeri seni, fotografi budaya, dan media kreatif Papua Selatan.', 'type' => 'textarea', 'group' => 'general'],
            ['key' => 'logo', 'value' => null, 'type' => 'image', 'group' => 'brand'],
            ['key' => 'favicon', 'value' => null, 'type' => 'image', 'group' => 'brand'],
            ['key' => 'email', 'value' => (string) config('chapung.emails.info'), 'type' => 'email', 'group' => 'contact'],
            ['key' => 'whatsapp', 'value' => '6281234567890', 'type' => 'phone', 'group' => 'contact'],
            ['key' => 'instagram', 'value' => 'https://instagram.com/chapungart', 'type' => 'url', 'group' => 'social'],
            ['key' => 'facebook', 'value' => 'https://facebook.com/chapungart', 'type' => 'url', 'group' => 'social'],
            ['key' => 'tiktok', 'value' => 'https://www.tiktok.com/@chapungart', 'type' => 'url', 'group' => 'social'],
            ['key' => 'youtube', 'value' => 'https://youtube.com/@chapungart', 'type' => 'url', 'group' => 'social'],
            ['key' => 'address', 'value' => 'Merauke, Papua Selatan', 'type' => 'textarea', 'group' => 'contact'],
            ['key' => 'google_maps_url', 'value' => null, 'type' => 'url', 'group' => 'contact'],
            ['key' => 'currency', 'value' => 'IDR', 'type' => 'text', 'group' => 'commerce'],
            ['key' => 'timezone', 'value' => 'Asia/Jayapura', 'type' => 'text', 'group' => 'general'],
        ];

        foreach ($settings as $setting) {
            SiteSetting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
