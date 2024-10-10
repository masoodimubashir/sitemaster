<x-app-layout>



    @section('notifications')
        <li class="nav-item dropdown">
            <a class="nav-link count-indicator {{ $notifications ? 'text-danger' : 'text-black' }}"
                id="notificationDropdown" href="#" data-bs-toggle="dropdown">
                <i class="icon-bell"> </i>

                @if ($notifications)
                    <span>
                        {{ $notifications->count() }}
                    </span>
                @endif

            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0"
                aria-labelledby="notificationDropdown">
                <a class="dropdown-item py-3 border-bottom">
                    <p class="mb-0 fw-medium float-start">You have
                        {{ $notifications->count() }} Notification </p>
                    {{-- <a  href="{{ route('user.markAsRead') }}" > --}}
                    <span class="badge badge-pill badge-info float-end">
                        Mark As Read
                    </span>
                    {{-- </a> --}}

                </a>
                @foreach ($notifications as $key => $notification)
                    <a class="dropdown-item preview-item py-3">
                        <div class="preview-thumbnail text-black">
                            {{ $key }}
                        </div>
                        <div class="preview-item-content">
                            <h6 class="preview-subject fw-normal text-dark mb-1">
                                {{ $notification->data['message'] }}</h6>

                        </div>
                    </a>
                @endforeach

            </div>
        </li>
    @endsection

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}
                    {{ auth()->user()->email }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
