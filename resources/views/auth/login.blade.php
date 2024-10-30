<x-guest-layout>



        <form class="pt-3" method="POST" action="{{ route('login') }}">

            @csrf

            <div class="form-group">
                <input type="text" class="form-control form-control-lg" id="username" placeholder="Username"
                    name="username" value="{{ old('name') }}">
                <x-input-error :messages="$errors->get('username')" class="mt-2" />

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

</x-guest-layout>
