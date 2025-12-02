<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Cart;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        echo "Seeding admin and regular users...\n\n";
        
        //create or update admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password123'),
                'is_admin' => true,
            ]
        );
        
        echo "? Admin user: {$admin->email} (ID: {$admin->id})\n";
        
        // create or update admin cart
        Cart::updateOrCreate(
            ['user_id' => $admin->id],
            []
        );
        
        echo "? Admin cart ready\n\n";
        
        // create or update regular user
        $user = User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Regular User',
                'password' => Hash::make('password123'),
                'is_admin' => false,
            ]
        );
        
        echo "? Regular user: {$user->email} (ID: {$user->id})\n";
        
        // create or update regular user cart
        Cart::updateOrCreate(
            ['user_id' => $user->id],
            []
        );
        
        echo "? Regular user cart ready\n\n";
        
        echo "?? Seeding completed successfully!\n";
        echo "Login credentials:\n";
        echo "Admin: admin@example.com / password123\n";
        echo "User: user@example.com / password123\n";
    }
}
