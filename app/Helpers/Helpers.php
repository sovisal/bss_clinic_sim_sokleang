<?php

use App\Models\Setting;
use Illuminate\Support\Str;
use App\Models\Address_linkable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

function InternetIsConnected()
{
    $connected = @fsockopen("www.google.com", 80);
    if ($connected) {
        fclose($connected);
        return true;
    }
    return false;
}

function can($ability)
{
    return auth()->user()->can($ability);
}

function getFirstPermittedSubMenu($menu = []){
    foreach (($menu['sub'] ?? []) as $key => $sub_menu) {
        if ($menu['can'] != '' && $menu['url'] != '') { break; }
        if (can($sub_menu['can'] ?? '')) {
            $menu['can'] = $sub_menu['can'];
            $menu['url'] = $sub_menu['url'];
        }
        if ( Str::contains($key, ['separator', 'separator1', 'separator2'])) {unset($menu['sub'][$key]); }
    }
    return $menu;
}

function mainMenuActive($active)
{
    return ($active == mainActive());
}
function subMenuActive($active, $sub = '')
{
    if ($sub != '') {
        $sub = $sub . '.';
    }
    if (is_array($active)) {
        foreach ($active as $key => $act) {
            if ($sub . $act == module() . '.' . subModule()) {
                return true;
            }
        }
    } else {
        return ($sub . $active == module() . '.' . subModule());
    }
}

function module()
{
    $routename = explode('.', Route::currentRouteName());
    return $routename[count($routename) > 1 ? count($routename) - 2 : count($routename) - 1];
}

function mainActive()
{
    $routename = explode('.', Route::currentRouteName());
    if (count($routename) > 0) {
        return $routename[0];
    }
}

function subModule()
{
    $routename = explode('.', Route::currentRouteName());
    return $routename[count($routename) - 1];
}

function breadCrumb()
{
    $routename = explode('.', Route::currentRouteName());
    $i = 0;
    $li = '';
    $active = '';
    foreach ($routename as $key => $value) {
        // GET First
        if (++$i === count($routename)) {

            $crud = ['index', 'create', 'edit', 'show', 'image'];
            // Last Active
            if (in_array($value, $crud)) {
                $active .= __('breadcrumb.crud.' . $value);
            } else {
                $active .= __('breadcrumb.module.' . $value);
            }
            $li .= '<li class="breadcrumb-item active">' . $active . '</li>';
        } else if ($key === 0) {
            // Firtst Home
            if ($value == 'home') {
                $li .= '<li class="breadcrumb-item"><a href="' . route('home') . '"><i class="fa fa-user-shield"></i> ' . __('breadcrumb.module.' . $value) . '</a></li>';
            } else if ($value == 'setting') {
                $li .= '<li class="breadcrumb-item"><a href="' . route('home') . '"> ' . __('breadcrumb.module.' . $value) . '</a></li>';
            } else {
                $li .= '<li class="breadcrumb-item"><a href="' . route($value . '.index') . '">' . __('breadcrumb.module.' . $value) . '</a></li>';
            }
        } else if (count($routename) > 3) {
            // if length 3 Level deep
            $li .= '<li class="breadcrumb-item"><a href="' . route($routename[1] . '.' . $routename[2] . '.index') . '">' . __('breadcrumb.module.' . $value) . '</a></li>';
        } else if (count($routename) > 4) {
            // if length 4 Level deep
            $li .= '<li class="breadcrumb-item"><a href="' . route($routename[1] . '.' . $routename[2] . '.' . $routename[3] . '.index') . '">' . __('breadcrumb.module.' . $value) . '</a></li>';
        } else {
            // if length normal crud
            $li .= '<li class="breadcrumb-item"><a href="' . route($value . '.index') . '">' . __('breadcrumb.module.' . $value) . '</a></li>';
        }
        // End if
    }
    // End Foreach
    return $li;
}

function round_up($value, $precision)
{
    $pow = pow(10, $precision);
    return (ceil($pow * $value) + ceil($pow * $value - ceil($pow * $value))) / $pow;
}

function is_decimal($val)
{
    return is_numeric($val) && floor($val) != $val;
}

