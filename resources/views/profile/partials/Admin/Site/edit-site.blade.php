<x-app-layout>
    <x-breadcrumb :names="['Sites', 'Edit ' . $site->site_name]" :urls="['admin/sites', 'admin/sites/' . base64_encode($site->id) . '/edit']" />

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('sites.update', [base64_encode($site->id)]) }}"
                        class="forms-sample material-form" id="editSiteForm">
                        @csrf
                        @method('PUT')

                        <!-- Site Name -->
                        <div class="form-group">
                            <input type="text" name="site_name" value="{{ $site->site_name }}" />
                            <label for="input" class="control-label">Site Name</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('site_name')" class="mt-2" />
                        </div>

                        <!-- Service Charge -->
                        <div class="form-group">
                            <input type="number" name="service_charge" value="{{ $site->service_charge }}"
                                step="0.01" min="0" />
                            <label for="input" class="control-label">Service Charge</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('service_charge')" class="mt-2" />
                        </div>

                        <!-- Location -->
                        <div class="form-group">
                            <input type="text" name="location" value="{{ $site->location }}" />
                            <label for="input" class="control-label">Location</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('location')" class="mt-2" />
                        </div>

                        <!-- Site Owner Name -->
                        <div class="form-group">
                            <input type="text" name="site_owner_name" value="{{ $site->site_owner_name }}" />
                            <label for="input" class="control-label">Site Owner Name</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('site_owner_name')" class="mt-2" />
                        </div>

                        <!-- Contact No -->
                        <div class="form-group">
                            <input type="text" name="contact_no" value="{{ $site->contact_no }}" />
                            <label for="input" class="control-label">Contact No</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('contact_no')" class="mt-2" />
                        </div>


                        <div class="form-group mb-3">
                            <div class="dropdown">
                                <button class="form-control d-flex justify-content-between align-items-center"
                                    type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span id="selectedUsersText" class="text-muted">
                                        @if ($site->users->count() > 0)
                                            {{ $site->users->pluck('name')->join(', ') }}
                                        @else
                                            Select Engineers
                                        @endif
                                    </span>
                                    <i class="fas fa-chevron-down text-secondary"></i>
                                </button>

                                <div id="user_ids-error" class="error-message invalid-feedback"></div>

                                <div class="dropdown-menu w-100 p-0" aria-labelledby="userDropdown">
                                    <!-- Search -->
                                    <div class="p-2 border-bottom">
                                        <input type="text" class="form-control form-control-sm" id="userSearch"
                                            placeholder="Search engineers..." onkeyup="filterUsers()">
                                    </div>

                                    <!-- Users List -->
                                    <div style="max-height: 200px; overflow-y: auto;">
                                        @foreach ($users as $user)
                                            <div class="user-option">
                                                <div class="form-check px-3">
                                                    <input class="form-check-input user-checkbox" type="checkbox"
                                                        style="margin-left: 0px" name="engineer_ids[]"
                                                        id="user_checkbox_{{ $user->id }}"
                                                        value="{{ $user->id }}"
                                                        {{ $site->users->contains($user->id) ? 'checked' : '' }}
                                                        onchange="updateSelectedUsersText()">
                                                    <label class="form-check-label"
                                                        for="user_checkbox_{{ $user->id }}">
                                                        {{ $user->name }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Buttons -->
                                    <div class="d-flex justify-content-end border-top px-2 py-1">
                                        <button type="button" class="btn btn-sm" onclick="clearAllUsers()">
                                            <i class="fas fa-times me-1"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm" onclick="closeUserDropdown()">
                                            <i class="fas fa-check me-1"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <x-input-error :messages="$errors->get('engineer_ids')" class="mt-2" />
                        </div>


                        <button class="btn btn-success mt-3"><span>Save</span></button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize with current selections
            updateSelectedUsersText();
        });

        // Update selected user text
        function updateSelectedUsersText() {
            const checkboxes = document.querySelectorAll('.user-checkbox:checked');
            const selectedText = document.getElementById('selectedUsersText');
            const total = document.querySelectorAll('.user-checkbox').length;

            if (checkboxes.length === 0) {
                selectedText.textContent = 'Select Engineers';
            } else if (checkboxes.length <= 3) {
                // Show names when 3 or fewer engineers are selected
                const names = Array.from(checkboxes).map(checkbox => {
                    return checkbox.nextElementSibling.textContent.trim();
                }).join(', ');
                selectedText.textContent = names;
            } else {
                // Show count when more than 3 engineers are selected
                selectedText.textContent = `${checkboxes.length} Engineers Selected`;
            }
        }

        // Clear all
        function closeUserDropdown() {
            document.querySelector('.dropdown-menu').classList.remove('show');
        }

        function clearAllUsers() {
            document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = false);
            updateSelectedUsersText();
        }

        // Filter user list
        function filterUsers() {
            const term = document.getElementById('userSearch').value.toLowerCase();
            document.querySelectorAll('.user-option').forEach(option => {
                const label = option.querySelector('label').textContent.toLowerCase();
                option.style.display = label.includes(term) ? 'block' : 'none';
            });
        }

        // Toggle select all
        function toggleSelectAllUsers() {
            const checked = document.getElementById('selectAllUsers').checked;
            document.querySelectorAll('.user-checkbox').forEach(checkbox => {
                checkbox.checked = checked;
            });
            updateSelectedUsersText();
        }
    </script>
</x-app-layout>
