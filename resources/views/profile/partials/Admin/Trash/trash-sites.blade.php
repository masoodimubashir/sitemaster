<x-app-layout>

    <div class="row">

        <div class="col-12 grid-margin stretch-card">

                <div class="card-body">
                    <h4 class="card-title text-3xl text-info fw-bold">Sites</h4>

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
                                @if (count($sites))
                                    @foreach ($sites as $key => $site)
                                        <tr>
                                            <td>{{ $key }}</td>
                                            <td>
                                                <mark
                                                    class="fw-bold link-offset-2 link-underline link-underline-opacity-0">
                                                    {{ ucfirst($site->site_name) }}
                                                </mark>
                                            </td>
                                            <td>
                                                <a
                                                    href="{{ route('trash.restore', ['model_name' => 'site', 'id' => $site->id]) }}">
                                                    <i class="fa fa-history display-5 text-success"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="3" class="text-danger text-center fw-bold">No Records Found </td>
                                    </tr>
                                @endif
                            </tbody>


                        </table>

                        {{ $sites->links() }}

                    </div>
                </div>

        </div>
    </div>


</x-app-layout>
