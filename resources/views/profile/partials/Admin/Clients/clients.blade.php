<x-app-layout>
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card-body">

                <div class="flex items-center justify-between">
                    <h4 class="text-xl text-info fw-bold">Clients</h4>

                    <a class="btn btn-info text-white" href="{{ route('clients.create') }}">
                        Create Client
                    </a>
                </div>

                <div class="table-responsive mt-4">

                    @if (count($clients))
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="bg-info fw-bold text-white"> Name </th>
                                    <th class="bg-info fw-bold text-white"> Username /  Number </th>
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
                        <h1 class="display-4">No records found..</h1>

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
