<x-app-layout>


    @php

        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';

    @endphp

    <x-breadcrumb :names="['Suppliers']" :urls="[$user . '/suppliers']" />

    @if (session('status') === 'update')
        <x-success-message message='Supplier Updated Succussfully...' />
    @endif

    @if (session('status') === 'error')
        <x-error-message message='Supplier Deleted...' />
    @endif

    @if (session('status') === 'create')
        <x-success-message message='Supplier Created Succussfully...' />
    @endif

    @if (session('status') === 'delete')
        <x-error-message message='Supplier Deleted Succussfully...' />
    @endif

    @if (session('status') === 'hasPaymnent')
        <x-error-message message='Supplier cannot be deleted...' />
    @endif

    <div class="row">


        <div class="col-sm-12">

            <div class="d-flex justify-content-end">

                <a class="btn btn-sm btn-success" href="{{ url($user . '/suppliers/create') }}">
                    <i class="fa fa-user pe-2"></i>
                    Create Supplier
                </a>

            </div>

            <div class="col-lg-12 grid-margin stretch-card">

                <div class="card-body ">
                    <div class="table-responsive mt-4">

                        @if ($suppliers)
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-info fw-bold text-white">Name</th>
                                        <th class="bg-info fw-bold text-white">Contact No</th>
                                        <th class="bg-info fw-bold text-white">Address</th>
                                        <th class="bg-info fw-bold text-white">Supplier Type</th>
                                        <th class="bg-info fw-bold text-white">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($suppliers as $supplier)
                                        <tr>

                                            <td>
                                                <a class="fw-bold link-offset-2 link-underline link-underline-opacity-0"
                                                    href="{{ url($user . '/suppliers', [$supplier]) }}">
                                                    {{ strtoupper($supplier->name) }}
                                                </a>
                                            </td>

                                            <td>
                                                <a href="tel:{{ $supplier->contact_no }}">
                                                    +91-{{ $supplier->contact_no }}
                                                </a>
                                            </td>

                                            <td>
                                                {{ ucfirst($supplier->address) }}
                                            </td>

                                            <td>
                                                {{ $supplier->is_raw_material_provider ? 'Raw Material Provider' : '' }}
                                                {{ $supplier->is_workforce_provider ? 'Workforce Provider' : '' }}
                                            </td>

                                            <td class="space-x-4">
                                                <a href="{{ url($user . '/suppliers/' . $supplier->id . '/edit') }}">
                                                    <i
                                                        class="fa-regular fa-pen-to-square text-xl bg-white rounded-full"></i>
                                                </a>
                                                <form id="delete-form-{{ $supplier->id }}"
                                                    action="{{ url($user . '/suppliers/' . $supplier->id) }}"
                                                    method="POST" style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                                <a href="#"
                                                    onclick="event.preventDefault(); if (confirm('Are you sure you want to delete this supplier?')) document.getElementById('delete-form-{{ $supplier->id }}').submit();">
                                                    <i
                                                        class="fa-solid fa-trash-o text-xl text-red-600 bg-white rounded-full px-2 py-1"></i>
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
                        {{ $suppliers->links() }}
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
