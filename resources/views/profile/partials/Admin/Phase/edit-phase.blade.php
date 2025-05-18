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

    {{-- <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Users') }}
        </h2>
    </x-slot> --}}


</x-app-layout>
