<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daily Wagers') }}
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
                        <a class="bg-black  p-2 rounded text-white " href="{{ route('dailywager.create') }}">Create Wager</a>
                    @endcannot

                    <br>
                    <br>
                    <br>

                    @if (count($daily_wagers))
                        @foreach ($daily_wagers as $daily_wager)
                            <div class="border my-4 p-2">

                                {{ $daily_wager }}

                                <a href="{{ route('dailywager.edit', base64_encode($daily_wager->id)) }}">Edit Wager</a>
                                <br>


                                <form id="delete-form-{{ $daily_wager->id }}"
                                    action="{{ route('dailywager.destroy', [base64_encode($daily_wager->id)]) }}"
                                    method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>

                                <a href="{{ base64_encode($daily_wager->id) }}"
                                    onclick="event.preventDefault(); if (confirm('Are you sure you want to delete this supplier?')) document.getElementById('delete-form-{{ $daily_wager->id }}').submit();">
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
