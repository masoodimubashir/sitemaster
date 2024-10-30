<x-app-layout>


    <div class="row">

        @if (session('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert" id="alert-box">
                {{ session('message') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">

                    <h3 class="text-info">Update Construction Material</h3>



                    <form method="POST" class="forms-sample material-form"
                        action="{{ route('construction-material-billings.update', [$construction_material_billing->id]) }}"
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

                        <!-- Select Site -->
                        <div class="form-group">
                            <input type="hidden" name="site_id"
                                value="{{ $construction_material_billing->phase->site->id }}" />
                            <x-input-error :messages="$errors->get('site_id')" class="mt-2" />

                        </div>
                        
                        <!-- Supplier -->
                        <div class="mt-4">
                            <select id="supplier_id" class="form-select form-select-sm" name="supplier_id">
                                <option value="">Select Supplier</option>
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
                            <x-input-label for="image" :value="__('Item Bill')" />
                            <input class="form-control form-control-md" id="image" type="file" name="image">
                            @error('image')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        {{-- <!-- Verified By Admin -->
                        <div class="mt-4">
                            <x-input-label for="verified_by_admin" :value="__('Verified By Admin')" />
                            <input id="verified_by_admin" type="checkbox" class="block mt-1" name="verified_by_admin"
                                {{ $construction_material_billing->verified_by_admin ? 'checked' : '' }}
                                autocomplete="verified_by_admin" />
                            @error('verified_by_admin')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div> --}}


                        <div class="flex items-center justify-end mt-4">

                            <div class="button-container">

                                <a class=" btn btn-info"
                                    href="{{ route('sites.show', [base64_encode($construction_material_billing->phase->site->id)]) }}"><span>Back</span></a>

                                <button class="btn btn-info"><span>Update Billing</span></button>

                            </div>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>

</x-app-layout>
