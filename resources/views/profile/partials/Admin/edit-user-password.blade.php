<x-app-layout>

    <x-breadcrumb :names="['Site Engineers', 'Update Password']" :urls="['admin/users', 'admin/edit-user/' . $user->id]" />

    @if (session('status') === 'password')
        <x-success-message message="Password Updated Successfully" />
    @endif

    <div class="row">

        <div class="col-md-12 grid-margin stretch-card">




            <div class="card">
                <div class="card-body">
                    <form method="post" action="{{ route('user.update-name', [$user->id]) }}"
                        class="forms-sample material-form">

                        @csrf
                        @method('put')

                        <div class="form-group">
                            <input type="text" name="name" value="{{ $user->name }}" />
                            <label for="input" class="control-label">Name</label><i class="bar"></i>
                            <x-input-error :messages="$errors->updateName->get('name')" class="mt-2" />

                        </div>
                        <div class="form-group">
                            <input type="text" name="username" value="{{ $user->username }}" />
                            <label for="input" class="control-label">Username </label><i class="bar"></i>
                            <x-input-error :messages="$errors->updateName->get('username')" class="mt-2" />
                        </div>

                        <button class=" btn btn-info">{{ __('Save') }}</button>

                    </form>
                </div>
            </div>

        </div>

        <div class="col-md-12 grid-margin stretch-card">

            <div class="card">

                <div class="card-body">

                    <form method="post" action="{{ route('admin.update-user-password', [$user->id]) }}"
                        class="forms-sample material-form">

                        @csrf
                        @method('put')

                        <div class="form-group">
                            <input type="text" name="password" />
                            <label for="input" class="control-label">New Password</label><i class="bar"></i>
                            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />


                        </div>
                        <div class="form-group">
                            <input type="text" name="password_confirmation" />
                            <label for="input" class="control-label">Confirm Password</label><i class="bar"></i>
                            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                        </div>

                        <button class=" btn btn-info">{{ __('Save') }}</button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>
