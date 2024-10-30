<x-app-layout>



    <div class="row">

        <div class="col-lg-12">

            <div class="card-body">

                <div class="flex items-center justify-between">

                    <h4 class="text-xl text-info fw-bold">Sites</h4>

                    <a class="btn btn-info" href="{{ route('sites.create') }}">
                        <i class="fa-solid fa-building me-2"></i>
                        Create New Site
                    </a>

                </div>

                 @if (session('message'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert" id="alert-box">
                            {{ session('message') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                <div class="table-responsive mt-4">

                    @if (count($sites))


                        <table class="table table-bordered">

                            <thead>

                                <tr>
                                    <th class="bg-info text-white fw-bold"> Site Status </th>
                                    <th class="bg-info text-white fw-bold">Date</th>
                                    <th class="bg-info text-white fw-bold">Site Name</th>
                                    <th class="bg-info text-white fw-bold"> Location </th>
                                    <th class="bg-info text-white fw-bold">Contact No</th>
                                    <th class="bg-info text-white fw-bold"> Site Owner Name </th>
                                    <th class="bg-info text-white fw-bold"> Service Charge(%) </th>
                                    <th class="bg-info text-white fw-bold">Actions</th>
                                </tr>

                            </thead>

                            <tbody>
                                @foreach ($sites as $site)
                                    <tr>

                                        <td class="fw-bold {{ $site->is_on_going ? 'text-success' : 'text-danger' }}">

                                            {{ $site->is_on_going ? 'Open' : 'Closed' }}

                                        </td>



                                        <td> {{ $site->created_at->format('d-M-Y') }} </td>

                                        <td title=" View {{ $site->site_name }} details...">

                                            <a href="{{ route('sites.show', [base64_encode($site->id)]) }}"
                                                class="fw-bold link-offset-2 link-underline link-underline-opacity-0">
                                                <mark>{{ ucfirst($site->site_name) }}</mark>
                                            </a>

                                        </td>

                                        <td>

                                            {{ ucfirst($site->location) }} </td>

                                        <td>

                                            <a href="tel:{{ $site->contact_no }}">
                                                +91-{{ $site->contact_no }}
                                            </a>

                                        </td>

                                        <td>
                                            {{ ucfirst($site->site_owner_name) }}
                                        </td>

                                        <td>
                                            {{ $site->service_charge }}
                                        </td>

                                        <td class="space-x-4">

                                            <a href="{{ route('sites.edit', base64_encode($site->id)) }}">
                                                <i
                                                    class="fa-regular fa-pen-to-square text-xl bg-white rounded-full"></i>
                                            </a>

                                            <form id="delete-form-{{ $site->id }}"
                                                action="{{ route('sites.destroy', [base64_encode($site->id)]) }}"
                                                method="POST" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>

                                            <a href="#"
                                                onclick="event.preventDefault(); if (confirm('Are you sure you want to delete this supplier?')) document.getElementById('delete-form-{{ $site->id }}').submit();">
                                                <i
                                                    class="fa-solid fa-trash-o text-xl text-red-600 bg-white rounded-full px-2 py-1"></i>
                                            </a>

                                            <form action="{{ route('sites.update-on-going', $site->id) }}"
                                                method="POST" class="d-inline">

                                                @csrf

                                                @method('POST')

                                                <button type="submit"
                                                    class="badge text-white {{ $site->is_on_going ? 'text-bg-danger' : 'text-bg-warning' }}">
                                                    {{ $site->is_on_going ? 'Close Site' : 'Open Site' }}
                                                </button>
                                            </form>

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
                                    <td class="text-danger fw-bold text-center">No Sites Found...</td>
                                </tr>
                            </tbody>
                        </table>


                    @endif
                </div>


                <div class="mt-4">

                    {{ $sites->links() }}

                </div>

            </div>

        </div>
    </div>
</x-app-layout>
