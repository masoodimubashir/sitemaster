<x-app-layout>


    <x-breadcrumb :names="['Items', 'Create Item']" :urls="['admin/items', 'admin/items/create']" />


    <div class="row">

        <div class="col-md-12 grid-margin stretch-card">

            <div class="card">

                <div class="card-body">

                    <form method="POST" action="{{ route('items.store') }}" class="forms-sample material-form">

                        @csrf

                        <div class="form-group">
                            <input type="text" name="item_name" value="{{ old('item_name') }}" />
                            <label for="input" class="control-label">Item Name</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('item_name')" class="mt-2" />
                        </div>

                        <button class=" btn btn-info mt-3"><span>Save</span></button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>