function filter_unit_attr($attributes)
{
    $filtered_attributes = [];
    foreach ($attributes as $key => $value) {
        if (substr($key, strpos($key, "_unit") + 1) != 'unit') {
            $filtered_attributes[$key] = $value . ((array_key_exists($key . '_unit', $attributes)) ? ' ' . $attributes[$key . '_unit'] : '');
        }
    }
    return $filtered_attributes;
}

function zipFile($zip_file, $path, $destination_path, $sub_folder = true)
{
    if ($zip_file != 'file-zip.zip') {
        $zip_file .= '.zip';
    }
    // Initializing PHP class
    $zip = new \ZipArchive();
    $output_path = storage_path('zipfiles/' . $zip_file);
    if ($destination_path != '') {
        $output_path = $destination_path . $zip_file;
    }
    $zip->open($output_path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

    // Adding file: second parameter is what will the path inside of the archive
    // So it will create another folder called "storage/" inside ZIP, and put the file there.
    if ($sub_folder) {
        $zip->addFile(storage_path($path), $path);
    } else {
        $filename = substr($path, strpos($path, "/") + 1);
        $zip->addFile(storage_path($path), $filename);
    }
    $zip->close();

    return $output_path;
}

function setting($duration = 60 * 60)
{
    return cache()->remember('bss_stock_clinic.setting', $duration, function () {
        return Setting::first();
    });
}

function bg_random()
{
    $bg = [
        'blue-',
        'green-',
        'yellow-',
        'red-',
        'purple-',
        'gray-',
        'pink-',
        'indigo-',
    ];
    $string_bg = $bg[rand(0, 7)] . rand(3, 9) . '00';
    return $string_bg;
}

function getParaClinicHeaderDetail($row = null)
{
    $row = is_null($row) ? new Collection : $row;
    $status_html = d_paid_status($row->payment_status) . ' ' . d_para_status($row->status);
    return '<table class="table-form table-header-info">
                <thead>
                    <tr>
                        <th colspan="4" class="text-left tw-bg-gray-100">Patient <span class="tw-pl-2 detail-status">' . $status_html . '</span></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td width="20%" class="text-right tw-bg-gray-100">Form</td>
                        <td class="type">' . d_obj($row, 'type', ['name_en', 'name_kh']) . '</td>
                        <td width="20%" class="text-right tw-bg-gray-100">Code</td>
                        <td class="code">' . $row->code . '</td>
                    </tr>
                    <tr>
                        <td class="text-right tw-bg-gray-100">Name</td>
                        <td class="name">' . d_obj($row, 'patient', ['name_en', 'name_kh']) . '</td>
                        <td class="text-right tw-bg-gray-100">Requested date</td>
                        <td class="requested_date">' . date('d/m/Y H:i', strtotime($row->requested_at)) . '</td>
                    </tr>
                    <tr>
                        <td class="text-right tw-bg-gray-100">Requested by</td>
                        <td class="reqeusted_by">' . d_obj($row, 'doctor_requested', ['name_en', 'name_kh']) . '</td>
                        <td class="text-right tw-bg-gray-100">Physician</td>
                        <td class="physician">' . d_obj($row, 'doctor', ['name_en', 'name_kh']) . '</td>
                    </tr>
                    <tr>
                        <td class="text-right tw-bg-gray-100">Payment type</td>
                        <td class="payment_type">' . d_obj($row, 'payment', ['title_en', 'title_kh']) . '</td>
                        <td class="text-right tw-bg-gray-100">Amount</td>
                        <td class="amount">' . d_currency($row->price) . '</td>
                    </tr>
                </tbody>
            </table>';
}

// How to use in view : ' . getParentDataByType('enterprise', 1) . '
function getParentDataByType(...$param)
{
    return \App\Http\Controllers\DataParentController::getParentDataByType(...$param);
}

// How to use getParentDataSelection('enterprise')
function getParentDataSelection(...$param)
{
    return \App\Http\Controllers\DataParentController::getParentDataSelection(...$param);
}

// 4 Level address selector
function get4LevelAdressSelector(...$param)
{
    $_4level_address = new \App\Http\Controllers\FourLevelAddressController();
    $_4level_level = $_4level_address->BSSFullAddress(...$param);
    return $_4level_level;
}

function get4LevelAdressSelectorByID($id, ...$param)
{
    if (Address_linkable::where('id', $id)->count() == 1) {
        $address = Address_linkable::findOrFail($id);
        $param[0] = $address->village_code ?: $address->commune_code ?: $address->district_code ?: $address->province_code ?: 'xx';
    }
    return get4LevelAdressSelector(...$param);
}

function update4LevelAddress($request, $address_id = null)
{
    if ($address_id || $request->address_id) {
        return app('App\Http\Controllers\AddressLinkableController')->update($request, $address_id ?: $request->address_id);
    } else {
        return app('App\Http\Controllers\AddressLinkableController')->store($request);
    }
}

function duplicate4LevelAddress($request, $obj_source) {
    $request->pt_province_id = $obj_source->province_code;
    $request->pt_district_id = $obj_source->district_code;
    $request->pt_commune_id = $obj_source->commune_code;
    $request->pt_village_id = $obj_source->village_code;
    return app('App\Http\Controllers\AddressLinkableController')->store($request);
}

function delete4LevelAddress($addres_id)
{
    return app('App\Http\Controllers\AddressLinkableController')->destroy($addres_id);
}

function append_array_to_obj(&$obj, $arr)
{
    foreach ($arr ?: [] as $index => $val) {
        $obj->{$index} = $obj->{$index} ?: $val ?: '';
    }
    return $obj;
}

function data_parent_selection_conf()
{
    return  [
        'gender' => [
            'label' => 'Gender',
            'is_invisible' => false,
        ],
        'marital_status' => [
            'label' => 'Marital Status',
        ],
        'blood_type' => [
            'label' => 'Blood Type',
        ],
        'nationality' => [
            'label' => 'Nationality',
        ],
        'enterprise' => [
            'label' => 'Enterprise',
        ],
        'payment_type' => [
            'label' => 'Payment Type',
        ],
        'payment_status' => [
            'label' => 'Payment Status',
        ],
        'evalutaion_category' => [
            'label' => 'Indication Category',
        ],
        'indication_disease' => [
            'label' => 'Indication',
            'is_child' => true,
            'child_of' => 'evalutaion_category'
        ],
        'comsumption' => [
            'label' => 'Comsumption',
        ],
        'time_usage' => [
            'label' => 'Usage Time',
        ],
        'status' => [
            'label' => 'Status',
        ],
    ];
}

function apply_markdown_character($txt = '')
{
    $result = [];
    if (!empty($txt)) {
        $result = $txt = str_split($txt);
        foreach ($txt as $key => $val) {
            if ($val == '^') {
                $result[$key] = '';
                $result[$key + 1] = '<sup>' . $result[$key + 1] . '</sup>';
            }
        }
    }

    return implode($result);
}

function render_readable_date($date)
{
    if ($date ?? false) {
        return date('d-M-Y', strtotime($date));
    }
}

function render_currency($amn, $digit = 2, $currency = 'USD', $id_suffix_display = false)
{
    $amn = $amn ?? 0;
    if ($id_suffix_display) {
        return number_format($amn, $digit) . ' ' . $currency;
    }
    return $currency . ' ' . number_format($amn, $digit);
}

function render_record_status($st_num = 0)
{
    $label = '';
    switch ($st_num) {
        case 0:
            $label = '<span class="badge badge-light">Disabled</span>';
            break;
        case 1:
            $label = '<span class="badge badge-light">Draft</span>';
            break;
        case 2:
            $label = '<span class="badge badge-success">Completed</span>';
            break;
        default:
            $label = '<span class="badge badge-light">' . $st_num . '</span>';
    }
    return $label;
}

function render_payment_status($st_num = 0)
{
    $label = '';
    switch ($st_num) {
        case 0:
            $label = '<span class="badge badge-light">Unpaid</span>';
            break;
        case 1:
            $label = '<span class="badge badge-success">Paid</span>';
            break;
    }
    return $label;
}


function generate_code($prefix, $table_name, $auto_update = true)
{
    $year = $table_name == 'invoices' ? date('Y') : '2023';
    $table_info = DB::select("SELECT increment FROM module_code_generation WHERE module='{$table_name}' AND year = '{$year}';");
    if (sizeof($table_info) == 0) {
        DB::insert('INSERT INTO module_code_generation (module, increment, year) VALUES (?, ?, ?)', [$table_name, 0, $year]);
    }

    $code_increment = $table_info ? ((reset($table_info)->increment) + 1) : 1;
    if ($auto_update) {
        DB::update('UPDATE module_code_generation SET increment=increment+1 WHERE module=? AND year=?', [$table_name, $year]);
    }

    if ($table_name == 'products') {
        return $prefix . '-' . str_pad($code_increment, 7, "0", STR_PAD_LEFT);
    } else {
        return $prefix . '-' . str_pad($code_increment, 5, "0", STR_PAD_LEFT);
    }
}

function render_synonyms_name($name_en = '', $name_kh = '', $separator = '::')
{
    if (!empty($name_en) && !empty($name_kh) && trim(strtolower($name_en)) != trim(strtolower($name_kh))) {
        return $name_en . ' ' . $separator . ' ' . $name_kh;
    } else {
        return $name_en ?: $name_kh ?: 'N/A';
    }
}

function validateDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

function d_text($txt = null, $default = '-')
{
    return $txt ?: $default;
}

function d_link($label = null, $link = null, $target = '_self')
{
    return '<a class="d_link" href="' . $link . '">' . $label . '</a>';
}

function d_combine_obj($obj, $keys, $separator = ' :: ')
{
    return implode($separator, array_filter(array_unique(array_map(function ($key) use ($obj) {
        return $obj->{$key};
    }, $keys)), 'strlen'));
}

function d_combine_array($arr, $keys, $separator = ' :: ')
{
    return implode($separator, array_unique(array_map(function ($key) use ($arr) {
        return trim($arr[$key]);
    }, $keys)));
}

function d_obj($obj = null, $param1 = null, $param2 = null, $param3 = null)
{
    $result = $obj;

    if ($result && $param1) {
        if (is_array($param1)) {
            $result = d_combine_obj($result, $param1);
        } else {
            $result = $result->$param1;
        }
    } else {
        return d_text($result);
    }

    if ($result && $param2) {
        if (is_array($param2)) {
            $result = d_combine_obj($result, $param2);
        } else {
            $result = $result->$param2;
        }
    } else {
        return d_text($result);
    }

    if ($result && $param3) {
        if (is_array($param3)) {
            $result = d_combine_obj($result, $param3);
        } else {
            $result = $result->$param3;
        }
    } else {
        return d_text($result);
    }

    return $result;
}

function d_array($array = null, $param1 = null, $param2 = null, $param3 = null)
{
    $result = $array;

    if ($result && $param1) {
        if (is_array($param1)) {
            $result = d_combine_array($result, $param1);
        } else {
            $result = $result[$param1];
        }
    } else {
        return d_text($result);
    }

    if ($result && $param2) {
        if (is_array($param2)) {
            $result = d_combine_array($result, $param2);
        } else {
            $result = $result[$param2];
        }
    } else {
        return d_text($result);
    }

    if ($result && $param3) {
        if (is_array($param3)) {
            $result = d_combine_array($result, $param3);
        } else {
            $result = $result[$param3];
        }
    } else {
        return d_text($result);
    }
}

function d_number($num, $default = '-')
{
    return is_numeric($num) ? floatval($num) : $default;
}

function d_badge($txt, $type = 'type')
{
    return '<span class="badge badge-' . $type . '">' . $txt . '</span>';
}

function d_currency($amn, $digit = 2, $currency = 'USD', $id_suffix_display = false)
{
    $amn = $amn ?? 0;
    if ($id_suffix_display) {
        return number_format($amn, $digit) . ' ' . $currency;
    }
    return $currency . ' ' . number_format($amn, $digit);
}

function d_date($date, $format = 'Y-m-d H:i:s', $default = '-')
{
    return validateDate($date, $format) ? date('d-M-Y', strtotime($date)) : $default;
}

function d_date_time($date, $format = 'Y-m-d H:i:s', $default = '-')
{
    return validateDate($date, $format) ? date('d-M-Y h:i A', strtotime($date)) : $default;
}

function d_labor_range($min = '', $max = '')
{
    return ($min != '' && $max != '') ? $min . '-' . $max  : '';
}

function d_status($status, $false = 'Inactive', $true = 'Active', $false_badge = 'badge-danger', $true_badge = 'badge-primary')
{
    if ($status) {
        return '<span class="badge ' . $true_badge . '">' . $true . '</span>';
    }

    return '<span class="badge ' . $false_badge . '">' . $false . '</span>';
}

function d_action($param)
{
    foreach ([
        'id', 'module' => '', 'moduleAbility', 'isTrashed' => false,
        'disableShow' => false, 'disableEdit' => false, 'disableDelete' => false, 'disableRestore' => false, 'disableForceDelete' => false,
        'showBtnShow' => true, 'showBtnEdit' => true, 'showBtnDelete' => true, 'showBtnRestore' => true, 'showBtnForceDelete' => false,
        'paraImage' => [], 'showBtnPrint' => false,
        'showCustomBtn' => [], 'deleteCustomBtn' => [], 'editCustomBtn' => [],
    ] as $field => $val) { $param[$field] = $param[$field] ?? $val; }
    

    $render_result = '';

    if (sizeof($param['paraImage']) > 0) {
        $render_result .= '<a onclick="getImage(\'' . reset($param['paraImage']) . '\',\'' . next($param['paraImage']) . '\')" class="btn btn-sm btn-warning btn-icon" title="Image">
            <i class="bx bx-image"></i> 
        </a> ';
    }

    if ($param['showBtnPrint']) {
        if (can('Print'. Str::ucfirst($param['moduleAbility'] ?? $param['module']))) {
            $render_result .= '<a onclick="printPopup(\'' . route($param['module'] .'.print', $param['id']) . '\')" class="btn btn-sm btn-dark btn-icon" title="Print">
                <i class="bx bx-printer"></i> 
            </a> ';
        }
    }
    
    
    if($param['showCustomBtn']) {
        if (can($param['showCustomBtn'][0])) {
            $render_result .= '<a href="' . $param['showCustomBtn'][1] . '" class="btn btn-sm btn-primary btn-icon">
                <i class="bx bx-detail"></i> 
            </a> ';
        }
    } elseif($param['showBtnShow']) {
        if (can('ViewAny'. Str::ucfirst($param['moduleAbility'] ?? $param['module']))) {
            $render_result .= '<a href="' . route($param['module'] .'.show', $param['id']) . '" class="btn btn-sm btn-primary btn-icon" title="Show">
                <i class="bx bx-detail"></i> 
            </a> ';
        }
    }

    if ($param['editCustomBtn']) {
        if (can($param['editCustomBtn'][0])) {
            if ($param['disableEdit']) {
                $render_result .= ' <a href="#" class="btn btn-sm btn-secondary btn-icon btn-sm disabled"><i class="bx bx-edit-alt"></i></a> ';
            } else {
                $render_result .= ' <a href="' . $param['editCustomBtn'][1] . '" class="btn btn-sm btn-secondary btn-icon btn-sm" title="Edit">
                    <i class="bx bx-edit-alt"></i>
                </a> ';
            }
        }
    } elseif($param['showBtnEdit']) {
        if (can('Update'. Str::ucfirst($param['moduleAbility'] ?? $param['module']))) {
            if ($param['disableEdit']) {
                $render_result .= ' <a href="#" class="btn btn-sm btn-secondary btn-icon btn-sm disabled"><i class="bx bx-edit-alt"></i></a> ';
            } else {
                $render_result .= ' <a href="' . route($param['module'] .'.edit', $param['id']) . '" class="btn btn-sm btn-secondary btn-icon btn-sm" title="Edit">
                    <i class="bx bx-edit-alt"></i>
                </a> ';
            }
        }
    }

    if($param['deleteCustomBtn']) {
        if (can($param['deleteCustomBtn'][0])) {
            if ($param['disableDelete']) {
                $render_result .= '<button type="button" class="btn btn-sm btn-danger btn-icon btn-sm disabled">
                    <i class="bx bx-trash"></i> 
                </button> ';
            } else {
                $render_result .= '<button type="button" class="btn btn-sm btn-danger btn-icon confirmDelete btn-sm" data-id="' . $param['id'] . '" title="Delete">
                    <i class="bx bx-trash"></i> 
                </button>
                <form class="sr-only" id="form-delete-' . $param['id'] . '" action="' . $param['deleteCustomBtn'][1] . '" method="POST">
                    <input type="hidden" name="_token" value="' . csrf_token() .'" />
                    <input type="hidden" name="_method" value="delete" />
                    <button class="sr-only" id="btn-' . $param['id'] . '">Delete</button>
                </form> ';
            }
        }
    } elseif($param['showBtnDelete']) {
        if (can('Delete'. Str::ucfirst($param['moduleAbility'] ?? $param['module']))) {
            if ($param['disableDelete']) {
                $render_result .= '<button type="button" class="btn btn-sm btn-danger btn-icon btn-sm disabled">
                    <i class="bx bx-trash"></i> 
                </button> ';
            } else {
                $render_result .= '<button type="button" class="btn btn-sm btn-danger btn-icon confirmDelete btn-sm" data-id="' . $param['id'] . '" title="Delete">
                    <i class="bx bx-trash"></i> 
                </button>
                <form class="sr-only" id="form-delete-' . $param['id'] . '" action="' . route($param['module'] .'.delete', $param['id']) . '" method="POST">
                    <input type="hidden" name="_token" value="' . csrf_token() .'" />
                    <input type="hidden" name="_method" value="delete" />
                    <button class="sr-only" id="btn-' . $param['id'] . '">Delete</button>
                </form> ';
            }
        }
    }

    return $render_result;
}

function d_para_status($status, $active = 'Active', $closed = 'Completed')
{
    if ($status == 2) {
        return '<span class="badge badge-success">' . $closed . '</span>';
    }

    return '<span class="badge badge-parimary">' . $active . '</span>';
}

function d_paid_status($status = 0)
{
    if ($status == '1') {
        return '<span class="badge badge-success">Paid</span>';
    } elseif ($status == '2') {
        return '<span class="badge badge-danger">Refunded</span>';
    } else {
        return '<span class="badge badge-dark">Unpaid</span>';
    }
}

function d_exchange_rate()
{
    return 4100;
}

// Param : (File Name, Main Path)
function remove_file($file_name, $path)
{
    if ($file_name && File::exists($path . $file_name)) {
        File::delete($path . $file_name);
    }
}

// Param : (Request->image, Main Path, New Image name)
function create_image($image, $path, $image_name = null)
{
    if ($image && $image != '/images/browse-image.jpg') {
        // Get image Data
        $data = $image;
        list(, $data) = explode(';', $data);
        list(, $data) = explode(',', $data);
        $data = base64_decode($data);
        // Set Image Name and Path
        $image_name = $image_name ?: time() . '_image_' . rand(111, 999) . '.png';
        // put image to folder/dir and update supplier field
        file_put_contents(($path . $image_name), $data);
        return $image_name;
    }
    return null;
}

// Param : (Request->image, Main Path, New Image name, Old image name)
function update_image($image, $path, $image_name = null, $old_image = null)
{
    if ($image) {
        // set image name and path
        $image_name = $image_name ?: time() . '_image_' . rand(111, 999) . '.png';
        // Check if reset/delete image
        if ($image == '/images/browse-image.jpg') {
            remove_file($old_image, $path);
            $image_name = null;
        } else {
            // get image data
            $data = $image;
            list(, $data) = explode(';', $data);
            list(, $data) = explode(',', $data);
            $data = base64_decode($data);
            // put image to folder/dir
            file_put_contents($path . $image_name, $data);
            remove_file($old_image, $path);
        }
        return $image_name;
    }
    return $old_image ?: null;
}
