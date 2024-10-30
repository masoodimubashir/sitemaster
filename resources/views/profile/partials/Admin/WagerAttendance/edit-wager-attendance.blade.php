<x-app-layout>


    <div class="row">

          @if (session('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert" id="alert-box">
                {{ session('message') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">

                    <h4 class="text-info">Edit Wager Attendance</h4>



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

                            @error('daily_wager_id')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>
                        @error('daily_wager_id')
                            <x-input-error :messages="$message" class="mt-2" />
                        @enderror


                        <div class="mt-3">
                            <input id="phase_id" type="hidden" name="phase_id" placeholder="Phase"
                                value="{{ $daily_wager_attendance->phase_id }}" />
                            @error('phase_id')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>


                        <div class="row">
                            <div class="col-md-6">

                                <input type="date" name="date" class="form-control">
                                @error('date')
                                    <x-input-error :messages="$message" class="mt-2" />
                                @enderror
                            </div>

                            <!-- Is Present -->
                            <div class="form-check col-md-6">


                                <div class="custom-control custom-checkbox mr-sm-2">
                                    <input type="checkbox" class="custom-control-input"
                                        {{ $daily_wager_attendance->is_present ? 'checked' : '' }}
                                        name="is_present">
                                    <label class="custom-control-label">Mark Present</label>
                                </div>

                                @error('is_present')
                                    <x-input-error :messages="$message" class="mt-2" />
                                @enderror
                            </div>


                        </div>




                        <div class="flex items-center justify-end mt-4">

                            <div class="button-container">

                                <a class=" btn btn-info"
                                    href="{{ route('sites.show', [base64_encode($daily_wager_attendance->phase->site->id)]) }}"><span>Back</span></a>

                                <button class="btn btn-info"><span>Make Attendance</span></button>

                            </div>
                        </div>


                    </form>

                </div>
            </div>
        </div>
    </div>

</x-app-layout>
