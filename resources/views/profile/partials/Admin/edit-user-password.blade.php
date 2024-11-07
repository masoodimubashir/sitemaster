<x-app-layout>

    <x-breadcrumb :names="['Site Engineers', 'Update ' . $user->name . ' Paswword']" :urls="['admin/users', 'admin/edit-user/' . $user->id]" />

    @if (session('status') === 'password')
        <x-success-message message="Password Updated Successfully" />
    @endif

    <div class="row">


        <div class="col-md-12 grid-margin stretch-card">

            <div class="card">

                <div class="card-body">

                    <form method="post" action="{{ route('password.update') }}" class="forms-sample material-form">
                        @csrf
                        @method('put')

                        <div class="form-group">
                            <input type="password" name="password" />
                            <label for="input" class="control-label">New Password</label><i class="bar"></i>
                            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />


                        </div>
                        <div class="form-group">
                            <input type="password" name="password_confirmation" />
                            <label for="input" class="control-label">Confirm Password</label><i class="bar"></i>
                            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                        </div>

                        <button class=" btn btn-info">{{ __('Save') }}</button>

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
