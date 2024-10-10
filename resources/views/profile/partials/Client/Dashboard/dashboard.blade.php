<x-app-layout>



    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
                <div class="card-body">

                    <div class="flex items-center justify-between">

                        <h4 class="text-3xl text-info">Sites</h4>

                    </div>

                    <div class="table-responsive mt-4">

                        @if (count($sites))

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-info text-white fw-bold">Date</th>
                                        <th class="bg-info text-white fw-bold">Site Name</th>
                                        <th class="bg-info text-white fw-bold"> Location </th>
                                        <th class="bg-info text-white fw-bold">Contact No</th>
                                        <th class="bg-info text-white fw-bold"> Site Owner Name </th>
                                        <th class="bg-info text-white fw-bold"> Service Charge </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($sites as  $site)

                                        <tr>

                                            <td>
                                                {{ $site->created_at->format('d-M-Y') }}
                                            </td>

                                            <td title=" View {{ $site->site_name }} details...">
                                                <a href="{{ route('dashboard.show', [base64_encode($site->id)]) }}"
                                                    class="fw-bold link-offset-2 link-underline link-underline-opacity-0">
                                                    <mark>{{ ucfirst($site->site_name) }}</mark>
                                                </a>
                                            </td>

                                            <td> {{ ucfirst($site->location) }} </td>

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


                                        </tr>
                                    @endforeach

                                </tbody>


                            </table>
                        @else
                            <h1 class="display-4">No records found..</h1>

                        @endif
                    </div>

                    <div class="mt-4">
                        {{ $sites->links() }}

                    </div>
                </div>


        </div>
    </div>
</x-app-layout>
