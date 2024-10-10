<x-app-layout>


    <div class="row">


        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">

                    <h4 class="display-4 text-info">Make Payment</h4>


                    @if (session('message'))
                        <p class="card-description">
                            {{ session('message') }}

                        </p>
                    @endif


                    <form action="{{ route('supplier-payments.store') }}" class="forms-sample material-form" method="POST"
                        enctype="multipart/form-data">

                        @csrf

                        {{-- Phase Name --}}
                        <div class="form-group">
                            <input type="number" min="0" name="amount" />
                            <label for="input" class="control-label">Amount</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                        </div>

                        <div class="row">

                            <div class="col-md-6">
                                <!-- Site -->
                                <select class="form-select form-select-sm" id="supplier_id" name="supplier_id">
                                    <option value="">Select Supplier</option>
                                    @foreach ($sites as $site)
                                        <option value="{{ $site->id }}">
                                            {{ $site->site_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('supplier_id')
                                    <x-input-error :messages="$message" class="mt-2" />
                                @enderror
                            </div>


                            <div class="col-md-6">
                                {{-- Supplier --}}

                                <select class="form-select form-select-sm" id="supplier_id" name="supplier_id">
                                    <option value="">Select Supplier</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('supplier_id')
                                    <x-input-error :messages="$message" class="mt-2" />
                                @enderror
                            </div>
                        </div>


                        <!-- Is Verified -->
                        <div class="form-check">
                            <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" name="is_verified"> Verify
                            </label>
                            @error('is_verified')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        {{-- Screenshot --}}
                        <div class="mt-3 ">
                            <input class="form-control form-control-md" id="image" type="file" name="screenshot">
                        </div>


                        <div class=" mt-4">
                            <a href="{{ route('payments.index') }}" class="btn btn-primary">Back</a>
                            <x-primary-button>
                                {{ __('Pay') }}
                            </x-primary-button>
                        </div>


                    </form>



                </div>
            </div>
        </div>
    </div>



</x-app-layout>
