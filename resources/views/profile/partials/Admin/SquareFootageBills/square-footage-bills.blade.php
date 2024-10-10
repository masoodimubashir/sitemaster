<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Square Footage Bills') }}
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
                        <a class="bg-black  p-2 rounded text-white " href="{{ route('square-footage-bills.create') }}">Create
                            Square Footage Bill</a>
                    @endcannot

                    <br>
                    <br>
                    <br>

                    @if (count($square_footage_bills))
                        @foreach ($square_footage_bills as $square_footage_bill)
                            <div class="border my-4 p-2">

                                {{ $square_footage_bill }}

                                <a
                                    href="{{ route('square-footage-bills.edit', base64_encode($square_footage_bill->id)) }}">Edit
                                    Square Footage Bill</a>
                                <br>


                                <form id="delete-form-{{ $square_footage_bill->id }}"
                                    action="{{ route('square-footage-bills.destroy', [base64_encode($square_footage_bill->id)]) }}"
                                    method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>

                                <a href="{{ base64_encode($square_footage_bill->id) }}"
                                    onclick="event.preventDefault(); if (confirm('Are you sure you want to delete this supplier?')) document.getElementById('delete-form-{{ $square_footage_bill->id }}').submit();">
                                    Delete Square Footage Bill
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
