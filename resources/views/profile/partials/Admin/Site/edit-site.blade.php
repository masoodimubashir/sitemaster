<x-app-layout>

    <x-breadcrumb :names="['Sites', 'Edit ' . $site->site_name]" :urls="['admin/sites', 'admin/sites/' . base64_encode($site->id) . '/edit']" />

    <div class="row">

        <div class="col-md-12 grid-margin stretch-card">

            <div class="card">

                <div class="card-body">

                    <form method="POST" action="{{ route('sites.update', [base64_encode($site->id)]) }}"
                        class="forms-sample material-form">

                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <input type="text" name="site_name" value="{{ $site->site_name }}" />
                            <label for="input" class="control-label">Site Name</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('site_name')" class="mt-2" />
                        </div>

                        <div class="form-group">
                            <input type="number" name="service_charge" value="{{ $site->service_charge }}"
                                step="0.01" min="0" />
                            <label for="input" class="control-label">Service Charge</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('service_charge')" class="mt-2" />
                        </div>

                        <div class="form-group">
                            <input type="text" name="location" value="{{ $site->location }}" />
                            <label for="input" class="control-label">Location</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('location')" class="mt-2" />
                        </div>

                        <div class="form-group">
                            <input type="text" name="site_owner_name" value="{{ $site->site_owner_name }}" />
                            <label for="input" class="control-label">Site Owner Name</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('site_owner_name')" class="mt-2" />
                        </div>

                        <div class="form-group">
                            <input type="text" name="contact_no" value="{{ $site->contact_no }}" />
                            <label for="input" class="control-label">Contact No</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('contact_no')" class="mt-2" />
                        </div>

                        <div>
                            <label for="user_id">Select Site Engineer</label>
                            <select class="form-select form-select-sm text-black" id="user_id" name="user_id" style="cursor: pointer">
                                @foreach ($users as $user)
                                    <option {{ $site->user_id === $user->id ? 'selected' : '' }}
                                        value="{{ $user->id }}">{{ ucfirst($user->name) }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
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
