<x-app-layout>

    @php
        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp

    <x-breadcrumb :names="['Suppliers', 'Edit ' . $supplier->name]" :urls="[$user . '/suppliers', $user . '/suppliers/' . $supplier->id . '/edit']" />

    @if (session('status') === 'supplier')
        <x-success-message message="supplier not found.." />
    @endif

    <div class="row">


        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">


                    <form method="POST" action="{{ url($user . '/suppliers',  $supplier->id) }}"
                        class="forms-sample material-form">

                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <input type="text" name="name" value="{{ $supplier->name }}" />
                            <label for="input" class="control-label">Supplier Name</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />

                        </div>

                        <div class="form-group">
                            <input type="text" name="contact_no" value="{{ $supplier->contact_no }}" />
                            <label for="number" class="control-label">Contact Number</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('contact_no')" class="mt-2" />
                        </div>




                        <div class="form-group">
                            <textarea name="address">{{ $supplier->address }}</textarea>
                            <label for="textarea" class="control-label">Address</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('address')" class="mt-2" />
                        </div>

                        <div class="form-group flex gap-4">
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="provider"
                                        id="is_raw_material_provider1" value="is_raw_material_provider"
                                        {{ $supplier->is_raw_material_provider ? 'checked' : '' }}> Raw
                                    Material Provider
                                </label>
                                @error('provider')
                                    <span class="text-red-500 fw-bold  text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="provider"
                                        id="is_workforce_provider22" value="is_workforce_provider"
                                        {{ $supplier->is_workforce_provider ? 'checked' : '' }}> Workforce
                                    Provider
                                </label>

                            </div>
                        </div>


                        <button class="btn btn-info"><span>Save</span></button>


                    </form>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
