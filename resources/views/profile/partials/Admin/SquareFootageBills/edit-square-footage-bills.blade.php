<x-app-layout>

    @php
        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp

    <x-breadcrumb :names="['View ' . $square_footage_bill->phase->site->site_name, 'Edit ' . $square_footage_bill->wager_name]" :urls="[
        $user . '/sites/' . base64_encode($square_footage_bill->phase->site->id),
        $user . '/square-footage-bills/' . base64_encode($square_footage_bill->id) . '/edit',
    ]" />

    <div class="row">

        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">


                    <form method="POST"
                        action="{{ url($user . '/square-footage-bills/'.  base64_encode($square_footage_bill->id)) }}"
                        class="forms-sample material-form" enctype="multipart/form-data">

                        @method('PUT')
                        @csrf

                        <!-- Wager Name -->
                        <div class="form-group">
                            <input id="wager_name" type="text" name="wager_name"
                                value="{{ $square_footage_bill->wager_name }}" />
                            <label for="wager_name" class="control-label" />Wager Name</label><i class="bar"></i>

                            @error('wager_name')
                                <x-input-error :messages="$wager_name" class="mt-2" />
                            @enderror
                        </div>

                        <!-- Price -->
                        <div class="form-group">
                            <input id="price" type="number" name="price"
                                value="{{ $square_footage_bill->price }}" />
                            <label for="price" class="control-label" />Price</label><i class="bar"></i>

                            @error('price')
                                <x-input-error :messages="$price" class="mt-2" />
                            @enderror
                        </div>

                        <!-- Number Of Days -->
                        <div class="form-group">
                            <input id="multiplier" type="number" name="multiplier"
                                value="{{ $square_footage_bill->multiplier }}" />
                            <label for="multiplier" class="control-label">Multiplier</label><i class="bar"></i>

                            @error('multiplier')
                                <x-input-error :messages="$multiplier" class="mt-2" />
                            @enderror
                        </div>

                        <!-- Type -->
                        <label for="date" class="mb-1" style="font-size: 0.8rem; color: rgba(17, 17, 17, 0.48);">
                            Select Type
                        </label>
                        <select class="form-select form-select-sm text-black" id="type" name="type"
                            style="cursor: pointer">
                            <option {{ $square_footage_bill->type === 'per_sqr_ft' ? 'selected' : '' }}
                                value="per_sqr_ft">Per Square Feet</option>
                            <option {{ $square_footage_bill->type === 'per_unit' ? 'selected' : '' }} value="per_unit">
                                Per Unit</option>
                            <option {{ $square_footage_bill->type === 'full_contract' ? 'selected' : '' }}
                                value="full_contract">Full Contract
                            </option>
                        </select>
                        @error('type')
                            <x-input-error :messages="$type" class="mt-2" />
                        @enderror


                        <!-- Select Supplier -->
                        <div class="form-group">
                            <input id="multiplier" type="hidden" name="supplier_id"
                                value="{{ $square_footage_bill->supplier->id }}" />

                            @error('supplier_id')
                                <x-input-error :messages="$supplier_id" class="mt-2" />
                            @enderror
                        </div>


                        <div class="form-group">

                            <input id="phase_id" type="hidden" name="phase_id" placeholder="Phase"
                                value="{{ $square_footage_bill->phase->id }}" />

                            @error('phase_id')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <!-- Image -->
                        <div class="mt-3">
                            <label for="date" class="mb-1"
                                style="font-size: 0.8rem; color: rgba(17, 17, 17, 0.48);">
                                Item Bill
                            </label>
                            <input class="form-control form-control-md" id="image" type="file" name="image_path">
                            @error('image_path')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <button class="btn btn-info mt-4">Save</button>

                    </form>

                </div>
            </div>
        </div>
    </div>

</x-app-layout>
