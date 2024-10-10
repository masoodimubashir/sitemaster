<x-guest-layout>


    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />



    <form class="mb-0 mt-6 space-y-4 rounded-lg p-4 shadow-lg sm:p-6 lg:p-8" method="POST"
        action="{{ route('password.email') }}">

        <div class="mb-4 text-sm text-gray-600">
            {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
        </div>

        @csrf


        <div>
            <label for="email" class="sr-only">Email</label>

            <div class="relative">
                <input type="email" name="email" class="w-full rounded-lg border-gray-200 p-4 pe-12 text-sm shadow-sm"
                    placeholder="Enter email" />
                </span>
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />


        </div>

        <button type="submit" class="block w-full rounded-lg bg-indigo-600 px-5 py-3 text-sm font-medium text-white">
            {{ __('Email Password Reset Link') }}
        </button>

        @if (Route::has('password.request'))
            <a class="underline text-sm text-gray-900 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mt-6"
                href="/">
                {{ __('Login') }}
            </a>
        @endif

    </form>
</x-guest-layout>
