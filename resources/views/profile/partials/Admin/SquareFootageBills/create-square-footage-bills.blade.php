<x-app-layout>


    

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Square Footage Bill') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="POST" action="{{ route('square-footage-bills.store') }}" enctype="multipart/form-data">

                        @csrf

                        @if (session('message'))
                            {{ session('message') }}
                        @endif

                        @if (session('error'))
                            {{ session('error') }}
                        @endif

                        <!-- Wager Name -->
                        <div class="mt-4">
                            <x-input-label for="wager_name" :value="__('Item Name')" />
                            <x-text-input id="wager_name" class="block mt-1 w-full" type="text" name="wager_name"
                                :value="old('wager_name')" autocomplete="wager_name" />
                            @error('wager_name')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <!-- Price -->
                        <div class="mt-4">
                            <x-input-label for="price" :value="__('Price')" />
                            <x-text-input id="price" class="block mt-1 w-full" type="number" name="price"
                                :value="old('price')" autocomplete="price" />
                            @error('price')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <!-- Number Of Days -->
                        <div class="mt-4">
                            <x-input-label for="multiplier" :value="__('Multiplier')" />
                            <x-text-input id="multiplier" class="block mt-1 w-full" type="number"
                                name="multiplier" :value="old('multiplier')" autocomplete="multiplier" />
                            @error('multiplier')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                         <!-- Type -->
                        <div class="mt-4">
                            <x-input-label for="type" :value="__('Select Type')" />
                            <select name="type" id="type"  class="block mt-1 w-full rounded-sm">
                                <option value="per_sqr_ft">Per Square Feet</option>
                                <option value="per_unit">Per Unit</option>
                                <option value="full_contract">Full Contract</option>
                            </select>
                            @error('type')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <!-- Select Supplier -->
                        <div class="mt-4">
                            <select id="supplier_id" class="block mt-1 w-full rounded-sm" name="supplier_id">
                                <option value="">Select Supplier</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <!-- Select Phase -->
                        <div class="mt-4">
                            <select id="supplier_id" class="block mt-1 w-full rounded-sm" name="supplier_id">
                                <option value="">Select Phase</option>
                                @foreach ($phases as $phase)
                                    <option value="{{ $phase->id }}">{{ $phase->phase_name }}</option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                         <!-- Number Of Days -->
                        <div class="mt-4">
                            <x-input-label for="image_path" :value="__('Select Image')" />
                            <x-text-input id="image_path" class="block mt-1 w-full" type="file" />
                            @error('image_path')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <div class="flex items-center justify-end mt-4">

                            {{-- <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                href="{{ route('login') }}">
                                {{ __('Already registered?') }}
                            </a> --}}

                            <x-primary-button class="ms-4">
                                {{ __('Create Bill') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

</x-app-layout>
