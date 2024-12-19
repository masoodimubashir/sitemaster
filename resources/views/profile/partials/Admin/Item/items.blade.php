<x-app-layout>

    @php
        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp

    <x-breadcrumb :names="['Items']" :urls="[$user . '/items']" />

    @if (session('status') === 'create')
        <x-success-message message="Item Created..." />
    @endif

    @if (session('status') === 'update')
        <x-success-message message="Item Updated..." />
    @endif

    @if (session('status') === 'delete')
        <x-error-message message="Item Deleted..." />
    @endif

    @if (session('status') === 'error')
        <x-error-message message="Sorry! Item Not Found..." />
    @endif

    @if (session('status') === 'null')
        <x-error-message message="This Item Cannot Be deleted" />
    @endif

    <div class="row">


        <div class="d-flex justify-content-end">

            <a class="btn btn-sm btn-success" href="{{ url($user . '/items/create') }}" class="float-right">
                <i class="fa-solid fa-boxes-stacked pr-2"></i>
                Create Item
            </a>

        </div>

        <div class="table-responsive mt-4">

            @if (count($items) > 0)
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="bg-info fw-bold text-white"> Date </th>
                            <th class="bg-info fw-bold text-white"> Item </th>
                            <th class="bg-info fw-bold text-white"> Actions</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach ($items as $item)
                            <tr>
                                <td> {{ $item->created_at }} </td>

                                <td>
                                    {{ $item->item_name }}
                                </td>



                                <td class="space-x-4">
                                    
                                    <a href="{{ url($user . '/items/' . $item->id . '/edit' ) }}">
                                        <i class="fa-regular fa-pen-to-square fs-5 bg-white rounded-full "></i>
                                    </a>

                                    <form id="delete-form-{{ $item->id }}"
                                        action="{{ url($user . '/items/' . $item->id) }}" method="POST"
                                        style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>

                                    <a href="{{ url($user . '/items/' . $item->id) }}"
                                        onclick="event.preventDefault(); if (confirm('Are you sure you want to delete this supplier?')) document.getElementById('delete-form-{{ $item->id }}').submit();">
                                        <i class="fa-solid fa-trash-o fs-5 text-red-600 bg-white rounded-full"></i>
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
                            <td class="text-center text-danger fw-bold">No Records Available Yet</td>
                        </tr>
                    </tbody>
                </table>

            @endif

        </div>

        <div class="mt-4">
            {{ $items->links() }}

        </div>
    </div>

</x-app-layout>
