<?php

use App\Models\Category;
use App\Models\Photography;

test('photography marketplace renders digital product attributes safely', function () {
    $category = Category::create([
        'name' => 'Digital Photography',
        'slug' => 'digital-photography',
        'type' => 'photography',
        'is_active' => true,
    ]);

    $photo = Photography::create([
        'title' => 'Wasur Digital Print',
        'slug' => 'wasur-digital-print',
        'category_id' => $category->id,
        'photographer_name' => 'Chapung Photographer',
        'location' => 'Merauke',
        'camera' => 'Canon EOS R',
        'lens' => '35mm',
        'taken_at' => now(),
        'license' => 'Digital editorial license',
        'thumbnail' => 'photography/wasur-digital.jpg',
        'price' => 450000,
        'stock' => 2,
    ]);

    $this->withSession(['locale' => 'en'])
        ->get(route('photography.index'))
        ->assertOk()
        ->assertSee('Digital product')
        ->assertSee('Digital editorial license')
        ->assertSee(route('photography.show', $photo->slug), false);

    $this->withSession(['locale' => 'en'])
        ->get(route('photography.show', $photo->slug))
        ->assertOk()
        ->assertSee('Digital product')
        ->assertSee('Secure digital delivery after confirmation')
        ->assertSee('Camera')
        ->assertSee('Canon EOS R')
        ->assertSee('Lens')
        ->assertSee('35mm')
        ->assertSee('License')
        ->assertSee('Digital editorial license')
        ->assertSee('loading="eager"', false)
        ->assertSee('fetchpriority="high"', false)
        ->assertDontSee('digital_file_path')
        ->assertDontSee('storage/app/private');
});

test('photography detail renders physical product fallback safely', function () {
    $photo = Photography::create([
        'title' => 'Maro Physical Print',
        'slug' => 'maro-physical-print',
        'photographer_name' => 'Chapung Photographer',
        'thumbnail' => null,
        'price' => null,
    ]);

    $this->withSession(['locale' => 'en'])
        ->get(route('photography.show', $photo->slug))
        ->assertOk()
        ->assertSee('Physical art print')
        ->assertSee('Manual shipping confirmation')
        ->assertSee('Price by request')
        ->assertSee('Photography product description is not available yet.')
        ->assertDontSee('storage/app/private');
});
