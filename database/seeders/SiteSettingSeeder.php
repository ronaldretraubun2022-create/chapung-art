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
        $bankAccounts = json_encode(config('chapung.bank_accounts', []), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $contactNumbers = json_encode(config('chapung.contact_numbers', []), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $settings = [
            ['key' => 'site_name', 'value' => 'Chapung Art', 'type' => 'text', 'group' => 'general'],
            ['key' => 'site_description', 'value' => 'Galeri seni, fotografi budaya, dan media kreatif Papua Selatan.', 'type' => 'textarea', 'group' => 'general'],
            ['key' => 'logo', 'value' => null, 'type' => 'image', 'group' => 'brand'],
            ['key' => 'favicon', 'value' => null, 'type' => 'image', 'group' => 'brand'],
            ['key' => 'email', 'value' => (string) config('chapung.emails.info'), 'type' => 'email', 'group' => 'contact'],
            ['key' => 'phone', 'value' => (string) config('chapung.contact_phone'), 'type' => 'phone', 'group' => 'contact'],
            ['key' => 'whatsapp', 'value' => (string) config('chapung.contact_whatsapp'), 'type' => 'phone', 'group' => 'contact'],
            ['key' => 'contact_numbers', 'value' => $contactNumbers, 'type' => 'textarea', 'group' => 'contact'],
            ['key' => 'instagram', 'value' => 'https://instagram.com/chapungart', 'type' => 'url', 'group' => 'social'],
            ['key' => 'facebook', 'value' => 'https://facebook.com/chapungart', 'type' => 'url', 'group' => 'social'],
            ['key' => 'tiktok', 'value' => 'https://www.tiktok.com/@chapungart', 'type' => 'url', 'group' => 'social'],
            ['key' => 'youtube', 'value' => 'https://youtube.com/@chapungart', 'type' => 'url', 'group' => 'social'],
            ['key' => 'address', 'value' => (string) config('chapung.address'), 'type' => 'textarea', 'group' => 'contact'],
            ['key' => 'google_maps_url', 'value' => (string) config('chapung.google_maps_url'), 'type' => 'url', 'group' => 'contact'],
            ['key' => 'currency', 'value' => 'IDR', 'type' => 'text', 'group' => 'commerce'],
            ['key' => 'bank_accounts', 'value' => $bankAccounts, 'type' => 'textarea', 'group' => 'commerce'],
            ['key' => 'timezone', 'value' => 'Asia/Jayapura', 'type' => 'text', 'group' => 'general'],
        ];

        foreach ($settings as $setting) {
            if (in_array($setting['key'], ['address', 'bank_accounts', 'phone', 'whatsapp', 'contact_numbers'], true)) {
                SiteSetting::updateOrCreate(['key' => $setting['key']], $setting);

                continue;
            }

            SiteSetting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
