<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\PosCategoryModifier;
use App\Models\PosModifier;
use App\Models\Store;
use Illuminate\Database\Seeder;

class UpsizeModifierSeeder extends Seeder
{
    public function run(): void
    {
        $store = Store::query()->findOrFail(3);
        $category = Category::query()
            ->where('store_id', $store->id)
            ->where('category_name', 'Premium Series')
            ->firstOrFail();

        $modifier = PosModifier::query()->updateOrCreate(
            [
                'store_id' => $store->id,
                'name' => 'Upsize',
            ],
            [
                'price_delta' => 3000,
                'is_active' => true,
            ]
        );

        PosCategoryModifier::query()->updateOrCreate(
            [
                'category_id' => $category->id,
                'modifier_id' => $modifier->id,
            ],
            [
                'store_id' => $store->id,
                'is_active' => true,
            ]
        );
    }
}
