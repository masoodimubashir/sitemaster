<x-app-layout>
    @php
        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp

    <div class="container-fluid ">
        

        <!-- Notifications Content -->
        <div class="row">
            <div class="col-12">
                @if ($notifications->count() > 0)
                    <!-- Notifications Grid -->
                    <div class="notifications-container">
                        @foreach ($notifications as $index => $notification)
                            <div class="notification-card mb-4" >
                                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                                    <div class="card-body p-4">
                                        <div class="row align-items-center">
                                           

                                            <!-- Notification Content -->
                                            <div class="col">
                                                <div class="notification-content">
                                                    <!-- Type Badge -->
                                                    <div class="mb-2">
                                                        @if ($notification->type === 'App\Notifications\UserSiteNotification')
                                                               Site Notification
                                                        @else
                                                            <span class="badge bg-gradient-success text-white px-3 py-1 rounded-pill">
                                                                Verification
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <!-- Message -->
                                                    <h5 class="notification-message mb-2">
                                                        {{ ucwords($notification->data['message']) }}
                                                    </h5>

                                                    <!-- Time Info -->
                                                    <div class="notification-time d-flex align-items-center text-muted">
                                                        <i class="fas fa-clock me-2"></i>
                                                        <span class="me-3">{{ $notification->created_at->diffForHumans() }}</span>
                                                        <span class="text-muted">
                                                            {{ \Carbon\Carbon::parse($notification->created_at)->format('M j, Y \a\t g:i A') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Action Button -->
                                            <div class="col-auto">
                                                <a href="{{ url($user . '/markAsRead/' . $notification->id) }}" 
                                                   class="btn btn-outline-danger btn-floating rounded-circle p-2"
                                                   title="Mark as read">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Progress Bar -->
                                    <div class="notification-progress"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if ($notifications->hasPages())
                        <div class="row mt-5">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="pagination-info">
                                        <p class="text-muted mb-0">
                                            Showing {{ $notifications->firstItem() }} to {{ $notifications->lastItem() }} 
                                            of {{ $notifications->total() }} notifications
                                        </p>
                                    </div>
                                    <div class="pagination-controls">
                                        {{ $notifications->onEachSide(2)->links('pagination::bootstrap-4') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <!-- Premium Empty State -->
                    <div class="empty-state-container">
                        <div class="card border-0 shadow-lg rounded-4 text-center">
                            <div class="card-body py-5">
                                <div class="empty-state-icon mb-4">
                                    <div class="icon-circle">
                                        <i class="fas fa-bell-slash"></i>
                                    </div>
                                </div>
                                <p class="text-muted fs-5 mb-4">
                                    You have no new notifications at the moment.<br>
                                    Check back later for updates.
                                </p>
                             
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>


</x-app-layout>