<x-app-layout>


    <div class="row">


        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">

                    <h4 class="display-4 text-info">Create Client</h4>


                    @if (session('message'))
                        <p class="card-description">
                            {{ session('message') }}

                        </p>
                    @endif



                    <form method="POST" action="{{ route('clients.store') }}" class="forms-sample material-form">

                        @csrf

                        <div class="form-group">
                            <input type="text" name="name" value="{{ old('name') }}" />
                            <label for="input" class="control-label">Name (Site Owner Name)</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="form-group">
                            <input type="text" name="number" min="0" value="{{ old('number') }}" />
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



                        <div>

                            <a class=" btn btn-info" href="{{ route('clients.index') }}">Back</a>

                            <button class=" btn btn-primary"><span>Submit</span></button>

                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>



</x-app-layout>
