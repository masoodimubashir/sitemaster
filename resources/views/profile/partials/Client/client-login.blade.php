<x-guest-layout>




    <form class="pt-3" method="POST" action="{{ route('client.login') }}">

        @csrf

        <div class="form-group">
            <input type="number" class="form-control form-control-lg" id="number" placeholder="Username" name="number"
                min="0" value="{{ old('number') }}">

        </div>
            <x-input-error :messages="$errors->get('number')" class="mt-2" />



            <div class="form-group position-relative">
                <input type="password" class="form-control form-control-lg" id="password" placeholder="Password"
                    name="password" value="{{ old('password') }}" aria-describedby="toggle-password">
                <span class="input-icon position-absolute top-50 end-0 translate-middle text-info fw-bold"
                    id="toggle-password" onclick="togglePasswordVisibility()">
                    <i class="fa fa-eye-slash" id="password-icon"></i>
                </span>
            </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />



        <div class="mt-3 d-grid gap-2">

            <button type="submit" class="btn  btn-info  fw-medium">
                Sign In
            </button>

            <a class="text-secondary fw-bold fs-4 nav-link" href="{{ url('/') }}">

                Back
            </a>


        </div>


    </form>

</x-guest-layout>
