<x-app-layout>

    <div class="row">

        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-3xl text-info fw-bold">Suppliers</h4>
                    </p>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Name</th>
                                    <th>Restore</th>
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
                                        <td>No Records Found </td>
                                    </tr>
                                @endif
                            </tbody>


                        </table>

                        {{ $suppliers->links() }}

                    </div>
                </div>
            </div>
        </div>
    </div>


</x-app-layout>
