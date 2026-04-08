<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crear usuarios
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@cevicheria.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'active' => true,
        ]);

        $cajero = User::create([
            'name' => 'Juan Pérez',
            'email' => 'cajero@cevicheria.com',
            'password' => Hash::make('password'),
            'role' => 'cajero',
            'active' => true,
        ]);

        $mesero = User::create([
            'name' => 'María García',
            'email' => 'mesero@cevicheria.com',
            'password' => Hash::make('password'),
            'role' => 'mesero',
            'active' => true,
        ]);

        // Crear categorías
        $ceviches = Category::create([
            'name' => 'Ceviches',
            'description' => 'Variedad de ceviches frescos',
            'active' => true,
        ]);

        $bebidas = Category::create([
            'name' => 'Bebidas',
            'description' => 'Bebidas frías y refrescantes',
            'active' => true,
        ]);

        $entradas = Category::create([
            'name' => 'Entradas',
            'description' => 'Entradas y aperitivos',
            'active' => true,
        ]);

        $platosFondo = Category::create([
            'name' => 'Platos de Fondo',
            'description' => 'Platos principales',
            'active' => true,
        ]);

        // Crear productos de Ceviches
        Product::create([
            'category_id' => $ceviches->id,
            'name' => 'Ceviche Clásico',
            'description' => 'Pescado fresco marinado en limón con cebolla, ají y culantro',
            'price' => 25.00,
            'stock' => 50,
            'active' => true,
        ]);

        Product::create([
            'category_id' => $ceviches->id,
            'name' => 'Ceviche Mixto',
            'description' => 'Combinación de pescado, camarones y pulpo',
            'price' => 30.00,
            'stock' => 40,
            'active' => true,
        ]);

        Product::create([
            'category_id' => $ceviches->id,
            'name' => 'Ceviche de Conchas Negras',
            'description' => 'Exquisitas conchas negras marinadas',
            'price' => 35.00,
            'stock' => 30,
            'active' => true,
        ]);

        Product::create([
            'category_id' => $ceviches->id,
            'name' => 'Tiradito',
            'description' => 'Finas láminas de pescado con salsa especial',
            'price' => 28.00,
            'stock' => 35,
            'active' => true,
        ]);

        // Crear productos de Bebidas
        Product::create([
            'category_id' => $bebidas->id,
            'name' => 'Chicha Morada',
            'description' => 'Bebida tradicional de maíz morado',
            'price' => 5.00,
            'stock' => 100,
            'active' => true,
        ]);

        Product::create([
            'category_id' => $bebidas->id,
            'name' => 'Inca Kola',
            'description' => 'Gaseosa nacional 500ml',
            'price' => 4.00,
            'stock' => 80,
            'active' => true,
        ]);

        Product::create([
            'category_id' => $bebidas->id,
            'name' => 'Limonada Frozen',
            'description' => 'Limonada helada refrescante',
            'price' => 6.00,
            'stock' => 60,
            'active' => true,
        ]);

        Product::create([
            'category_id' => $bebidas->id,
            'name' => 'Agua Mineral',
            'description' => 'Agua mineral 625ml',
            'price' => 3.00,
            'stock' => 100,
            'active' => true,
        ]);

        // Crear productos de Entradas
        Product::create([
            'category_id' => $entradas->id,
            'name' => 'Causa Limeña',
            'description' => 'Papa amarilla rellena con pollo o atún',
            'price' => 12.00,
            'stock' => 40,
            'active' => true,
        ]);

        Product::create([
            'category_id' => $entradas->id,
            'name' => 'Papa a la Huancaína',
            'description' => 'Papas con salsa de ají amarillo',
            'price' => 10.00,
            'stock' => 50,
            'active' => true,
        ]);

        Product::create([
            'category_id' => $entradas->id,
            'name' => 'Choritos a la Chalaca',
            'description' => 'Mejillones con salsa criolla',
            'price' => 15.00,
            'stock' => 30,
            'active' => true,
        ]);

        // Crear productos de Platos de Fondo
        Product::create([
            'category_id' => $platosFondo->id,
            'name' => 'Arroz con Mariscos',
            'description' => 'Arroz con variedad de mariscos',
            'price' => 32.00,
            'stock' => 35,
            'active' => true,
        ]);

        Product::create([
            'category_id' => $platosFondo->id,
            'name' => 'Sudado de Pescado',
            'description' => 'Pescado en salsa con yucas',
            'price' => 28.00,
            'stock' => 40,
            'active' => true,
        ]);

        Product::create([
            'category_id' => $platosFondo->id,
            'name' => 'Chicharrón de Calamar',
            'description' => 'Calamar frito crujiente',
            'price' => 26.00,
            'stock' => 45,
            'active' => true,
        ]);

        Product::create([
            'category_id' => $platosFondo->id,
            'name' => 'Jalea Mixta',
            'description' => 'Variedad de mariscos fritos',
            'price' => 35.00,
            'stock' => 30,
            'active' => true,
        ]);

        echo "✓ Base de datos poblada exitosamente!\n";
        echo "✓ Usuarios creados:\n";
        echo "  - Admin: admin@cevicheria.com / password\n";
        echo "  - Cajero: cajero@cevicheria.com / password\n";
        echo "  - Mesero: mesero@cevicheria.com / password\n";
    }
}
