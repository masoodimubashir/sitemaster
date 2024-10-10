<x-app-layout>


    <div class="row">


        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">

                    <h4 class="text-3xl text-primary">Edit Client</h4>

                    @if (session('message'))
                        <p class="card-description">
                            {{ session('message') }}

                        </p>
                    @endif



                    <form method="POST" action="{{ route('clients.update', [$client->id]) }}" class="forms-sample material-form">

                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <input type="text" name="name" value="{{ $client->name }}" />
                            <label for="input" class="control-label">Name (Site Owner Name)</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
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
