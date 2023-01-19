<?php

namespace App\View\Components;

use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\View\Component;

class FilterReport extends Component
{

    public $url;

    /**
     * Create a new component instance.
     */
    public function __construct($url = '')
    {
        $this->url = $url;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        $data['patients'] = Patient::where('id', request()->ft_patient_id)->get();
        $data['doctors'] = Doctor::with(['user', 'address'])->orderBy('name_kh', 'asc')->get();
        return view('components.para-clinic.filter-report', $data);
    }
}
