<x-app-layout>

     <x-breadcrumb :names="['View ' . $daily_wager->phase->site->site_name, 'Edit ' . $daily_wager->wager_name]" :urls="[
        'admin/sites/' . base64_encode($daily_wager->phase->site->id),
        'admin/dailywager/' . base64_encode($daily_wager->id) . '/edit',
    ]" />

    <div class="row">


        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">


                    <form method="POST" action="{{ route('dailywager.update', [$daily_wager->id]) }}"
                        class="forms-sample material-form">

                        @method('PUT')
                        @csrf

                        <!-- Wager Name -->
                        <div class="form-group">
                            <input id="wager_name" type="text" name="wager_name"
                                value="{{ $daily_wager->wager_name }}" />
                            <label for="wager_name" class="control-label">Wager Name</label><i class="bar"></i>

                            @error('wager_name')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <!-- Price Per day -->
                        <div class="form-group">
                            <input id="price_per_day" type="number" name="price_per_day"
                                value="{{ $daily_wager->price_per_day }}" step="0.01" />
                            <label for="price_per_day" class="control-label">Price Per
                                Day</label><i class="bar"></i>

                            @error('price_per_day')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>



                        <!-- Select Supplier -->
                        <div class="form-group">
                            <input id="supplier_id" type="hidden" name="supplier_id"
                                value="{{ $daily_wager->supplier->id }}" />
                            @error('supplier_id')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>


                        <!-- Select Phase -->
                        <div class=" col-md-6 mt-3">
                            <input id="phase_id" type="hidden" name="phase_id" placeholder="Phase"
                                value="{{ $daily_wager->phase->id }}" />
                            @error('phase_id')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <button class="btn btn-info"><span>Save</span></button>

                    </form>


                </div>
            </div>
        </div>
    </div>

</x-app-layout>
