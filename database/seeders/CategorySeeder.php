<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Plantes d\'intérieur', 'slug' => 'plantes-interieur', 'description' => 'Plantes pour la maison'],
            ['name' => 'Plantes d\'extérieur', 'slug' => 'plantes-exterieur', 'description' => 'Plantes pour le jardin'],
            ['name' => 'Outils de jardinage', 'slug' => 'outils-jardinage', 'description' => 'Outils et accessoires'],
            ['name' => 'Engrais & Soins', 'slug' => 'engrais-soins', 'description' => 'Engrais, terres et traitements'],
        ];
        foreach ($categories as $cat) {
            Category::create($cat);
        }
    }
}
