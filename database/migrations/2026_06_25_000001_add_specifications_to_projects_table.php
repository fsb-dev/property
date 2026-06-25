<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->json('specifications')->nullable()->after('longitude');
        });

        // Migrate legacy type values removed in the ProjectType enum rewrite.
        // 'villa' and 'plot' were top-level types; they are now categories under
        // Residential and Land respectively.
        DB::table('projects')->where('type', 'villa')
            ->update(['type' => 'residential', 'category' => 'villa']);

        DB::table('projects')->where('type', 'plot')
            ->update(['type' => 'land']);

        // Clear old Luxury/MidRange/Affordable category values — they no longer
        // exist in the enum and would cause a cast exception on load.
        $validCategories = [
            'apartment', 'flat', 'condo', 'duplex', 'villa', 'townhouse',
            'office_space', 'shop_retail', 'showroom', 'commercial_floor',
            'residential_commercial', 'shopping_mall_apartment', 'office_residential',
            'residential_plot', 'commercial_plot', 'industrial_plot',
            'factory_space', 'warehouse', 'industrial_land',
        ];

        DB::table('projects')
            ->whereNotNull('category')
            ->whereNotIn('category', $validCategories)
            ->update(['category' => null]);
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('specifications');
        });
    }
};
