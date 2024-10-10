<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Workforce') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="POST" action="{{ route('workforce.update', [base64_encode($workforce->id)]) }}">
                        @csrf
                        @method('PUT')

                        @if (session('message'))
                            {{ session('message') }}
                        @endif

                        <!-- Item Name -->
                        <div class="mt-4">
                            <x-input-label for="workforce_name" :value="__('Workforce Name')" />
                            <x-text-input id="workforce_name" class="block mt-1 w-full" type="text"
                                name="workforce_name" :value="$workforce->workforce_name" autocomplete="workforce_name" />
                            @error('workforce_name')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>




                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ms-4">
                                {{ __('Create Workforce') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

</x-app-layout>
