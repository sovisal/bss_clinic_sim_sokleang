<?php

namespace App\View\Components;

use App\Models\Setting;
use Illuminate\View\Component;
use App\Models\Inventory\Product;
use App\Models\Inventory\StockIn;

class AppLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $_POST['nb_stock_expired'] = StockIn::Expired()->count();
        $_POST['nb_out_of_stock'] = Product::OutOfStock()->count();
        $nb_alerted = $_POST['nb_stock_expired'] + $_POST['nb_out_of_stock'];
        $stock_alert_badge   = $nb_alerted > 0 ? '<span class="text-danger"> (<em>' . $nb_alerted . '</em> )</span>' : '';
        
        $menu = [];
        $menu['patient'] = getFirstPermittedSubMenu([
            'can' => '',
            'url' => '',
            'label' => 'Patient',

            'sub' => [
                'patient' => [
                    'can' => 'ViewAnyPatient',
                    'url' => route('patient.index'),
                    'name' => ['index', 'create', 'edit', 'show'],
                    'label' => 'Patient',
                ],
                'consultation' => [
                    'can' => 'ViewAnyConsultation',
                    'url' => route('patient.index'),
                    'name' => ['index', 'create', 'edit', 'show'],
                    'label' => 'Consulting',
                ],
            ],
        ]);

        $menu['invoice'] = getFirstPermittedSubMenu([
            'can' => 'ViewAnyInvoice',
            'url' => route('invoice.index'),
            'label' => 'Invoice',
            'sub' => [
                'invoice' => [
                    'can' => 'ViewAnyInvoice',
                    'url' => route('invoice.index'),
                    'name' => ['index', 'create', 'edit', 'show'],
                    'label' => 'Invoice List',
                ],
                'service' => [
                    'can' => 'ViewAnyService',
                    'url' => route('invoice.service.index'),
                    'name' => ['index', 'create', 'edit', 'show'],
                    'label' => 'Services',
                ],
            ],
        ]);

        $menu['prescription'] = [
            'can' => 'ViewAnyPrescription',
            'url' => route('prescription.index'),
            'label' => 'Prescription',
            'sub' => [
                'prescription' => [
                    'can' => 'ViewAnyPrescription',
                    'url' => route('prescription.index'),
                    'name' => ['index', 'create', 'edit', 'show'],
                    'label' => 'Prescription List',
                ],
            ],
        ];

        $menu['para_clinic'] = getFirstPermittedSubMenu([
            'can' => '',
            'url' => '',
            'label' => 'Para Clinic',
            'sub' => [
                'labor' => [
                    'can' => 'ViewAnyLaboratory',
                    'url' => route('para_clinic.labor.index'),
                    'name' => ['index', 'create', 'edit', 'show'],
                    'label' => 'Laboratory',
                ],
                'xray' => [
                    'can' => 'ViewAnyXray',
                    'url' => route('para_clinic.xray.index'),
                    'name' => ['index', 'create', 'edit', 'show'],
                    'label' => 'X-Ray',
                ],
                'echography' => [
                    'can' => 'ViewAnyEchography',
                    'url' => route('para_clinic.echography.index'),
                    'name' => ['index', 'create', 'edit', 'show'],
                    'label' => 'Echography',
                ],
                'ecg' => [
                    'can' => 'ViewAnyEcg',
                    'url' => route('para_clinic.ecg.index'),
                    'name' => ['index', 'create', 'edit', 'show'],
                    'label' => 'ECG',
                ],
            ],
        ]);

        if (env('STOCK_INVENTORY', false) == true) {
            $menu['inventory'] = getFirstPermittedSubMenu([
                'can' => '',
                'url' => '',
                'label' => $nb_alerted > 0 ? '<span class="text-danger">Inventory</span>' : 'Inventory',

                'sub' => [
                    'stock_alert' => [
                        'can' => 'ViewAnyStockAlert',
                        'url' => route('inventory.stock_alert.index'),
                        'name' => ['index'],
                        'label' => 'Stock Alert ' . $stock_alert_badge,
                    ],
                    'stock_in' => [
                        'can' => 'ViewAnyStockIn',
                        'url' => route('inventory.stock_in.index'),
                        'name' => ['index', 'create', 'edit'],
                        'label' => 'Stock In',
                    ],
                    'stock_out' => [
                        'can' => 'ViewAnyStockOut',
                        'url' => route('inventory.stock_out.index'),
                        'name' => ['index', 'create', 'edit'],
                        'label' => 'Stock Out',
                    ],
                    'stock_balance' => [
                        'can' => 'ViewAnyStockBalance',
                        'url' => route('inventory.stock_balance.index'),
                        'name' => ['index'],
                        'label' => 'Stock Balance',
                    ],
                    'stock_adjustment' => [
                        'can' => 'ViewAnyStockAdjustment',
                        'url' => route('inventory.stock_adjustment.index'),
                        'name' => ['index', 'create', 'edit'],
                        'label' => 'Stock Adjustment',
                    ],
                    'separator1' => [
                        'can' => '',
                        'url' => '',
                        'name' => ['index', 'create', 'edit'],
                        'label' => '-',
                    ],
                    'product' => [
                        'can' => 'ViewAnyProduct',
                        'url' => route('inventory.product.index'),
                        'name' => ['index', 'create', 'edit'],
                        'label' => 'Product',
                    ],
                    'product_category' => [
                        'can' => 'ViewAnyProductCategory',
                        'url' => route('inventory.product_category.index'),
                        'name' => ['index', 'create', 'edit'],
                        'label' => 'Product Category',
                    ],
                    'product_unit' => [
                        'can' => 'ViewAnyProductUnit',
                        'url' => route('inventory.product_unit.index'),
                        'name' => ['index', 'create', 'edit'],
                        'label' => 'Product Unit',
                    ],
                    'product_type' => [
                        'can' => 'ViewAnyProductType',
                        'url' => route('inventory.product_type.index'),
                        'name' => ['index', 'create', 'edit'],
                        'label' => 'Product Type',
                    ],
                    'separator2' => [
                        'can' => '',
                        'url' => '',
                        'name' => ['index', 'create', 'edit'],
                        'label' => '-',
                    ],
                    'supplier' => [
                        'can' => 'ViewAnySupplier',
                        'url' => route('inventory.supplier.index'),
                        'name' => ['index', 'create', 'edit'],
                        'label' => 'Supplier',
                    ],
                ],
            ]);
        }

        $menu['setting'] = getFirstPermittedSubMenu([
            'can' => '',
            'url' => '',
            'label' => 'Setting',
            'sub' => [
                'setting' => [
                    'can' => 'DeveloperMode',
                    'url' => route('setting.edit'),
                    'name' => ['edit'],
                    'label' => 'Setting',
                ],
                'labor-type' => [
                    'can' => 'ViewAnyLaborType',
                    'url' => route('setting.labor-type.index'),
                    'name' => ['index', 'create', 'edit', 'sort_order'],
                    'label' => 'Labor Service',
                ],
                'echo-type' => [
                    'can' => 'ViewAnyEchoType',
                    'url' => route('setting.echo-type.index'),
                    'name' => ['index', 'create', 'edit', 'sort_order'],
                    'label' => 'Echo Service',
                ],
                'ecg-type' => [
                    'can' => 'ViewAnyEcgType',
                    'url' => route('setting.ecg-type.index'),
                    'name' => ['index', 'create', 'edit', 'sort_order'],
                    'label' => 'ECG Service',
                ],
                'xray-type' => [
                    'can' => 'ViewAnyXRayType',
                    'url' => route('setting.xray-type.index'),
                    'name' => ['index', 'create', 'edit', 'sort_order'],
                    'label' => 'Xray Service',
                ],
                'medicine' => [
                    'can' => 'ViewAnyMedicine',
                    'url' => route('setting.medicine.index'),
                    'name' => ['index', 'create', 'edit'],
                    'label' => 'Medicine',
                ],
                'unit' => [
                    'can' => 'ViewAnyProductUnit',
                    'url' => route('setting.unit.index'),
                    'name' => ['index', 'create', 'edit'],
                    'label' => 'Unit',
                ],
                'doctor' => [
                    'can' => 'ViewAnyDoctor',
                    'url' => route('setting.doctor.index'),
                    'name' => ['index', 'create', 'edit'],
                    'label' => 'Doctor',
                ],
                'data-parent' => [
                    'can' => 'ViewAnyDataParent',
                    'url' => route('setting.data-parent.index'),
                    'name' => ['index', 'create', 'edit'],
                    'label' => 'Data Selection',
                ],
                'address' => [
                    'can' => 'ViewAnyAddress',
                    'url' => route('setting.address.index'),
                    'name' => ['index', 'create', 'edit'],
                    'label' => 'Address',
                ],
            ],
        ]);

        $menu['user'] = getFirstPermittedSubMenu([
            'can' => '',
            'url' => '',
            'label' => 'User Managment',
            'sub' => [
                'user' => [
                    'can' => 'ViewAnyUser',
                    'url' => route('user.index'),
                    'name' => ['index', 'create', 'edit', 'ability'],
                    'label' => 'User',
                ],
                'role' => [
                    'can' => 'ViewAnyRole',
                    'url' => route('user.role.index'),
                    'name' => ['index', 'create', 'edit', 'ability'],
                    'label' => 'Role',
                ],
                'ability' => [
                    'can' => 'ViewAnyAbility',
                    'url' => route('user.ability.index'),
                    'name' => ['index', 'create', 'edit'],
                    'label' => 'Ability',
                ],
            ],
        ]);

        $setting = Setting::first();
        if (!$setting) {
            $setting = Setting::Create([
                'clinic_name_kh' => 'Clinic KH',
                'clinic_name_en' => 'Clinic EN',
                'sign_name_kh' => 'Name KH',
                'sign_name_en' => 'Name EN',
                'phone' => 'Phone',
                'address' => 'Address',
                'description' => 'Description',
            ]);
        }

        return view('layouts.app', compact('menu', 'setting'));
    }
}
