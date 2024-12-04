<x-app-layout>


    <x-breadcrumb :names="['Site Engineer']" :urls="['admin/users']" />

    @if (session('status') === 'create')
        <x-success-message message="Site Engineer Created..." />
    @endif

    @if (session('status') === 'update')
        <x-success-message message="Site Engineer Updated..." />
    @endif

    @if (session('status') === 'delete')
        <x-success-message message="Site Engineer Deleted..." />
    @endif

    @if (session('status') === 'error')
        <x-success-message message="Sorry! Site Engineer Not Found..." />
    @endif

    <div class="row">

        <div class="d-flex justify-content-end">

            <a class="btn btn-sm btn-success" href="{{ url('admin/users/create') }}" class="float-right">
                <i class="fa fa-user-plus pr-1" aria-hidden="true"></i>
                Create Engineer
            </a>

        </div>

        <div class="table-responsive mt-4">



            @if (count($users))

                <table class="table table-bordered">
                    <thead>
                        <tr>

                            <th class="bg-info fw-bold text-white"> Name </th>
                            <th class="bg-info fw-bold text-white"> Username </th>
                            <th class="bg-info fw-bold text-white"> Projects Assigned </th>
                            <th class="bg-info fw-bold text-white"> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td> {{ ucfirst($user->name) }} </td>

                                <td>
                                    {{ $user->username }}
                                </td>

                                <td>
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                        data-bs-target="#exampleModal">
                                        View Sites
                                    </button>
                                </td>

                                <div class="modal fade" id="exampleModal" tabindex="-1"
                                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-scrollablel  modal-xl">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5 text-info" id="exampleModalLabel">
                                                    Supplier Name : {{ ucwords($user->name) }}</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">

                                                <div class="row g-1">
                                                    @foreach ($user->sites as $site)
                                                        <div class="col">
                                                            <p class=" p-2  rounded text-black me-1 "> ->
                                                                {{ ucwords($site->site_name) }}</p>
                                                        </div>
                                                    @endforeach
                                                </div>

                                            </div>
                                            <div class="modal-footer">

                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Close
                                                </button>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <td class="space-x-4">
                                    <a href="{{ route('admin.edit-user', ['id' => $user->id]) }}">
                                        <i class="fa-regular fa-pen-to-square text-xl bg-white rounded-full "></i>
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
                            <td class="text-center text-danger fw-bold">No Records Awailable Yet</td>
                        </tr>
                    </tbody>
                </table>

            @endif

        </div>

        <div class="mt-4">
            {{ $users->links() }}

        </div>
    </div>

    <!-- Button trigger modal -->







</x-app-layout>
