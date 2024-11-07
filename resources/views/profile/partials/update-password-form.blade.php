<section>


    <header>
        <h2 class="fw-bold text-info">
            {{ __('Update Password') }}
        </h2>
    </header>




    <form method="POST" action="{{ route('password.update') }}" class="forms-sample material-form">

         @csrf
        @method('put')

        @if (session('status') === 'password')
           <x-success-message message="Password Updated Successfully..."/>
        @endif

        <div class="form-group">
            <input type="text" name="password" value="{{ old('password') }}" />
            <label for="input" class="control-label">New Password</label><i class="bar"></i>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />

        </div>

        <div class="form-group">
            <input type="text" name="password_confirmation" value="{{ old('password_confirmation') }}" />
            <label for="input" class="control-label">Confirm Password</label><i class="bar"></i>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />

        </div>


        <button class=" btn btn-info"><span>Save</span></button>



    </form>

</section>
