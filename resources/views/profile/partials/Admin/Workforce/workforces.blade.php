<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Workforces') }}
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
                        {{ session('error') }}
                    @endif

                    @can('admin')
                        <a class="bg-black  p-2 rounded text-white " href="{{ route('workforce.create') }}">Create
                            Workforce</a>
                    @endcannot

                    <br>
                    <br>
                    <br>

                    @if (count($workforces))
                        @foreach ($workforces as $workforce)
                            <div class="border my-4 p-2">

                                {{ $workforce->workforce_name }}

                                <br>
                                <a href="{{ route('workforce.edit', base64_encode($workforce->id)) }}">Edit Workforce</a>
                                <br>


                                <form id="delete-form-{{ $workforce->id }}"
                                    action="{{ route('workforce.destroy', [base64_encode($workforce->id)]) }}" method="POST"
                                    style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>

                                <a href="{{ base64_encode($workforce->id) }}"
                                    onclick="event.preventDefault(); if (confirm('Are you sure you want to delete this supplier?')) document.getElementById('delete-form-{{ $workforce->id }}').submit();">
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
