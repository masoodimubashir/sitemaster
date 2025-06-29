<x-app-layout>


    <x-breadcrumb :names="['Sites', 'Create Site']" :urls="['user/dashboard', 'user/sites/create']" />


    <div class="row">

        <div class="col-md-12 grid-margin stretch-card">

            <div class="card">

                <div class="card-body">


                    <form method="POST" action="{{ url('user/sites/store') }}" class="forms-sample material-form">

                        @csrf


                        <div class="form-group">
                            <input type="text" name="site_name" value="{{ old('site_name') }}"/>
                            <label for="input" class="control-label">Site Name</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('site_name')" class="mt-2" />
                        </div>

                        <div class="form-group">
                            <input type="number" name="service_charge" placeholder="....10%..." step="0.01"  value="{{ old('service_charge') }}"/>
                            <label for="input" class="control-label">Service Charge</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('service_charge')" class="mt-2" />
                        </div>

                        <div class="form-group">
                            <input type="text" name="location" value="{{ old('location') }}"/>
                            <label for="input" class="control-label">Location</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('location')" class="mt-2" />
                        </div>

                        <div class="row">


                            <div class="col-md-6">

                                <input type="hidden" value="{{ auth()->user()->id }}" name="user_id" />
                            </div>

                            <div class="col-12">

                                <select class="form-select form-select-sm text-black" style="cursor: pointer" id="exampleFormControlSelect3"
                                    name="client_id">
                                    <option value="">Select Client</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}">{{ ucfirst($client->name) }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
                            </div>

                        </div>

                        <button class=" btn btn-success mt-3"><span>Save</span></button>

                    </form>
                </div>
            </div>
        </div>
    </div>

 

</x-app-layout>
