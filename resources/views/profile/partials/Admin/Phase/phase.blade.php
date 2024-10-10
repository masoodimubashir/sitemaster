<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Phases') }}
        </h2>

    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                <div class="p-6 text-gray-900">

                    @if (session('message'))
                        {{ session('message') }}
                    @endif

                    @if (session('error'))
                        {{ session('message') }}
                    @endif

                    @can('admin')
                        <a class="bg-black  p-2 rounded text-white " href="{{ route('phase.create') }}">Create Phase</a>
                    @endcannot

                    <br>
                    <br>
                    <br>

                    @if (count($phases))
                        @foreach ($phases as $phase)
                            <div class="border my-4 p-2">
                                {{ $phase->id }}

                                <br>

                                Phase Name: {{ $phase->phase_name }}
                                <br>
                                Site Name: {{ $phase->site->site_name }}
                                <br>

                                Site Owner Name: {{ $phase->site->site_owner_name }}
                                <br>

                                Site Service Charge: {{ Number::currency($phase->site->service_charge, 'INR') }}

                                <a href="{{ route('phase.edit', base64_encode($phase->id)) }}">Edit Phase</a>
                                <br>


                                <form id="delete-form-{{ $phase->id }}"
                                    action="{{ route('phase.destroy', [base64_encode($phase->id)]) }}" method="POST"
                                    style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>

                                <a href="{{ base64_encode($phase->id) }}"
                                    onclick="event.preventDefault(); if (confirm('Are you sure you want to delete this supplier?')) document.getElementById('delete-form-{{ $phase->id }}').submit();">
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
