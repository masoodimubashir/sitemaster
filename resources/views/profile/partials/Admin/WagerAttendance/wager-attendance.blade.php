<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Attendance') }}
        </h2>

    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                <div class="p-6 text-gray-900">

                    @if (session('message'))
                        {{ session('message') }}
                    @endif


                    <div>
                        <form action="{{ route('daily-wager-attendance.store') }}" method="POST">

                            @csrf

                            <!-- Amount -->
                            <div class="mt-4">
                                <x-input-label for="no_of_persons" :value="__('Number Of Persons')" />
                                <x-text-input id="no_of_persons" class="block mt-1 w-full" type="number"
                                    name="no_of_persons" :value="old('no_of_persons')" autocomplete="no_of_persons" />
                                @error('no_of_persons')
                                    <x-input-error :messages="$message" class="mt-2" />
                                @enderror
                            </div>

                            <!-- Wager -->
                            <div class="mt-4">
                                <select id="daily_wager_id" class="block mt-1 w-full" name="daily_wager_id">
                                    <option value="">Select Wager</option>

                                    @foreach ($wagers as $wager)
                                        <option value="{{ $wager->id }}">{{ $wager->wager_name }}</option>
                                    @endforeach
                                </select>
                                @error('daily_wager_id')
                                    <x-input-error :messages="$message" class="mt-2" />
                                @enderror
                            </div>

                            <!-- Is Present -->
                            <div class="mt-4">
                                <x-input-label for="is_present" :value="__('Present Absent')" />
                                <x-text-input id="is_present" class="block mt-1" type="checkbox" name="is_present"
                                    :value="old('is_present')" autocomplete="is_present" />
                                @error('is_present')
                                    <x-input-error :messages="$message" class="mt-2" />
                                @enderror
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="bg-black text-white px-2 py-3 rounded-md mt-1">Make
                                    Attendance</button>
                            </div>


                        </form>
                    </div>

                    <br>
                    <br>
                    <br>



                    @if ($user)
                        <div class="border my-4 p-2">


                            <table class="w-full">
                                <thead>
                                    <tr class="text-left">
                                        <th>Id</th>
                                        <th>No Of Persons</th>
                                        <th>Present</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>


                                    @foreach ($user->wagerAttendances as $wager_attendance)
                                        <tr>
                                            <td>{{ $wager_attendance->id }}</td>

                                            <td>{{ $wager_attendance->no_of_persons }}</td>
                                            <td>{{ $wager_attendance->is_present ? 'Present' : 'Absent' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($wager_attendance->created_at)->format('d-M-y') }}
                                            </td>

                                            <td>
                                                <a
                                                    href="{{ route('daily-wager-attendance.edit', base64_encode($wager_attendance->id)) }}">Edit
                                                    Wager</a>
                                                <br>


                                                <form id="delete-form-{{ $wager_attendance->id }}"
                                                    action="{{ route('daily-wager-attendance.destroy', [base64_encode($wager_attendance->id)]) }}"
                                                    method="POST" style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>

                                                <a href="{{ base64_encode($wager_attendance->id) }}"
                                                    onclick="event.preventDefault(); if (confirm('Are you sure you want to delete this supplier?')) document.getElementById('delete-form-{{ $wager_attendance->id }}').submit();">
                                                    Delete Supplier
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        @else
                            <h1>No records found...</h1>
                    @endif


                </div>
            </div>
        </div>
    </div>
</x-app-layout>
