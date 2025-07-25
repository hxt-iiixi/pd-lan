<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'kdvenecia@gmail.com'],
            [
                'name' => 'KEN',
                'password' => Hash::make('password123'), // You can change this securely later
                'is_admin' => true, // assumes you have an `is_admin` boolean field
                'is_active' => true, // if you have an `is_active` field
                'is_approved' => true, // if your schema includes this
            ]
        );
         User::updateOrCreate(
            ['email' => 'irish@gmail.com'],
            [
                'name' => 'Irish',
                'password' => Hash::make('01060812'), // You can change this securely later
                'is_admin' => true, // assumes you have an `is_admin` boolean field
                'is_active' => true, // if you have an `is_active` field
                'is_approved' => true, // if your schema includes this
            ]
        );
          User::updateOrCreate(
            ['email' => 'victoria@gmail.com'],
            [
                'name' => 'Victoria',
                'password' => Hash::make('victoria'), // You can change this securely later
                'is_admin' => false, // assumes you have an `is_admin` boolean field
                'is_active' => true, // if you have an `is_active` field
                'is_approved' => true, // if your schema includes this
            ]
        );
          User::updateOrCreate(
            ['email' => 'genalyn@gmail.com'],
            [
                'name' => 'Genalyn',
                'password' => Hash::make('genahlyn11'), // You can change this securely later
                'is_admin' => false, // assumes you have an `is_admin` boolean field
                'is_active' => true, // if you have an `is_active` field
                'is_approved' => true, // if your schema includes this
            ]
        );
        User::updateOrCreate(
            ['email' => 'phaidevenecia@gmail.com'],
            [
                'name' => 'PHAI',
                'password' => Hash::make('Zachzeth*08'), // You can change this securely later
                'is_admin' => true, // assumes you have an `is_admin` boolean field
                'is_active' => true, // if you have an `is_active` field
                'is_approved' => true, // if your schema includes this
            ]
        );
    }
}
