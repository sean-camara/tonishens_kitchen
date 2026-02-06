<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Migrates data from the old tonishens_kitchen database into the new normalized schema.
 *
 * Prerequisites:
 * 1. Old database tables should still exist in the same MySQL database (or a copy).
 * 2. Run `php artisan migrate` first to create the new tables.
 * 3. Then run `php artisan db:seed --class=MigrateOldDataSeeder`
 *
 * This seeder reads from the OLD table names (prefixed with 'old_' if you renamed them,
 * or uses the original names). Adjust the table names below as needed.
 */
class MigrateOldDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting data migration from old schema...');

        $this->migrateUsers();
        $this->migrateCategories();
        $this->migrateDishes();
        $this->migrateOrders();
        $this->migrateInventory();
        $this->migrateAbout();

        $this->command->info('Data migration complete!');
    }

    private function migrateUsers(): void
    {
        $this->command->info('Migrating users...');

        // Migrate regular users
        $oldUsers = DB::table('users')->get();
        foreach ($oldUsers as $u) {
            DB::table('users')->insert([
                'id'         => $u->user_id,
                'first_name' => $u->first_name,
                'last_name'  => $u->last_name,
                'email'      => $u->email,
                'password'   => Hash::check('', $u->password) ? $u->password : Hash::make('changeme123'),
                'phone'      => $u->phone ?? null,
                'address'    => $u->default_address ?? null,
                'role'       => 'customer',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Migrate profile image (BLOB → file)
            if (!empty($u->profile_image)) {
                $filename = "avatars/user_{$u->user_id}.jpg";
                Storage::disk('public')->put($filename, $u->profile_image);
                DB::table('users')->where('id', $u->user_id)->update(['avatar_path' => $filename]);
            }
        }

        // Migrate admin users
        $oldAdmins = DB::table('users_admin')->get();
        foreach ($oldAdmins as $a) {
            // Check if email already exists
            $exists = DB::table('users')->where('email', $a->admin_email)->exists();
            if ($exists) continue;

            DB::table('users')->insert([
                'first_name' => $a->admin_first_name ?? 'Admin',
                'last_name'  => $a->admin_last_name ?? '',
                'email'      => $a->admin_email,
                'password'   => Hash::check('', $a->admin_password) ? $a->admin_password : Hash::make('admin123'),
                'role'       => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('  Users migrated: ' . DB::table('users')->count());
    }

    private function migrateCategories(): void
    {
        $this->command->info('Migrating categories...');

        $oldCategories = DB::table('dish_categories')->get();
        foreach ($oldCategories as $c) {
            DB::table('categories')->insert([
                'id'         => $c->category_id,
                'name'       => $c->category_name,
                'slug'       => Str::slug($c->category_name),
                'sort_order' => $c->category_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function migrateDishes(): void
    {
        $this->command->info('Migrating dishes...');

        $oldDishes = DB::table('dishes')->get();
        foreach ($oldDishes as $d) {
            $imagePath = null;
            if (!empty($d->dish_image)) {
                $imagePath = "dishes/dish_{$d->dish_id}.jpg";
                Storage::disk('public')->put($imagePath, $d->dish_image);
            }

            DB::table('dishes')->insert([
                'id'           => $d->dish_id,
                'category_id'  => $d->category_id,
                'name'         => $d->dish_name,
                'description'  => $d->dish_description ?? null,
                'price'        => $d->dish_price,
                'image_path'   => $imagePath,
                'is_available' => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        $this->command->info('  Dishes migrated: ' . DB::table('dishes')->count());
    }

    private function migrateOrders(): void
    {
        $this->command->info('Migrating orders...');

        $oldOrders = DB::table('orders')->get();
        foreach ($oldOrders as $o) {
            $subtotal = $o->total_amount ?? 0;
            $tax = round($subtotal * 0.12, 2);
            $delivery = 50;
            $total = $subtotal + $tax + $delivery;

            DB::table('orders')->insert([
                'id'           => $o->order_id,
                'user_id'      => $o->user_id,
                'subtotal'     => $subtotal,
                'tax_amount'   => $tax,
                'delivery_fee' => $delivery,
                'total_amount' => $total,
                'status'       => $this->mapStatus($o->order_status ?? 'Pending'),
                'created_at'   => $o->order_time ?? now(),
                'updated_at'   => $o->order_time ?? now(),
            ]);

            // Order details
            if (isset($o->first_name)) {
                DB::table('order_details')->insert([
                    'order_id'       => $o->order_id,
                    'first_name'     => $o->first_name ?? '',
                    'last_name'      => $o->last_name ?? '',
                    'phone'          => $o->phone ?? '',
                    'address'        => $o->address ?? '',
                    'payment_method' => strtolower($o->payment_method ?? 'cod'),
                    'change_for'     => $o->change_for ?? null,
                    'request_cutlery'=> $o->request_utensils ?? false,
                    'notes'          => $o->delivery_notes ?? null,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        }

        // Order items
        $oldItems = DB::table('order_items')->get();
        foreach ($oldItems as $i) {
            $dish = DB::table('dishes')->where('id', $i->dish_id)->first();
            DB::table('order_items')->insert([
                'order_id'   => $i->order_id,
                'dish_id'    => $i->dish_id,
                'dish_name'  => $dish->name ?? 'Unknown Dish',
                'unit_price' => $i->price ?? $dish->price ?? 0,
                'quantity'   => $i->quantity ?? 1,
                'line_total' => ($i->price ?? 0) * ($i->quantity ?? 1),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Feedback
        if (\Schema::hasTable('feedback_old') || \Schema::hasTable('dish_feedback')) {
            $tableName = \Schema::hasTable('dish_feedback') ? 'dish_feedback' : 'feedback_old';
            $oldFeedback = DB::table($tableName)->get();
            foreach ($oldFeedback as $f) {
                DB::table('feedback')->insert([
                    'order_id'   => $f->order_id,
                    'user_id'    => $f->user_id,
                    'dish_id'    => $f->dish_id ?? null,
                    'rating'     => $f->rating ?? 5,
                    'comment'    => $f->comment ?? null,
                    'created_at' => $f->created_at ?? now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('  Orders migrated: ' . DB::table('orders')->count());
    }

    private function migrateInventory(): void
    {
        $this->command->info('Migrating inventory...');

        // Ingredient categories
        if (\Schema::hasTable('ingredient_categories_old') || \Schema::hasTable('ingredients_category')) {
            $catTable = \Schema::hasTable('ingredients_category') ? 'ingredients_category' : 'ingredient_categories_old';
            $oldCats = DB::table($catTable)->get();
            foreach ($oldCats as $c) {
                DB::table('ingredient_categories')->insert([
                    'id'         => $c->category_id ?? $c->id,
                    'name'       => $c->category_name ?? $c->name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Ingredients
        if (\Schema::hasTable('ingredients_old') || \Schema::hasTable('ingredients')) {
            $ingTable = \Schema::hasTable('ingredients') ? 'ingredients' : 'ingredients_old';
            $oldIngs = DB::table($ingTable)->get();
            foreach ($oldIngs as $ing) {
                DB::table('ingredients')->insert([
                    'category_id'   => $ing->category_id ?? 1,
                    'name'          => $ing->item_name ?? $ing->name,
                    'unit'          => $ing->unit ?? 'pcs',
                    'quantity'      => $ing->stock_count ?? $ing->quantity ?? 0,
                    'reorder_level' => $ing->reorder_level ?? 10,
                    'cost_per_unit' => $ing->cost_per_unit ?? 0,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }
    }

    private function migrateAbout(): void
    {
        $this->command->info('Migrating about page data...');

        // History
        if (\Schema::hasTable('about_history_old') || \Schema::hasTable('about_content')) {
            $table = \Schema::hasTable('about_content') ? 'about_content' : 'about_history_old';
            $row = DB::table($table)->first();
            if ($row) {
                DB::table('about_history')->insert([
                    'content'    => $row->content ?? $row->history_text ?? '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Contacts
        if (\Schema::hasTable('about_contacts')) {
            $contacts = DB::table('about_contacts')->get();
            foreach ($contacts as $c) {
                DB::table('contacts')->insert([
                    'type'       => $c->contact_type ?? 'Other',
                    'value'      => $c->contact_value ?? '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Social links
        if (\Schema::hasTable('about_socials')) {
            $socials = DB::table('about_socials')->get();
            foreach ($socials as $s) {
                DB::table('social_links')->insert([
                    'platform'   => $s->social_platform ?? '',
                    'url'        => $s->social_url ?? '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // FAQs
        if (\Schema::hasTable('about_faqs')) {
            $faqs = DB::table('about_faqs')->get();
            foreach ($faqs as $faq) {
                DB::table('faqs')->insert([
                    'question'   => $faq->faq_question ?? '',
                    'answer'     => $faq->faq_answer ?? '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function mapStatus(string $oldStatus): string
    {
        return match (strtolower(trim($oldStatus))) {
            'pending'      => 'Pending',
            'preparing'    => 'Preparing',
            'on the way', 'otw', 'on delivery' => 'On the Way',
            'completed', 'delivered' => 'Completed',
            'canceled', 'cancelled' => 'Canceled',
            default => 'Pending',
        };
    }
}
