<x-app-layout>


    @php
        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp


    <x-breadcrumb :names="['Clients', 'Edit ' . $client->name]" :urls="[$user . '/clients', $user . '/clients/' . base64_encode($client->id) . '/edit']" />


    <div class="row">

        <div class="col-md-12 grid-margin stretch-card">

            <div class="card">

                <div class="card-body">

                    <form method="POST" action="{{ url($user . '/clients/' . $client->id) }}"
                        class="forms-sample material-form">

                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <input type="text" name="name" value="{{ $client->name }}" />
                            <label for="input" class="control-label">Name (Site Owner Name)</label><i
                                class="bar"></i>
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="form-group">
                            <input type="number" name="number" min="0" value="{{ $client->number }}" />
                            <label for="number" class="control-label">Phone Number (Username)</label><i
                                class="bar"></i>
                            <x-input-error :messages="$errors->get('number')" class="mt-2" />
                        </div>

                        <div class="form-group">
                            <input type="text" name="password" value="{{ old('password') }}" />
                            <label for="input" class="control-label">Password</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="form-group">
                            <input type="text" name="password_confirmation"
                                value="{{ old('password_confirmation') }}" />
                            <label for="input" class="control-label">Confirm Password</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        <button class=" btn btn-success"><span>Save</span></button>

                    </form>


                </div>
            </div>
        </div>
    </div>

</x-app-layout>
