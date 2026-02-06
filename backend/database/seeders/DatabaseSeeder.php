<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Category;
use App\Models\Dish;
use App\Models\AboutHistory;
use App\Models\Contact;
use App\Models\SocialLink;
use App\Models\Faq;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user (super_admin role)
        User::firstOrCreate(
            ['email' => 'admin@tonishenskitchen.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'password' => Hash::make('admin123'),
                'role' => 'super_admin',
                'phone' => '09171234567',
                'address' => 'Tonishen\'s Kitchen HQ',
            ]
        );

        // Create a test customer
        User::firstOrCreate(
            ['email' => 'customer@test.com'],
            [
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '09181234567',
                'address' => 'Manila, Philippines',
            ]
        );

        // Create categories
        $categories = ['Rice Meals', 'Noodles', 'Soup', 'Grilled', 'Beverages', 'Desserts'];
        foreach ($categories as $name) {
            Category::firstOrCreate(['name' => $name]);
        }

        // Create sample dishes
        $riceCategory = Category::where('name', 'Rice Meals')->first();
        $noodlesCategory = Category::where('name', 'Noodles')->first();
        $soupCategory = Category::where('name', 'Soup')->first();
        $grilledCategory = Category::where('name', 'Grilled')->first();
        $beveragesCategory = Category::where('name', 'Beverages')->first();
        $dessertsCategory = Category::where('name', 'Desserts')->first();

        $dishes = [
            ['name' => 'Chicken Adobo', 'description' => 'Classic Filipino chicken adobo braised in soy sauce and vinegar', 'price' => 150.00, 'category_id' => $riceCategory->id, 'is_available' => true],
            ['name' => 'Pork Sinigang', 'description' => 'Sour pork soup with vegetables in tamarind broth', 'price' => 180.00, 'category_id' => $soupCategory->id, 'is_available' => true],
            ['name' => 'Pancit Canton', 'description' => 'Stir-fried egg noodles with vegetables and meat', 'price' => 120.00, 'category_id' => $noodlesCategory->id, 'is_available' => true],
            ['name' => 'Inihaw na Liempo', 'description' => 'Grilled pork belly marinated in Filipino spices', 'price' => 200.00, 'category_id' => $grilledCategory->id, 'is_available' => true],
            ['name' => 'Kare-Kare', 'description' => 'Oxtail stew in peanut sauce with vegetables', 'price' => 250.00, 'category_id' => $riceCategory->id, 'is_available' => true],
            ['name' => 'Sinigang na Hipon', 'description' => 'Shrimp sour soup with vegetables', 'price' => 220.00, 'category_id' => $soupCategory->id, 'is_available' => true],
            ['name' => 'Pancit Bihon', 'description' => 'Rice noodles stir-fried with soy sauce and vegetables', 'price' => 110.00, 'category_id' => $noodlesCategory->id, 'is_available' => true],
            ['name' => 'Inihaw na Bangus', 'description' => 'Grilled milkfish stuffed with tomatoes and onions', 'price' => 180.00, 'category_id' => $grilledCategory->id, 'is_available' => true],
            ['name' => 'Halo-Halo', 'description' => 'Shaved ice dessert with mixed sweet beans, fruits, and leche flan', 'price' => 100.00, 'category_id' => $dessertsCategory->id, 'is_available' => true],
            ['name' => 'Mango Shake', 'description' => 'Fresh Philippine mango blended with ice and milk', 'price' => 80.00, 'category_id' => $beveragesCategory->id, 'is_available' => true],
            ['name' => 'Lechon Kawali', 'description' => 'Deep-fried crispy pork belly', 'price' => 190.00, 'category_id' => $riceCategory->id, 'is_available' => true],
            ['name' => 'Bulalo', 'description' => 'Beef shank and marrow soup with corn and vegetables', 'price' => 280.00, 'category_id' => $soupCategory->id, 'is_available' => true],
        ];

        foreach ($dishes as $dish) {
            Dish::firstOrCreate(['name' => $dish['name']], $dish);
        }

        // Seed About data
        AboutHistory::firstOrCreate(
            ['id' => 1],
            ['content' => "Tonishen's Kitchen started as a humble home-based food business, born out of a passion for authentic Filipino cooking. What began as preparing meals for family and friends quickly grew into a beloved local kitchen known for its hearty, home-cooked dishes.\n\nOur recipes are passed down through generations, using only the freshest ingredients and traditional cooking methods. Every dish we serve carries the warmth and love of Filipino home cooking.\n\nToday, Tonishen's Kitchen continues to serve the community with the same dedication to quality and taste that started it all. We believe that great food brings people together, and we're proud to be part of your dining experience."]
        );

        Contact::firstOrCreate(['type' => 'Phone'], ['value' => '0917-123-4567', 'sort_order' => 1]);
        Contact::firstOrCreate(['type' => 'Email'], ['value' => 'tonishenskitchen@gmail.com', 'sort_order' => 2]);
        Contact::firstOrCreate(['type' => 'Address'], ['value' => 'Manila, Philippines', 'sort_order' => 3]);

        SocialLink::firstOrCreate(['platform' => 'Facebook'], ['url' => 'https://facebook.com/tonishenskitchen', 'sort_order' => 1]);
        SocialLink::firstOrCreate(['platform' => 'Instagram'], ['url' => 'https://instagram.com/tonishenskitchen', 'sort_order' => 2]);

        Faq::firstOrCreate(['question' => 'What are your operating hours?'], ['answer' => 'We are open from 9:00 AM to 8:00 PM, Monday to Saturday. We are closed on Sundays and public holidays.', 'sort_order' => 1]);
        Faq::firstOrCreate(['question' => 'How do I place an order?'], ['answer' => 'Simply browse our menu, add items to your cart, and proceed to checkout. You can also call us directly to place your order.', 'sort_order' => 2]);
        Faq::firstOrCreate(['question' => 'Do you offer delivery?'], ['answer' => 'Yes! We offer delivery within Metro Manila. A flat delivery fee of ₱50 applies to all orders.', 'sort_order' => 3]);
        Faq::firstOrCreate(['question' => 'Can I cancel my order?'], ['answer' => 'Orders can be cancelled while they are still in "Pending" status. Once preparation has started, cancellations are no longer possible.', 'sort_order' => 4]);
    }
}
