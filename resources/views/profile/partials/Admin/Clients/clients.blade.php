<x-app-layout>

    <x-breadcrumb :names="['Clients']" :urls="['admin/clients']" />

    @if (session('message') === 'client')
        <x-success-message message='Client Created Succussfully...' />
    @endif

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card-body">

                <div class="d-flex justify-content-end">

                    <a class="btn btn-sm btn-success" href="{{ url('admin/clients/create') }}" class="float-right">
                        <i class="fa fa-user-plus pr-1" aria-hidden="true"></i>
                        Create Clients
                    </a>

                </div>

                <div class="table-responsive mt-4">

                    @if (count($clients))
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="bg-info fw-bold text-white"> Name </th>
                                    <th class="bg-info fw-bold text-white"> Username / Number </th>
                                    <th class="bg-info fw-bold text-white">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($clients as $client)
                                    <tr>

                                        <td> {{ ucfirst($client->name) }} </td>

                                        <td>
                                            {{ $client->number }}
                                        </td>

                                        <td class="space-x-4">
                                            <a href="{{ route('clients.edit', [base64_encode($client->id)]) }}">
                                                <i
                                                    class="fa-regular fa-pen-to-square text-xl bg-white rounded-full"></i>
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
                    {{ $clients->links() }}

                </div>
            </div>
        </div>
    </div>

    <div id="imageModel" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Image Preview</h5>
                <!-- Close button to dismiss the modal -->
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img id="modalImage" src="" alt="" class="img-fluid">
            </div>
            <div class="modal-footer">
                <!-- Close button in the footer -->
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>





    <script>
        function showImage(src) {
            var modalImage = document.getElementById('modalImage');
            modalImage.src = src;
        }
    </script>
</x-app-layout>
