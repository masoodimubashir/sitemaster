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

                    <h3 class="text-info">Edit Square Footage Bill</h3>

                    <form method="POST"
                        action="{{ route('square-footage-bills.update', [base64_encode($square_footage_bill->id)]) }}"
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
                        <select class="form-select form-select-sm" id="exampleFormControlSelect3" name="type">
                            <option value="">Select Type</option>
                            <option {{ $square_footage_bill->type === 'per_sqr_ft' ? 'selected' : ''  }} value="per_sqr_ft">Per Square Feet</option>
                            <option {{ $square_footage_bill->type === 'per_unit' ? 'selected' : ''  }} value="per_unit">Per Unit</option>
                            <option {{ $square_footage_bill->type === 'full_contract' ? 'selected' : ''  }} value="full_contract">Full Contract
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
