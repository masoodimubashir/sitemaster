<x-app-layout>

    <x-breadcrumb :names="['Suppliers', 'Create Supplier']" :urls="['admin/suppliers', 'admin/suppliers/create']" />


    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">

            <div class="card">


                <div class="card-body">

                    <form method="POST" action="{{ route('suppliers.store') }}" class="forms-sample material-form">

                        @csrf




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


                        <div class="form-group">
                            <textarea name="address"></textarea>
                            <label for="textarea" class="control-label">Address</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('address')" class="mt-2" />
                        </div>


                        <div class="col-md-6 d-flex justify-content-between">
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
                        </div>



                        <button class=" btn btn-info"><span>Save</span></button>

                    </form>
                </div>
            </div>
        </div>
    </div>


</x-app-layout>
