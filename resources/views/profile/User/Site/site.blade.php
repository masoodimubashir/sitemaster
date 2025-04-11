<x-app-layout>

    <x-breadcrumb :names="['Sites']" :urls="['user/dashboard']" />

    @if (session('status') === 'create')
        <x-success-message message="Site Created..." />
    @endif

    @if (session('status') === 'update')
        <x-success-message message="Site Verification Updated..." />
    @endif

    @if (session('status') === 'verify')
        <x-success-message message="Site Verification Updated..." />
    @endif

    @if (session('status') === 'delete')
        <x-error-message message="Site Deleted...." />
    @endif

    @if (session('status') === 'error')
        <x-error-message message="Site Cannot Be Deleted...." />
    @endif

    <div class="row">

        <div class="col-lg-12">

            <div class="card-body">

                <div class="d-flex justify-content-end">

                    <a class="btn btn-success btn-sm" href="{{ url('user/sites/create') }}">
                        <i class="fa-solid fa-building mr-2"></i>
                        Create Site
                    </a>

                </div>


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

                                            <a href="{{ url('user/sites/' . base64_encode($site->id)) }}"
                                                class="fw-bold link-offset-2 link-underline link-underline-opacity-0">
                                                {{ ucfirst($site->site_name) }}
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

                                          

                                          

                                          

                                            <form action="{{ route('sites.update-on-going', $site->id) }}"
                                                method="POST" class="d-inline">

                                                @csrf

                                                @method('POST')

                                                <button type="submit"
                                                    class="badge badge-pill btn text-white {{ $site->is_on_going ? 'text-bg-success' : 'text-bg-danger' }}">
                                                    {{ $site->is_on_going ? 'Verified' : 'Verify' }}
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
