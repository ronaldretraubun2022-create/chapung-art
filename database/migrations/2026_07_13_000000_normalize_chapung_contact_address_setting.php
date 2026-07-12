<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const FINAL_ADDRESS = "JL. SESATE NO. 242, RT 007/RW 002, BAMBU PEMALI\nKABUPATEN MERAUKE, PAPUA SELATAN 99616";

    private const FINAL_MAPS_URL = 'https://www.google.com/maps/search/?api=1&query=JL.%20SESATE%20NO.%20242%2C%20RT%20007%2FRW%20002%2C%20BAMBU%20PEMALI%20KABUPATEN%20MERAUKE%20PAPUA%20SELATAN%2099616';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }

        $this->updateLegacySetting('address', self::FINAL_ADDRESS, 'textarea', 'contact', [
            "JL SESATE NO 242 RT.007 RW.002 BAMBU PEMALI\nMERAUKE MERAUKE, KAB. 99616",
            "JL SESATE NO 242 RT.007 RW.002 BAMBU PEMALI\r\nMERAUKE MERAUKE, KAB. 99616",
            'JL SESATE NO 242 RT.007 RW.002 BAMBU PEMALI MERAUKE MERAUKE, KAB. 99616',
            'JL SESATE NO 242 RT.007 RW.002 BAMBU PEMALI\KAB. MERAUKE - PROV. PAPUA SELATAN, 99616',
            "JL SESATE NO 242 RT.007 RW.002 BAMBU PEMALI\nKAB. MERAUKE - PROV. PAPUA SELATAN 99616",
            "JL SESATE NO 242 RT.007 RW.002 BAMBU PEMALI\r\nKAB. MERAUKE - PROV. PAPUA SELATAN 99616",
        ]);

        $this->updateLegacySetting('google_maps_url', self::FINAL_MAPS_URL, 'url', 'contact', [
            'https://www.google.com/maps/search/?api=1&query=JL%20SESATE%20NO%20242%20RT.007%20RW.002%20BAMBU%20PEMALI%20MERAUKE%20MERAUKE%20KAB.%2099616',
            'https://www.google.com/maps/search/?api=1&query=JL%20SESATE%20NO%20242%20RT.007%20RW.002%20BAMBU%20PEMALI%20KAB.%20MERAUKE%20PROV.%20PAPUA%20SELATAN%2099616',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data normalization is intentionally not reversed.
    }

    /**
     * @param  array<int, string>  $legacyValues
     */
    private function updateLegacySetting(string $key, string $value, string $type, string $group, array $legacyValues): void
    {
        $now = now();
        $setting = DB::table('site_settings')->where('key', $key)->first();

        if (! $setting) {
            DB::table('site_settings')->insert([
                'key' => $key,
                'value' => $value,
                'type' => $type,
                'group' => $group,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return;
        }

        if (filled($setting->value) && ! in_array((string) $setting->value, $legacyValues, true)) {
            return;
        }

        DB::table('site_settings')
            ->where('key', $key)
            ->update([
                'value' => $value,
                'type' => $type,
                'group' => $group,
                'updated_at' => $now,
            ]);
    }
};
