<x-app-layout>


    @php

        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';

    @endphp

    <x-breadcrumb :names="[
        'View' . $construction_material_billing->phase->site->site_name,
        'Edit ' . $construction_material_billing->item_name,
    ]" :urls="[
        'admin/sites/' . base64_encode($construction_material_billing->phase->site->id),
        'admin/construction-material-billings/' . base64_encode($construction_material_billing->id),
    ]" />


    <div class="row">

        <div class="col-md-12 grid-margin stretch-card">

            <div class="card">

                <div class="card-body">

                    <form method="POST" class="forms-sample material-form"
                        action="{{ url($user . '/construction-material-billings', [$construction_material_billing->id]) }}"
                        enctype="multipart/form-data">

                        @csrf
                        @method('PUT')

                        <!-- Item Name -->
                        <div class="form-group">
                            <input type="text" name="item_name"
                                value="{{ $construction_material_billing->item_name }}" />
                            <label for="input" class="control-label">Item Name</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('item_name')" class="mt-2" />

                        </div>

                        <!-- Amount -->
                        <div class="form-group">
                            <input type="number" name="amount" value="{{ $construction_material_billing->amount }}" />
                            <label for="input" class="control-label">Amount</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                        </div>

                        <!-- Unit -->
                        <div class="form-group mb-3 ">
                            <input type="number" name="unit_count" id="unit_count" value="{{ $construction_material_billing->unit_count }}" />
                            <label for="unit_count" class="control-label">Units</label>
                            <i class="bar"></i>
                            <p class="mt-1 text-danger" id="unit_count-error"></p>
                        </div>

                        <!-- Select Site -->
                        <div class="form-group">
                            <input type="hidden" name="site_id"
                                value="{{ $construction_material_billing->phase->site->id }}" />
                            <x-input-error :messages="$errors->get('site_id')" class="mt-2" />

                        </div>

                        <!-- Supplier -->

                        <div class="mt-4">

                            <label for="supplier_id" class="mb-1"
                                style="font-size: 0.8rem; color: rgba(17, 17, 17, 0.48);">Select Supplier</label>
                            <select id="supplier_id" class="form-select form-select-sm text-black" name="supplier_id">
                                @foreach ($suppliers as $supplier)
                                    <option
                                        {{ $construction_material_billing->supplier_id === $supplier->id ? 'selected' : '' }}
                                        value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>

                            @error('supplier_id')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <!-- Phases -->
                        <div class="form-group">
                            <input type="hidden" name="phase_id"
                                value="{{ $construction_material_billing->phase->id }}" />
                            <x-input-error :messages="$errors->get('phase_id')" class="mt-2" />

                        </div>
                        @error('phase_id')
                            <x-input-error :messages="$message" class="mt-2" />
                        @enderror

                        <!-- Item Bill Photo -->
                        <div class="mt-4">
                            <label for="image" class="mb-1"
                                style="font-size: 0.8rem; color: rgba(17, 17, 17, 0.48);">Item Bill</label>
                            <input class="form-control form-control-md" id="image" type="file" name="image">
                            @error('image')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <button class="btn btn-success mt-4">Save</button>

                    </form>

                </div>
            </div>
        </div>
    </div>

</x-app-layout>
