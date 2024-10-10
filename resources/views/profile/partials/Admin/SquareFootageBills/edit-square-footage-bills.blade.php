<x-app-layout>


    <div class="row">

        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">

                    <h3 class="text-info">Edit Square Footage Bill</h3>

                    @if (session('message'))
                        <p class="card-description">
                            {{ session('message') }}

                        </p>
                    @endif

                    <form method="POST"
                        action="{{ route('square-footage-bills.update', [base64_encode($square_footage_bill->id)]) }}"
                        class="forms-sample material-form">

                        @method('PUT')
                        @csrf

                        @if (session('message'))
                            {{ session('message') }}
                        @endif

                        @if (session('error'))
                            {{ session('error') }}
                        @endif



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
                        <select class="form-select form-select-sm" id="exampleFormControlSelect3" name="type">
                            <option value="">Select Type</option>
                            <option value="per_sqr_ft">Per Square Feet</option>
                            <option value="per_unit">Per Unit</option>
                            <option value="full_contract">Full Contract
                            </option>
                        </select>
                        @error('type')
                            <x-input-error :messages="$type" class="mt-2" />
                        @enderror


                        <!-- Select Supplier -->
                        <div class="form-group">
                            <input id="multiplier" type="number" name="supplier_id"
                                value="{{ $square_footage_bill->supplier->id }}" />
                            <label for="supplier_id" class="control-label">Multiplier</label><i class="bar"></i>

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
                            <label for="image">Item Bill</label>
                            <input class="form-control form-control-md" id="image" type="file" name="image_path">
                            @error('image_path')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                       <div class="flex items-center justify-end mt-4">

                            <div class="button-container">

                                <a class=" btn btn-info"
                                    href="{{ route('sites.show', [base64_encode($square_footage_bill->phase->site->id)]) }}"><span>Back</span></a>

                                <button class="btn btn-info"><span>Update Billing</span></button>

                            </div>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>

</x-app-layout>
