<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Wager') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="POST" action="{{ route('dailywager.store') }}">

                        @csrf

                        @if (session('message'))
                            {{ session('message') }}
                        @endif

                        @if (session('error'))
                            {{ session('error') }}
                        @endif

                         <!-- Price Per day -->
                        <div class="mt-4">
                            <x-input-label for="wager_name" :value="__('Workforce Name')" />
                            <x-text-input id="wager_name" class="block mt-1 w-full" type="text"
                                name="wager_name" :value="old('wager_name')" autocomplete="wager_name" />
                            @error('wager_name')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <!-- Price Per day -->
                        <div class="mt-4">
                            <x-input-label for="price_per_day" :value="__('Price Per Day')" />
                            <x-text-input id="price_per_day" class="block mt-1 w-full" type="number"
                                name="price_per_day" :value="old('price_per_day')" autocomplete="price_per_day" />
                            @error('price_per_day')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>




                        <!-- Select Workforce -->
                        <div class="mt-4">
                            <select id="site_id" class="block mt-1 w-full rounded-sm" name="site_id">
                                <option value="">Select Site</option>
                                @foreach ($sites as $site)
                                    <option value="{{ $site->id }}">{{ $site->site_name }}</option>
                                @endforeach
                            </select>
                            @error('site_id')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <div class="flex items-center justify-end mt-4">

                            {{-- <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                href="{{ route('login') }}">
                                {{ __('Already registered?') }}
                            </a> --}}

                            <x-primary-button class="ms-4">
                                {{ __('Create Wager') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

</x-app-layout>
