<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["view_alamat::kontrak","view_any_alamat::kontrak","create_alamat::kontrak","update_alamat::kontrak","delete_alamat::kontrak","delete_any_alamat::kontrak","view_kapasitas::lumbung::basah","view_any_kapasitas::lumbung::basah","create_kapasitas::lumbung::basah","update_kapasitas::lumbung::basah","delete_kapasitas::lumbung::basah","delete_any_kapasitas::lumbung::basah","view_kendaraan","view_any_kendaraan","create_kendaraan","update_kendaraan","delete_kendaraan","delete_any_kendaraan","view_kendaraan::masuks","view_any_kendaraan::masuks","create_kendaraan::masuks","update_kendaraan::masuks","delete_kendaraan::masuks","delete_any_kendaraan::masuks","view_kendaraan::muat","view_any_kendaraan::muat","create_kendaraan::muat","update_kendaraan::muat","delete_kendaraan::muat","delete_any_kendaraan::muat","view_kontrak","view_any_kontrak","create_kontrak","update_kontrak","delete_kontrak","delete_any_kontrak","view_lumbung::basah","view_any_lumbung::basah","create_lumbung::basah","update_lumbung::basah","delete_lumbung::basah","delete_any_lumbung::basah","view_pembelian","view_any_pembelian","create_pembelian","update_pembelian","delete_pembelian","delete_any_pembelian","view_penjualan","view_any_penjualan","create_penjualan","update_penjualan","delete_penjualan","delete_any_penjualan","view_role","view_any_role","create_role","update_role","delete_role","delete_any_role","view_sortiran","view_any_sortiran","create_sortiran","update_sortiran","delete_sortiran","delete_any_sortiran","view_supplier","view_any_supplier","create_supplier","update_supplier","delete_supplier","delete_any_supplier","view_surat::jalan","view_any_surat::jalan","create_surat::jalan","update_surat::jalan","delete_surat::jalan","delete_any_surat::jalan","view_timbangan::tronton","view_any_timbangan::tronton","create_timbangan::tronton","update_timbangan::tronton","delete_timbangan::tronton","delete_any_timbangan::tronton","view_user","view_any_user","create_user","update_user","delete_user","delete_any_user","widget_BlogPostsChart","widget_StatsDashboard","view_dryer","view_any_dryer","create_dryer","update_dryer","delete_dryer","delete_any_dryer","view_kapasitas::dryer","view_any_kapasitas::dryer","create_kapasitas::dryer","update_kapasitas::dryer","delete_kapasitas::dryer","delete_any_kapasitas::dryer"]},{"name":"satpam","guard_name":"web","permissions":["view_kendaraan","view_any_kendaraan","view_kendaraan::masuks","view_any_kendaraan::masuks","create_kendaraan::masuks","update_kendaraan::masuks","view_kendaraan::muat","view_any_kendaraan::muat","create_kendaraan::muat","update_kendaraan::muat","widget_BlogPostsChart","widget_StatsDashboard"]},{"name":"admin","guard_name":"web","permissions":["view_alamat::kontrak","view_any_alamat::kontrak","create_alamat::kontrak","update_alamat::kontrak","delete_alamat::kontrak","delete_any_alamat::kontrak","view_kapasitas::lumbung::basah","view_any_kapasitas::lumbung::basah","create_kapasitas::lumbung::basah","update_kapasitas::lumbung::basah","delete_kapasitas::lumbung::basah","delete_any_kapasitas::lumbung::basah","view_kendaraan","view_any_kendaraan","create_kendaraan","update_kendaraan","delete_kendaraan","delete_any_kendaraan","view_kendaraan::masuks","view_any_kendaraan::masuks","create_kendaraan::masuks","update_kendaraan::masuks","delete_kendaraan::masuks","delete_any_kendaraan::masuks","view_kendaraan::muat","view_any_kendaraan::muat","create_kendaraan::muat","update_kendaraan::muat","delete_kendaraan::muat","delete_any_kendaraan::muat","view_kontrak","view_any_kontrak","create_kontrak","update_kontrak","delete_kontrak","delete_any_kontrak","view_lumbung::basah","view_any_lumbung::basah","create_lumbung::basah","update_lumbung::basah","delete_lumbung::basah","delete_any_lumbung::basah","view_pembelian","view_any_pembelian","create_pembelian","update_pembelian","delete_pembelian","delete_any_pembelian","view_penjualan","view_any_penjualan","create_penjualan","update_penjualan","delete_penjualan","delete_any_penjualan","view_role","view_any_role","create_role","update_role","delete_role","delete_any_role","view_sortiran","view_any_sortiran","create_sortiran","update_sortiran","delete_sortiran","delete_any_sortiran","view_supplier","view_any_supplier","create_supplier","update_supplier","delete_supplier","delete_any_supplier","view_surat::jalan","view_any_surat::jalan","create_surat::jalan","update_surat::jalan","delete_surat::jalan","delete_any_surat::jalan","view_timbangan::tronton","view_any_timbangan::tronton","create_timbangan::tronton","update_timbangan::tronton","delete_timbangan::tronton","delete_any_timbangan::tronton","view_user","view_any_user","create_user","update_user","delete_user","delete_any_user","widget_BlogPostsChart","widget_StatsDashboard","view_dryer","view_any_dryer","create_dryer","update_dryer","delete_dryer","delete_any_dryer","view_kapasitas::dryer","view_any_kapasitas::dryer","create_kapasitas::dryer","update_kapasitas::dryer","delete_kapasitas::dryer","delete_any_kapasitas::dryer","view_laporan::lumbung","view_any_laporan::lumbung","create_laporan::lumbung","update_laporan::lumbung","delete_laporan::lumbung","delete_any_laporan::lumbung"]}]';
        $directPermissions = '{"6":{"name":"view_kapasitas::dryer::niboss","guard_name":"web"},"7":{"name":"view_any_kapasitas::dryer::niboss","guard_name":"web"},"8":{"name":"create_kapasitas::dryer::niboss","guard_name":"web"},"9":{"name":"update_kapasitas::dryer::niboss","guard_name":"web"},"10":{"name":"restore_kapasitas::dryer::niboss","guard_name":"web"},"11":{"name":"restore_any_kapasitas::dryer::niboss","guard_name":"web"},"12":{"name":"replicate_kapasitas::dryer::niboss","guard_name":"web"},"13":{"name":"reorder_kapasitas::dryer::niboss","guard_name":"web"},"14":{"name":"delete_kapasitas::dryer::niboss","guard_name":"web"},"15":{"name":"delete_any_kapasitas::dryer::niboss","guard_name":"web"},"16":{"name":"force_delete_kapasitas::dryer::niboss","guard_name":"web"},"17":{"name":"force_delete_any_kapasitas::dryer::niboss","guard_name":"web"}}';

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (! blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            /** @var Model $roleModel */
            $roleModel = Utils::getRoleModel();
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = $roleModel::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);

                if (! blank($rolePlusPermission['permissions'])) {
                    $permissionModels = collect($rolePlusPermission['permissions'])
                        ->map(fn($permission) => $permissionModel::firstOrCreate([
                            'name' => $permission,
                            'guard_name' => $rolePlusPermission['guard_name'],
                        ]))
                        ->all();

                    $role->syncPermissions($permissionModels);
                }
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (! blank($permissions = json_decode($directPermissions, true))) {
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($permissions as $permission) {
                if ($permissionModel::whereName($permission)->doesntExist()) {
                    $permissionModel::create([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ]);
                }
            }
        }
    }
}
