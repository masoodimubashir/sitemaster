<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Construction Material Billings') }}
        </h2>

    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                <div class="p-6 text-gray-900">

                    @if (session('message'))
                        {{ session('message') }}
                    @endif

                    <a class="bg-black  p-2 rounded text-white " href="{{ route('construction.create') }}">
                        Create Billings
                    </a>

                    <br>
                    <br>
                    <br>

                    @if ($construction_material_billings)
                        <div class="space-y-5">
                            {{-- {{ $construction_material_billings }} --}}
                            @foreach ($construction_material_billings->construction_material_billings as $construction_material_billing)
                                <div class="border">
                                    {{ $construction_material_billing->item_name }}
                                </div>
                            @endforeach

                        </div>
                    @else
                        <h1>No records found...</h1>
                    @endif


                </div>
            </div>
        </div>
    </div>
</x-app-layout>
