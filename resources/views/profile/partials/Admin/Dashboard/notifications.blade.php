<x-app-layout>



    <div class="row">

        <div class="col-lg-12">

            <div class="card-body">

                <div class="flex items-center justify-between">

                    <h2 class=" text-info fw-bold">Notifications</h2>

                </div>

            </div>

        </div>
    </div>

    <div class="card mt-3">

        <div class="card-body">
            <ul>
                @if (count($notifications) > 0)
                    @foreach ($notifications as $notification)

                        <li class="row border-b p-2 ">
                            <div class=" col-1 " style="cursor: pointer">
                                <a class="text-white" href="{{ route('admin.markAsRead', $notification->id) }}">
                                    <i class="fa-solid fa-x bg-light p-2 rounded"></i>
                                </a>
                            </div>
                            <div class="col-8  ">
                                <h1 class="badge badge-info">
                                    @if ( $notification->type === 'App\Notifications\UserSiteNotification')
                                        Site Notification
                                    @else
                                        Verification Notification
                                    @endif
                                </h1>
                                <p class=" fw-bold">{{ $notification->data['message'] }}</p>
                            </div>
                            <div class=" col-3  ">
                                <i class="fa-solid fa-clock text-info"></i>
                                 {{ $notification->created_at->diffForHumans() }} :
                                {{ \Carbon\Carbon::parse($notification->created_at)->format('D-M-Y H:s A ') }}
                            </div>
                        </li>
                    @endforeach
                @else
                    <h2 class="text-danger">No Notifications Awailable Yet</h2>
                @endif

            </ul>

        </div>


    </div>

</x-app-layout>
