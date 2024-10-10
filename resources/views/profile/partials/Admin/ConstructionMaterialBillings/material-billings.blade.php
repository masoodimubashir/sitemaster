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

                    @can('admin')
                        <a class="bg-black  p-2 rounded text-white "
                            href="{{ route('construction-material-billings.create') }}">
                            Create Billings
                        </a>
                    @endcannot

                    <br>
                    <br>
                    <br>

                    @if (count($construction_material_billings))
                        @foreach ($construction_material_billings as $construction_material_billing)
                            <div class="border my-4 p-2">

                                {{ $construction_material_billing->item_name }}
                                <br>
                                {{ $construction_material_billing->amount }}
                                <br>
                                {{ $construction_material_billing->verified_by_admin ? 'verified' : 'not verified' }}

                                <img src="{{ asset($construction_material_billing->item_image_path) }}" alt="">

                                <br>

                                {{ $construction_material_billing->id }}
                                <a
                                    href="{{ route('construction-material-billings.edit', base64_encode($construction_material_billing->id)) }}">Edit
                                    Site</a>
                                <br>


                                <form id="delete-form-{{ $construction_material_billing->id }}"
                                    action="{{ route('construction-material-billings.destroy', [base64_encode($construction_material_billing->id)]) }}"
                                    method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>

                                <a href="#"
                                    onclick="event.preventDefault(); if (confirm('Are you sure you want to delete this supplier?')) document.getElementById('delete-form-{{ $construction_material_billing->id }}').submit();">
                                    Delete Supplier
                                </a>
                            </div>
                        @endforeach
                    @else
                        <h1>No records found...</h1>
                    @endif


                </div>
            </div>
        </div>
    </div>
</x-app-layout>
