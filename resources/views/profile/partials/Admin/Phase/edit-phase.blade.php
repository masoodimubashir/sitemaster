<x-app-layout>

    @php
        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp

    <x-breadcrumb :names="['Phase', 'Edit ' . $phase->phase_name]" :urls="['admin/phase', 'admin/phase/' . base64_encode($phase->id) . '/edit']" />

    <div class="row">

        <div class="col-md-12 grid-margin stretch-card">

            <div class="card">

                <div class="card-body">


                    <form method="POST" action="{{ url($user . '/phase/' . base64_encode($phase->id)) }}"
                        class="forms-sample material-form">

                        @method('PUT')

                        @csrf

                        {{-- Date --}}
                        <div class="form-group">
                            <input type="date" name="created_at" id="created_at"
                                value="{{ $phase->created_at ? $phase->created_at->format('Y-m-d') : '' }}" />
                            <label for="created_at" class="control-label">Date</label>
                            <i class="bar"></i>
                            <p class="mt-1 text-danger" id="created_at-error"></p>
                        </div>

                        <!-- Phase Name -->
                        <div class="form-group">
                            <input type="text" name="phase_name" id="phase_name" value="{{ $phase->phase_name }}" />
                            <label for="phase_name" class="control-label">Phase Name</label><i class="bar"></i>
                            @error('phase_name')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>


                        <button class="btn btn-info">
                            Save
                        </button>
                    </form>


                </div>
            </div>
        </div>
    </div>



</x-app-layout>
