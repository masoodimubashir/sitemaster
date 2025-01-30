<x-app-layout>

    <x-breadcrumb :names="['Phases']" :urls="['admin/phase']" />

    @if (session('status') === 'create')
        <x-success-message message="Phase Created..." />
    @endif

    @if (session('status') === 'update')
        <x-success-message message="Phase Verification Updated...." />
    @endif

    {{-- @if (session('status') === 'not_found')
        <x-error-message message="Phase Not Found....." />
    @endif --}}

    @if (session('status') === 'delete')
        <x-error-message message="Phase deleted......" />
    @endif

    @if (session('status') === 'data')
        <x-error-message message="Phase Cannot Be deleted......" />
    @endif


    <div class="row">

        <div class="col-lg-12">

            <div class="card-body">

                <div class="table-responsive mt-4">

                    @if (count($phases))

                        <table class="table table-bordered">

                            <thead>

                                <tr>

                                    <th class="bg-info text-white fw-bold">Date</th>
                                    <th class="bg-info text-white fw-bold">Phase Name</th>
                                    <th class="bg-info text-white fw-bold">Site Name</th>
                                    <th class="bg-info text-white fw-bold">Actions</th>

                                </tr>

                            </thead>

                            <tbody>
                                @foreach ($phases as $phase)
                                    <tr>


                                        <td>
                                            {{ $phase->created_at->format('D-m-Y') }}
                                        </td>

                                        <td>
                                            {{ $phase->site->site_name }}
                                        </td>

                                        <td>
                                            {{ $phase->phase_name }}
                                        </td>

                                        <td>

                                            <a href="{{ url('admin/phase/' . base64_encode($phase->id) . '/edit') }}">
                                                <i
                                                    class="fa-regular fa-pen-to-square text-xl bg-white rounded-full"></i>
                                            </a>

                                            <form id="delete-form-{{ $phase->id }}"
                                                action="{{ url('admin/phase/' . base64_encode($phase->id)) }}"
                                                method="POST" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>

                                            <a href="#"
                                                onclick="event.preventDefault(); if (confirm('Are you sure you want to delete this phase?')) document.getElementById('delete-form-{{ $phase->id }}').submit();">
                                                <i
                                                    class="fa-solid fa-trash-o text-xl text-red-600 bg-white rounded-full px-2 py-1"></i>
                                            </a>
                                        </td>




                                    </tr>
                                @endforeach

                            </tbody>


                        </table>
                    @else
                        <table class="table table-bordered">
                            <thead></thead>
                            <tbody>
                                <tr>
                                    <td class="text-danger fw-bold text-center">No Phases Found...</td>
                                </tr>
                            </tbody>
                        </table>


                    @endif
                </div>


                <div class="mt-4">

                    {{ $phases->links() }}

                </div>

            </div>

        </div>
    </div>
</x-app-layout>
