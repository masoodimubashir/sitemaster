<x-app-layout>
   

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="POST" action="{{ route('construction-material-billings.store') }}"
                        enctype="multipart/form-data">

                        @csrf

                        @if (session('message'))
                            {{ session('message') }}
                        @endif


                        <!-- Item Name -->
                        <div class="mt-4">
                            <x-input-label for="item_name" :value="__('Item Name')" />
                            <x-text-input id="item_name" class="block mt-1 w-full" type="text" name="item_name"
                                :value="old('item_name')" autocomplete="item_name" />
                            @error('item_name')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>


                        <!-- Amount -->
                        <div class="mt-4">
                            <x-input-label for="amount" :value="__('Amount')" />
                            <x-text-input id="amount" class="block mt-1 w-full" type="number" name="amount"
                                :value="old('amount')" autocomplete="amount" />
                            @error('amount')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>


                        <!-- Select Site -->
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


                        <!-- Supplier -->
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

                        {{-- <!-- USers -->
                        <div class="mt-4">
                            <select id="user_id" class="block mt-1 w-full rounded-sm" name="user_id">
                                <option value="">Select User</option>

                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div> --}}

                        <!-- Phases -->
                        <div class="mt-4">
                            <select id="phase_id" class="block mt-1 w-full rounded-sm" name="phase_id">
                                <option value="">Select Phase</option>

                                @foreach ($phases as $phase)
                                    <option value="{{ $phase->id }}">{{ $phase->phase_name }}</option>
                                @endforeach
                            </select>
                            @error('phase_id')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <!-- Item Bill Photo -->
                        <div class="mt-4">
                            <x-input-label for="image" :value="__('Item Bill')" />
                            <x-text-input id="image" class="block mt-1 w-full" type="file" name="image"
                                :value="old('image')" autocomplete="image" />
                            @error('image')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>



                        {{-- <!-- Verified By Admin -->
                        <div class="mt-4">
                            <x-input-label for="verified_by_admin" :value="__('Verified By Admin')" />
                            <x-text-input id="verified_by_admin" class="block mt-1" type="checkbox"
                                name="verified_by_admin" :value="old('verified_by_admin')" autocomplete="verified_by_admin" />
                            @error('verified_by_admin')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div> --}}







                        <div class="flex items-center justify-end mt-4">

                            {{-- <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                href="{{ route('login') }}">
                                {{ __('Already registered?') }}
                            </a> --}}

                            <x-primary-button class="ms-4">
                                {{ __('Create Billing') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

</x-app-layout>
