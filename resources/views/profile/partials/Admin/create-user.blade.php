<x-app-layout>


    <x-breadcrumb :names="['Site Engineer', 'Create Engineer']" :urls="['admin/users', 'admin/users/create']" />


    <div class="row">


        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">

                <div class="card-body">
                    <form method="POST" action="{{ route('admin.register-user') }}" class="forms-sample material-form">

                        @csrf

                        <div class="form-group">
                            <input type="text" name="name" value="{{ old('name') }}"/>
                            <label for="input" class="control-label">name</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="form-group">
                            <input type="text" name="username" value="{{ old('username') }}"/>
                            <label for="input" class="control-label">Username</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('username')" class="mt-2" />
                        </div>

                        <div class="form-group">
                            <input type="text" name="password" value="{{ old('password') }}"/>
                            <label for="input" class="control-label">Password</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="form-group">
                            <input type="text" name="password_confirmation" />
                            <label for="input" class="control-label">Confirm Password</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        <div class="">
                            <button class=" btn btn-info"><span>Save</span></button>

                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
