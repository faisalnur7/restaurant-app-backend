<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- Roles ---
        $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
        $managerRole = Role::firstOrCreate(['slug' => 'manager'], ['name' => 'Manager']);
        $staffRole = Role::firstOrCreate(['slug' => 'staff'], ['name' => 'Staff']);

        $permissions = [
            1 => [
                'label_name' => 'Dashboard',
                'items' => [
                    'view dashboard',
                    'view top products',
                ],
            ],
            2 => [
                'label_name' => 'Reports',
                'items' => [
                    'view sales report',
                    'view order report',
                    'view product report',
                    'export sales report',
                    'export order report',
                    'export product report',
                ],
            ],
            3 => [
                'label_name' => 'Categories',
                'items' => [
                    'view categories',
                    'create categories',
                    'update categories',
                    'delete categories',
                ],
            ],
            4 => [
                'label_name' => 'Sub Categories',
                'items' => [
                    'view subcategories',
                    'create subcategories',
                    'update subcategories',
                    'delete subcategories',
                ],
            ],
            5 => [
                'label_name' => 'Products',
                'items' => [
                    'view products',
                    'create products',
                    'update products',
                    'delete products',
                ],
            ],
            6 => [
                'label_name' => 'Tables',
                'items' => [
                    'view tables',
                    'create tables',
                    'update tables',
                    'delete tables',
                ],
            ],
            7 => [
                'label_name' => 'Users',
                'items' => [
                    'view users',
                    'create users',
                    'update users',
                    'delete users',
                ],
            ],
            8 => [
                'label_name' => 'Roles',
                'items' => [
                    'view roles',
                    'create roles',
                    'update roles',
                    'delete roles',
                ],
            ],
            9 => [
                'label_name' => 'Orders',
                'items' => [
                    'view orders',
                    'create orders',
                    'update orders',
                    'update order status',
                    'delete orders',
                    'view kot',
                ],
            ],
        ];

        foreach ($permissions as $labelId => $group) {
            foreach ($group['items'] as $perm) {
                Permission::firstOrCreate(
                    ['slug' => Str::slug($perm)],
                    [
                        'name' => $perm,
                        'label' => $labelId,
                        'label_name' => $group['label_name'],
                    ]
                );
            }
        }

        // --- Assign permissions to roles ---
        // Admin gets all permissions
        $adminRole->permissions()->sync(Permission::pluck('id'));

        // Manager example
        $managerPermissions = Permission::whereIn('slug', [
            'view dashboard',
            'view top products',
            'view sales report',
            'view order report',
            'view product report',
            'view categories',
            'view subcategories',
            'view products',
            'view tables',
            'view users',
            'view roles',
            'view orders',
        ])->pluck('id');
        $managerRole->permissions()->sync($managerPermissions);

        // Staff example
        $staffPermissions = Permission::whereIn('slug', [
            'view dashboard',
            'view top products',
            'view orders',
            'create orders',
            'view kot',
        ])->pluck('id');
        $staffRole->permissions()->sync($staffPermissions);

        // --- Default Admin User ---
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );

        $admin->roles()->sync([$adminRole->id]);
    }
}
