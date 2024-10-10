<x-app-layout>


    <div class="row">


        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">

                    <h4 class="display-4 text-info">Create Supplier</h4>


                    @if (session('message'))
                        <p class="card-description">
                            {{ session('message') }}

                        </p>
                    @endif


                    <form method="POST" action="{{ route('suppliers.store') }}" class="forms-sample material-form">

                        @csrf

                        @if (session('message'))
                            {{ session('message') }}
                        @endif


                        <div class="form-group">
                            <input type="text" name="name" />
                            <label for="input" class="control-label">Supplier Name</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />

                        </div>
                        <div class="form-group">
                            <input type="text" name="contact_no" />
                            <label for="number" class="control-label">Contact Number</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('contact_no')" class="mt-2" />
                        </div>

                        <div class="form-group flex gap-4">
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="provider_type"
                                        id="is_raw_material_provider1" value="is_raw_material_provider"> Raw
                                    Material Provider
                                </label>
                            </div>
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="provider_type"
                                        id="is_workforce_provider22" value="is_workforce_provider"> Workforce
                                    Provider
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <textarea name="address"></textarea>
                            <label for="textarea" class="control-label">Address</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('address')" class="mt-2" />
                        </div>




                        <div>

                            <a class=" btn btn-success" href="{{ route('suppliers.index') }}"><span>Back</span></a>

                            <button class=" btn btn-primary"><span>Submit</span></button>

                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>


</x-app-layout>
