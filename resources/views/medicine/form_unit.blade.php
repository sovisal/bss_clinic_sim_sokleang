<table class="table-form table-padding-sm table-striped table-unit">
    <thead>
        <tr>
            <th colspan="10" class="tw-bg-gray-100">
                <div class="d-flex justify-content-between align-items-center">
                    <label>Product Package</label>
                    <div>
                        <x-form.button class="btn-add-package" icon="bx bx-plus" label="Add Package" />
                    </div>
                </div>
            </th>
        </tr>
        <tr>
            <th><label>Unit</label> <small class="required">*</small></th>
            <th width="15%"><label>Price</label></th>
            <th width="30%"><label>Code</label></th>
            <th width="8%"><label>Action</label></th>
        </tr>
    </thead>
    <tbody>
        <!-- JS dynamic -->
        @foreach (@$row->packages ?: [] as $package)
        @include('medicine.form_unit_sample')
        @endforeach
    </tbody>
</table>

