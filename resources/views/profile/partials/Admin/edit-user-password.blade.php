

<x-app-layout>


    <div class="row">


        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">

                    <h4 class="display-4 text-info ">
                        {{ __('Update Password') }}
                    </h4>
                    <p class="mt-2 text-sm text-gray-600 mb-2">
                        {{ __('Ensure your account is using a long, random password to stay secure.') }}
                    </p>

                    @if (session('message'))
                        <p class="card-description">
                            {{ session('message') }}

                        </p>
                    @endif


                    <form method="post" action="{{ route('admin.update-user-password', ['id' => $user->id]) }}"
                        class="forms-sample material-form">
                        @csrf
                        @method('put')




                        <div class="form-group">
                            <input type="password" name="password" />
                            <label for="input" class="control-label">New Password</label><i class="bar"></i>
                            @error('password')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>
                        <div class="form-group">
                            <input type="password" name="password_confirmation" />
                            <label for="input" class="control-label">Confirm Password</label><i class="bar"></i>
                            @error('password_confirmation')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>



                        <div class="button-container">
                            <a class=" btn btn-info" href="{{ route('users.index') }}"><span>Back</span></a>

                            <button class=" btn btn-primary">{{ __('Save') }}</button>
                            @if (session('status') === 'password-updated')
                                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                    class="text-sm text-gray-600">{{ __('Saved.') }}</p>
                            @endif
                        </div>

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
