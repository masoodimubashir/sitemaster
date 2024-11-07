<x-app-layout>


    <x-breadcrumb :names="['Sites', 'Create Site']" :urls="['admin/sites', 'admin/sites/create']" />


    <div class="row">

        <div class="col-md-12 grid-margin stretch-card">

            <div class="card">

                <div class="card-body">


                    <form method="POST" action="{{ route('sites.store') }}" class="forms-sample material-form">

                        @csrf


                        <div class="form-group">
                            <input type="text" name="site_name" />
                            <label for="input" class="control-label">Site Name</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('site_name')" class="mt-2" />
                        </div>

                        <div class="form-group">
                            <input type="number" name="service_charge" placeholder="....10%..." step="0.01" />
                            <label for="input" class="control-label">Service Charge</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('service_charge')" class="mt-2" />
                        </div>

                        <div class="form-group">
                            <input type="text" name="location" />
                            <label for="input" class="control-label">Location</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('location')" class="mt-2" />
                        </div>

                        <div class="row">
                            <div class="col-md-6">

                                <select class="form-select form-select-sm" id="exampleFormControlSelect3"
                                    name="user_id">
                                    <option value="">Select Site Enginner</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ ucfirst($user->name) }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
                            </div>

                            <div class="col-md-6">

                                <select class="form-select form-select-sm" id="exampleFormControlSelect3"
                                    name="client_id">
                                    <option value="">Select Client</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}">{{ ucfirst($client->name) }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
                            </div>

                        </div>

                        <button class=" btn btn-info mt-3"><span>Save</span></button>

                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Users') }}
        </h2>
    </x-slot> --}}


</x-app-layout>
