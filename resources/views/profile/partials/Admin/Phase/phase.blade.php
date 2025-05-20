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

    {{-- Phase Form --}}

    <div id="phase" class="modal fade" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <form class="forms-sample material-form" id="phaseForm">
                        @csrf

                        {{-- Phase Name --}}
                        <div class="form-group">
                            <input type="text" name="phase_name" id="phase_name" />
                            <label for="phase_name" class="control-label">Phase Name</label>
                            <i class="bar"></i>
                            <x-input-error :messages="$errors->get('phase_name')" class="mt-2" />
                        </div>

                        {{-- Site --}}
                        <div class="form-group">

                            <select name="site_id" id="site_id">
                                <option value="">Select Site</option>
                                @foreach ($sites as $site)
                                    <option value="{{ $site->id }}">{{ ucfirst($site->site_name) }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('site_id')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                Create Phase
                            </x-primary-button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="row">

        <div class="col-lg-12">

            <div class="d-flex justify-content-end align-items-center">

                <a class="dropdown-item" data-bs-toggle="modal" role="button" href="#phase">
                    <i class="fas fa-layer-group me-2"></i> Add Phase
                </a>
            
            </div>

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

    @push('scripts')
        <script>
            $(document).ready(function() {


                // Model Ajax Functions
                $('form[id="phaseForm"]').on('submit', function(e) {
                    e.preventDefault();

                    const form = $(this);
                    const formData = new FormData(form[0]);
                    const messageContainer = $('#messageContainer');
                    messageContainer.empty();


                    $('.text-danger').remove();

                    $.ajax({
                        url: '{{ url('admin/phase') }}',
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(response) {
                            form[0].reset();
                            messageContainer.append(`
                             <div  class="alert align-items-center text-white bg-success border-0" role="alert" >
                                 <div class="d-flex">
                                    <div class="toast-body">
                                        <strong><i class="fas fa-check-circle me-2"></i></strong>${response.message}
                                    </div>
                                </div>
                            </div> `);
                            // Auto-hide success message after 3 seconds
                            setTimeout(function() {
                                messageContainer.find('.alert').alert('close');
                                location.reload();
                            }, 2000);
                        },
                        error: function(response) {

                            if (response.status === 422) { // Validation errors
                                messageContainer.append(`
                    <div class="alert alert-danger mt-3 alert-dismissible fade show  " role="alert">
                    ${response.responseJSON.errors}

                    </div>`)

                            } else {
                                messageContainer.append(`
                    <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                        An unexpected error occurred. Please try again later.

                    </div>
                `);
                            }
                            // Auto-hide error message after 5 seconds
                            setTimeout(function() {
                                messageContainer.find('.alert').alert('close');

                            }, 2000);
                        }
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
