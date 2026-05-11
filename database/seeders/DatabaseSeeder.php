<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            AdditionalSettingSeeder::class,
            AdminSeeder::class,
            CargoCompanySeeder::class,
            CitySeeder::class,
            DistrictSeeder::class,
            NeighborhoodSeeder::class,
            CompanySeeder::class,
            CurrencySeeder::class,
            EntityLastUpdateSeeder::class,
            GeneralInfoSeeder::class,
            OrderStatusSeeder::class,
            PermissionSeeder::class,
            PermissionUserSeeder::class,
            ThemeSettingSeeder::class,
            UserSeeder::class,
            BankIntegrationSeeder::class,
            InstallmentSeeder::class,
        ]);
    }
}
