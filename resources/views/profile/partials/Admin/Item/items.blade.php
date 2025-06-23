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

    @if (session('status') === 'hasItem')
        <x-error-message message="This Item Cannot Be deleted" />
    @endif

    <div class="row">


        <div class="d-flex justify-content-end">

            <a class="btn btn-sm btn-success ps-2" href="{{ url($user . '/items/create') }}">
                <i class="fa-solid fa-boxes-stacked me-1"></i> <!-- me-2 adds margin-right -->
                Create Item
            </a>

        </div>

        <div class="table-responsive mt-4">

            @if (count($items) > 0)
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="bg-info fw-bold text-white"> Item </th>
                            @if ($user === 'admin')
                                <th class="bg-info fw-bold text-white"> Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>

                        @foreach ($items as $item)
                            <tr>

                                <td>
                                    {{ $item->item_name }}
                                </td>

                                @if ($user === 'admin')
                                    <td class="d-flex align-items-center"> <!-- Added flex container with gap -->
                                        <!-- Edit Icon -->
                                        <a href="{{ url($user . '/items/' . $item->id . '/edit') }}"
                                            class="text-decoration-none me-2"> <!-- Added me-2 for right margin -->
                                            <i class="fa-regular fa-pen-to-square bg-white rounded-full p-2"></i>
                                            <!-- Added p-2 for padding -->
                                        </a>

                                        <!-- Delete Form -->
                                        <form id="delete-form-{{ $item->id }}"
                                            action="{{ url($user . '/items/' . $item->id) }}" method="POST"
                                            style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>

                                        <!-- Delete Icon -->
                                        <a href="{{ url($user . '/items/' . $item->id) }}"
                                            onclick="event.preventDefault(); if (confirm('Are you sure you want to delete this item?')) document.getElementById('delete-form-{{ $item->id }}').submit();"
                                            class="text-decoration-none"> <!-- Added text-decoration-none -->
                                            <i class="fa-solid fa-trash text-danger bg-white rounded-full p-2"></i>
                                            <!-- Changed to fa-trash and added p-2 -->
                                        </a>
                                    </td>
                                @endif


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
