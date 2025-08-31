<x-app-layout>

    @php
        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp

    <x-breadcrumb :names="['View ' . $dialy_expense->phase->site->site_name, 'Edit ' . $dialy_expense->item_name]" :urls="[
        $user . '/sites/' . base64_encode($dialy_expense->phase->site->id),
        $user . '/daily-expenses/' . base64_encode($dialy_expense->id) . '/edit',
    ]" />

    <div class="row">

        <div class="col-md-12 grid-margin stretch-card">

            <div class="card">

                <div class="card-body">

                    <form method="POST" action="{{ url($user . '/daily-expenses/' . $dialy_expense->id) }}"
                        class="forms-sample material-form" enctype="multipart/form-data">

                        @csrf
                        @method('PUT')

                        {{-- Date --}}
                        <div class="form-group">
                            <input type="date" name="created_at" id="created_at"
                                value="{{ $dialy_expense->created_at ? $dialy_expense->created_at->format('Y-m-d') : '' }}" />
                            <label for="created_at" class="control-label">Date</label>
                            <i class="bar"></i>
                            <p class="mt-1 text-danger" id="created_at-error"></p>
                        </div>

                        <!-- Wager Name -->
                        <div class="form-group">
                            <input id="item_name" type="text" name="item_name"
                                value="{{ $dialy_expense->item_name }}" />
                            <label for="item_name" class="control-label">Item
                                Name</label><i class="bar"></i>
                            @error('item_name')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <div class="form-group">
                            <input id="site_id" type="hidden" name="site_id" value="{{ $dialy_expense->site_id }}" />
                            @error('site_id')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <!-- Price -->
                        <div class="form-group">
                            <input id="price" type="number" name="price" value="{{ $dialy_expense->price }}" />
                            <label for="price" class="control-label">Price</label><i class="bar"></i>
                            @error('price')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <div class="col-12 mt-3">
                            <label for="supplier_id" class="mb-1"
                                style="font-size: 0.8rem; color: rgba(17, 17, 17, 0.48);">Bill</label>
                            <input class="form-control" type="file" id="formFile" name="bill_photo"
                                value="{{ $dialy_expense->bill_photo }}">
                            @error('bill_photo')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror

                        </div>

                        <!-- Select Phase -->
                        <div class=" col-md-6 mt-3">
                            <input id="phase_id" type="hidden" name="phase_id" placeholder="Phase"
                                value="{{ $dialy_expense->phase_id }}" />
                            @error('phase_id')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>


                        <button class="btn btn-success"><span>Save</span></button>
                    </form>

                </div>

            </div>


        </div>
    </div>

</x-app-layout>
