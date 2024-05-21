<a href="#" class="m-nav__link m-dropdown__toggle" id="m_topbar_notification_icon">
   <span class="m-nav__link-badge m-badge m-badge--accent">{{count($notifications)}}</span>
   <span class="m-nav__link-icon"><i class="flaticon-alert-2"></i></span>
</a>
<div class="m-dropdown__wrapper" style="z-index: 101;margin-right: -300px;">
   <span class="m-dropdown__arrow m-dropdown__arrow--center" style="left: 60px; right: auto;"></span>
   <div class="m-dropdown__inner">
      <div class="m-dropdown__body">
         <div class="m-dropdown__content">
            <div class="m-list-timeline m-list-timeline--skin-light">
               <div class="m-list-timeline__items" style="overflow-y: scroll; max-height:200px;">
                  @if(count($notifications) > 0)
                     @foreach($notifications as $notification)
                        <div class="m-list-timeline__item">
                           <span class="m-list-timeline__badge m-list-timeline__badge--state1-success"></span>
                           <span class="m-topbar__userpic">
                              <img src="{{asset('public/img').'/'.$notification->image}}" width="30" height="30">
                              </span>
                           <a href="{{route($order_notification_link_type, $notification->order_id)}}" class="pr-5 m-list-timeline__text order-notification" data-notification-id="{{$notification->id}}">{{$notification->message}}</a>
                        </div>
                        <span class="notification-time">{{$notification->created_at}}</span>
                     @endforeach 
                  @else 
                     <div class="m-list-timeline__item">
                        <span class="m-list-timeline__badge m-list-timeline__badge--state1-success"></span>
                        <a href="#" class="m-list-timeline__text">{{trans('admin.no_notifications')}}</a>
                     </div>
                  @endif 
               </div>
               @if(count($notifications) > 0)
                  <a class="btn readAllNotification" style="text-align: center;">{{ trans('admin.read_all') }}</a>
               @endif
            </div>
         </div>
      </div>
   </div>
</div>