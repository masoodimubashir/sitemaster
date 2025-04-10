<x-app-layout>

    <div class="row">

        <div class="col-12 grid-margin stretch-card">
            <div class="card-body">
                <h4 class="card-title text-3xl text-info fw-bold">Suppliers</h4>
                <div class="table-responsive table-bordered mt-4">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="bg-info fw-bold text-white">Id</th>
                                <th class="bg-info fw-bold text-white">Name</th>
                                <th class="bg-info fw-bold text-white">Restore</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($suppliers))
                                @foreach ($suppliers as $key => $supplier)
                                    <tr>

                                        <td>{{ $key }}</td>
                                        <td>{{ ucfirst($supplier->name) }}</td>
                                        <td>
                                            <a
                                                href="{{ route('trash.restore', ['model_name' => 'supplier', 'id' => $supplier->id]) }}">
                                                <i class="fa fa-history display-5 text-success"></i>

                                            </a>
                                        </td>

                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="3" class="text-danger fw-bold text-center">No Records Found </td>
                                </tr>
                            @endif
                        </tbody>


                    </table>

                    {{ $suppliers->links() }}

                </div>
            </div>
        </div>
    </div>


</x-app-layout>
