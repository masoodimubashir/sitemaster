<x-guest-layout>

    <form class="pt-3" method="POST" action="{{ route('login') }}">

        @csrf

        <div class="form-group">
            <input type="text" class="form-control form-control-lg" id="username" placeholder="Username" name="username"
                value="{{ old('username') }}">
            <x-input-error :messages="$errors->get('username')" class="mt-2" />

        </div>

        <div class="form-group position-relative">
            <input type="password" class="form-control form-control-lg" id="password" placeholder="Password"
                name="password" value="{{ old('password') }}" aria-describedby="toggle-password">
            <span style="cursor: pointer" class="input-icon position-absolute top-50 end-0 translate-middle text-info fw-bold"
                id="toggle-password" onclick="togglePasswordVisibility()">
                <i class="fa fa-eye-slash"  id="password-icon"></i>
            </span>
        </div>
        <x-input-error :messages="$errors->get('password')" class="mt-2" />


        <div class="mt-3 d-grid gap-2">
            <button type="submit" class="btn  btn-info ">
                Sign In
            </button>

            <a style="color: #51B1E1"  class="fw-bold fs-5 nav-link" href="{{ url('/') }}">
                Back
            </a>
        </div>

    </form>




</x-guest-layout>
