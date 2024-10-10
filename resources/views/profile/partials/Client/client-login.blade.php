<x-guest-layout>


    <div class="auth-form-light text-left py-5 px-4 px-sm-5">
        
        <h4 class=" fw-bold text-info ">Sign in To Start Your Session.</h4>
        
        <form class="pt-3" method="POST" action="{{ route('client.login') }}">
            @csrf
            <div class="form-group">
                <input type="number" class="form-control form-control-lg" id="number" placeholder="Username"
                    name="number" min="0" value="{{ old('number') }}">
                <x-input-error :messages="$errors->get('number')" class="mt-2" />

            </div>
            <div class="form-group">
                <input type="password" class="form-control form-control-lg" id="password" placeholder="Password"
                    name="password" value="{{ old('password') }}">
                <x-input-error :messages="$errors->get('password')" class="mt-2" />

            </div>
            <div class="mt-3 d-grid gap-2">
                <button type="submit" class="btn btn-block btn-primary btn-lg fw-medium auth-form-btn">
                    Sign in

                </button>

            </div>
            <div class="my-2 d-flex justify-content-between align-items-center">


                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="auth-link text-black">

                        {{ __('Forgot your password?') }}
                    </a>

                @endif
                    <a href="{{ url('/') }}">Back</a>


            </div>


        </form>
    </div>

</x-guest-layout>
