<x-app-layout>

    <x-breadcrumb :names="['View ' . $daily_wager_attendance->phase->site->site_name, 'Edit']" :urls="[
        'admin/sites/' . base64_encode($daily_wager_attendance->phase->site->id),
        'admin/daily-wager-attendance/' . base64_encode($daily_wager_attendance->id) . '/edit',
    ]" />


    <div class="row">

        <div class="col-md-12 grid-margin stretch-card">

            <div class="card">

                <div class="card-body">

                    <form action="{{ route('daily-wager-attendance.update', [$daily_wager_attendance->id]) }}"
                        method="POST" class="forms-sample material-form">

                        @csrf
                        @method('PUT')

                        <!-- No Of Persons -->
                        <div class="form-group">
                            <input id="no_of_persons" type="number" name="no_of_persons"
                                value="{{ $daily_wager_attendance->no_of_persons }}" />
                            <label for="no_of_persons" class="control-label">No Of
                                Persons</label><i class="bar"></i>
                            @error('no_of_persons')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <!-- Wager -->
                        <div class="form-group">
                            <input id="daily_wager_id" type="hidden" name="daily_wager_id"
                                value="{{ $daily_wager_attendance->daily_wager_id }}" />

                        </div>

                        <div class="mt-4">
                            <input id="phase_id" type="hidden" name="phase_id" placeholder="Phase"
                                value="{{ $daily_wager_attendance->phase_id }}" />
                            @error('phase_id')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>


                        <div class="mt-4 ">
                            <label for="date">Select Date</label>
                            <input type="date" name="date" id="date" class="form-control" style="cursor: pointer">
                            @error('date')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        {{-- <!-- Is Present -->
                        <div class="form-check mt-4">

                            <div class="custom-control custom-checkbox ">
                                <input type="checkbox" class="custom-control-input"
                                    {{ $daily_wager_attendance->is_present ? 'checked' : '' }} name="is_present">
                                <label class="custom-control-label">Mark Present</label>
                            </div>

                            @error('is_present')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror

                        </div> --}}

                        <button class="btn btn-info mt-4"><span>Make Attendance</span></button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>
