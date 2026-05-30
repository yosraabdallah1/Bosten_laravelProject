<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // Catégorie 1 : Plantes d'intérieur
            [
                'category_id' => 1,
                'name' => 'Aloe Vera',
                'slug' => 'aloe-vera',
                'description' => 'Plante médicinale dépolluante, idéale dans une chambre ou un salon',
                'price' => 12.50,
                'stock' => 30,
                'image' => 'aloe-vera.jpg',
                'is_active' => true,
            ],
            [
                'category_id' => 1,
                'name' => 'Pothos',
                'slug' => 'pothos',
                'description' => 'Plante grimpante increvable, parfaite pour les débutants',
                'price' => 14.90,
                'stock' => 25,
                'image' => 'pothos.jpg',
                'is_active' => true,
            ],

            // Catégorie 2 : Plantes d'extérieur
            [
                'category_id' => 2,
                'name' => 'Bougainvillier',
                'slug' => 'bougainvillier',
                'description' => 'Plante grimpante aux fleurs magenta, résiste bien à la chaleur tunisienne',
                'price' => 18.00,
                'stock' => 20,
                'image' => 'bougainvillier.jpg',
                'is_active' => true,
            ],
            [
                'category_id' => 2,
                'name' => 'Cactus raquette (Opuntia)',
                'slug' => 'cactus-raquette',
                'description' => 'Cactus rustique sans entretien, supporte la sécheresse',
                'price' => 9.90,
                'stock' => 60,
                'image' => 'cactus-raquette.jpg',
                'is_active' => true,
            ],

            // Catégorie 3 : Outils de jardinage
            [
                'category_id' => 3,
                'name' => 'Sécateur professionnel',
                'slug' => 'secateur-pro',
                'description' => 'Lame en acier trempé, coupe nette pour tailler vos plantes',
                'price' => 24.50,
                'stock' => 15,
                'image' => 'secateur.jpg',
                'is_active' => true,
            ],
            [
                'category_id' => 3,
                'name' => 'Arrosoir 5L',
                'slug' => 'arrosoir-5l',
                'description' => 'Arrosoir plastique résistant, pomme fine pour arrosage doux',
                'price' => 11.90,
                'stock' => 40,
                'image' => 'arrosoir.jpg',
                'is_active' => true,
            ],

            // Catégorie 4 : Engrais & Soins
            [
                'category_id' => 4,
                'name' => 'Engrais universel liquide',
                'slug' => 'engrais-universel',
                'description' => 'Engrais NPK 10-10-10 pour toutes les plantes, 1 litre',
                'price' => 8.75,
                'stock' => 50,
                'image' => 'engrais.jpg',
                'is_active' => true,
            ],
            [
                'category_id' => 4,
                'name' => 'Terreau spécial plantes vertes',
                'slug' => 'terreau-plantes-vertes',
                'description' => 'Sac 10L, léger et riche en nutriments',
                'price' => 6.50,
                'stock' => 35,
                'image' => 'terreau.jpg',
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
