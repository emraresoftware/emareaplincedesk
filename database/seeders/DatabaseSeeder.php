<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Branch
        $branchId = DB::table('branches')->insertGetId([
            'name'       => 'Ana Şube',
            'code'       => 'MAIN',
            'city'       => 'İstanbul',
            'country'    => 'TR',
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Admin user
        DB::table('users')->insert([
            'name'              => 'Yönetici',
            'email'             => 'admin@emare.local',
            'password'          => Hash::make('password'),
            'branch_id'         => $branchId,
            'role'              => 'admin',
            'is_active'         => true,
            'email_verified_at' => now(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // Cihaz kategorileri
        $categories = [
            ['name' => 'Akıllı Telefon',   'icon' => '📱', 'color' => '#6366f1'],
            ['name' => 'Tablet',            'icon' => '📋', 'color' => '#3b82f6'],
            ['name' => 'Laptop / Diz Üstü','icon' => '💻', 'color' => '#8b5cf6'],
            ['name' => 'Masaüstü PC',       'icon' => '🖥️', 'color' => '#0ea5e9'],
            ['name' => 'Televizyon',        'icon' => '📺', 'color' => '#ec4899'],
            ['name' => 'Oyun Konsolu',      'icon' => '🎮', 'color' => '#f59e0b'],
            ['name' => 'Yazıcı / Faks',    'icon' => '🖨️', 'color' => '#10b981'],
            ['name' => 'Beyaz Eşya',        'icon' => '🧺', 'color' => '#14b8a6'],
            ['name' => 'Küçük Ev Aleti',    'icon' => '🔌', 'color' => '#f97316'],
            ['name' => 'Diğer',             'icon' => '🔧', 'color' => '#94a3b8'],
        ];
        foreach ($categories as $cat) {
            DB::table('device_categories')->insert([
                'name' => $cat['name'], 'icon' => $cat['icon'], 'color' => $cat['color'],
                'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // Demo teknisyenler
        $techs = [
            ['name' => 'Mehmet Kaya',   'email' => 'mehmet@emare.local',  'phone' => '0532 111 1111', 'specialty' => 'Telefon & Tablet'],
            ['name' => 'Ayşe Demir',    'email' => 'ayse@emare.local',    'phone' => '0533 222 2222', 'specialty' => 'Laptop & PC'],
            ['name' => 'Mustafa Şahin', 'email' => 'mustafa@emare.local', 'phone' => '0534 333 3333', 'specialty' => 'Beyaz Eşya & TV'],
        ];
        foreach ($techs as $t) {
            DB::table('technicians')->insert([
                'branch_id' => $branchId, 'name' => $t['name'], 'email' => $t['email'],
                'phone' => $t['phone'], 'specialty' => $t['specialty'],
                'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // Demo yedek parçalar
        $parts = [
            ['sku'=>'SCR-IP14-OEM',   'name'=>'iPhone 14 Ekran (OEM)',     'cat'=>'Ekran',     'cost'=>800,  'sale'=>1500,'stock'=>5,'min'=>2],
            ['sku'=>'SCR-S23-AMOLED', 'name'=>'Samsung S23 Ekran AMOLED',  'cat'=>'Ekran',     'cost'=>700,  'sale'=>1400,'stock'=>3,'min'=>2],
            ['sku'=>'BAT-IP13-OEM',   'name'=>'iPhone 13 Batarya',          'cat'=>'Batarya',   'cost'=>200,  'sale'=>450, 'stock'=>10,'min'=>5],
            ['sku'=>'BAT-UNIV-3000',  'name'=>'Universal 3000mAh Batarya', 'cat'=>'Batarya',   'cost'=>120,  'sale'=>280, 'stock'=>8,'min'=>3],
            ['sku'=>'CHG-USB-C-65W',  'name'=>'USB-C 65W Hızlı Şarj',     'cat'=>'Aksesuar',  'cost'=>80,   'sale'=>200, 'stock'=>15,'min'=>5],
            ['sku'=>'RAM-DDR4-8GB',   'name'=>'DDR4 8GB RAM',              'cat'=>'Bellek',     'cost'=>350,  'sale'=>650, 'stock'=>4,'min'=>2],
            ['sku'=>'SSD-M2-256',     'name'=>'M.2 NVMe 256GB SSD',        'cat'=>'Depolama',  'cost'=>500,  'sale'=>950, 'stock'=>3,'min'=>2],
        ];
        foreach ($parts as $p) {
            DB::table('spare_parts')->insert([
                'branch_id'=>$branchId,'sku'=>$p['sku'],'name'=>$p['name'],'category'=>$p['cat'],
                'cost_price'=>$p['cost'],'sale_price'=>$p['sale'],'stock_quantity'=>$p['stock'],'min_stock'=>$p['min'],
                'is_active'=>true,'created_at'=>now(),'updated_at'=>now(),
            ]);
        }

        $this->command->info('✅ Seed tamamlandı! admin@emare.local / password');
    }

}
}
