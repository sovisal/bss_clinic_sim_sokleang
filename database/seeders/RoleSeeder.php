<?php

namespace Database\Seeders;

use App\Models\Ability;
use App\Models\Role;
use App\Models\AbilityModule;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Role::firstOrCreate([
			'name' => 'Admin',
			'label' => 'Administrator',
		]);
		Role::firstOrCreate([
			'name' => 'User',
			'label' => 'Normal User',
		]);

		if ($role = Role::find(1)) {
			$ability_name = AbilityModule::whereIn('module', [
				'Patient',
				'Consultation',
				'Ecg',
				'Echography',
				'Laboratory',
				'Xray',
				'Invoice',
				'Prescription',
				'Address',
				'ProductUnit',
			])
			->with(['abilities'])
			->get()
			->map(function ($ability_module){
				return $ability_module->abilities->pluck('name')->toArray();
			})->toArray();
			
			$ability_name2 = [
				'CreateDataParent',
				'UpdateDataParent',
				'ViewAnyDataParent',
				'CreateDoctor',
				'UpdateDoctor',
				'ViewAnyDoctor',
				'CreateEcgType',
				'UpdateEcgType',
				'ViewAnyEcgType',
				'CreateEchoType',
				'UpdateEchoType',
				'ViewAnyEchoType',
				'CreateLaborType',
				'UpdateLaborType',
				'ViewAnyLaborType',
				'CreateXRayType',
				'UpdateXRayType',
				'ViewAnyXRayType',
				'CreateProduct',
				'UpdateProduct',
				'ViewAnyProduct',
				'CreateMedicine',
				'UpdateMedicine',
				'ViewAnyMedicine',
				'CreateService',
				'UpdateService',
				'ViewAnyService',
			];
			$collect_ids = collect([
				$ability_name,
				$ability_name2
			])->flatten()->toArray();

			$role->allowTo($collect_ids);
		}
	}
}
