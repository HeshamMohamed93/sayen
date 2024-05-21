<header id="m_header" class="m-grid__item m-header "  m-minimize-offset="200" m-minimize-mobile-offset="200" >
    <div class="m-container m-container--fluid m-container--full-height">
       <div class="m-stack m-stack--ver m-stack--desktop">
          <div class="m-stack__item m-brand  m-brand--skin-dark ">
             <div class="m-stack m-stack--ver m-stack--general">
                <div class="m-stack__item m-stack__item--middle m-stack__item--center m-brand__logo">
                   <a href="{{route('home')}}" class="m-brand__logo-wrapper">
                   <img class="rounded-circle" src="{{asset('public/img/logo.jpg')}}"/>
                   </a>  
                </div>
                <div class="m-stack__item m-stack__item--middle m-brand__tools">
                   <!-- BEGIN: Responsive Aside Left Menu Toggler -->
                   <a href="javascript:;" id="m_aside_left_offcanvas_toggle" class="m-brand__icon m-brand__toggler m-brand__toggler--left m--visible-tablet-and-mobile-inline-block">
                   <span></span>
                   </a>
                   <!-- END -->
                   <!-- BEGIN: Topbar Toggler -->
                   <a id="m_aside_header_topbar_mobile_toggle" href="javascript:;" class="m-brand__icon m--visible-tablet-and-mobile-inline-block">
                   <i class="flaticon-more"></i>
                   </a>
                   <!-- BEGIN: Topbar Toggler -->
                </div>
             </div>
          </div>
          <!-- END: Brand -->			
          <div class="m-stack__item m-stack__item--fluid m-header-head" id="m_header_nav">
             <!-- BEGIN: Horizontal Menu -->
             <button class="m-aside-header-menu-mobile-close  m-aside-header-menu-mobile-close--skin-dark " id="m_aside_header_menu_mobile_close_btn"><i class="la la-close"></i></button>
             <!-- END: Horizontal Menu -->
             <!-- BEGIN: Topbar -->
             <div id="m_header_topbar" class="m-topbar  m-stack m-stack--ver m-stack--general">
                <div class="m-stack__item m-topbar__nav-wrapper">
                   <ul class="m-topbar__nav m-nav m-nav--inline">
                     <li id="reloadNotification" class="reloadNotification m-nav__item m-topbar__notifications m-topbar__notifications--img m-dropdown m-dropdown--large m-dropdown--header-bg-fill m-dropdown--arrow m-dropdown--align-center m-dropdown--mobile-full-width" m-dropdown-toggle="click" m-dropdown-persistent="1" aria-expanded="true">
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
                                          <a class="btn readAllNotification" style="text-align: center;" >{{ trans('admin.read_all') }}</a>
                                       @endif
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </li>

                      <li class="m-nav__item m-topbar__user-profile m-topbar__user-profile--img m-dropdown m-dropdown--medium m-dropdown--arrow m-dropdown--header-bg-fill m-dropdown--align-right m-dropdown--mobile-full-width m-dropdown--skin-light" m-dropdown-toggle="click">
                         <a href="#" class="m-nav__link m-dropdown__toggle">
                         <span class="m-topbar__userpic">
                         <img src="{{ Auth::user()->image_path}}" alt=""/>
                         </span>					
                         </a>
                         <div class="m-dropdown__wrapper">
                            <span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
                            <div class="m-dropdown__inner">
                               <div class="m-dropdown__header m--align-center" style="background: url({{asset('public/admin/assets/app/media/img/misc/user_profile_bg.jpg')}}); background-size: cover;">
                                  <div class="m-card-user m-card-user--skin-dark">
                                     <div class="m-card-user__pic">
                                        <img src="{{ Auth::user()->image_path}}" alt=""/>
                                     </div>
                                     <div class="m-card-user__details">
                                        <span class="m-card-user__name m--font-weight-500">{{Auth::user()->name}}</span>
                                     </div>
                                  </div>
                               </div>
                               <div class="m-dropdown__body">
                                  <div class="m-dropdown__content">
                                     <ul class="m-nav m-nav--skin-light">
                                        <li class="m-nav__item">
                                           <a href="{{route('profile')}}" class="m-nav__link">
                                           <i class="m-nav__link-icon flaticon-profile-1"></i>
                                           <span class="m-nav__link-title">  
                                           <span class="m-nav__link-wrap">      
                                           <span class="m-nav__link-text">@lang('admin.profile')</span>      
                                           </span>
                                           </span>
                                           </a>
                                        </li>
                                        <li class="m-nav__separator m-nav__separator--fit">
                                        </li>
                                        <li class="m-nav__item">
                                           <a href="{{route('logout')}}" class="btn m-btn--pill btn-secondary m-btn m-btn--custom m-btn--label-brand m-btn--bolder">@lang('admin.logout')</a>
                                        </li>
                                     </ul>
                                  </div>
                               </div>
                            </div>
                         </div>
                      </li>
                   </ul>
                </div>
             </div>
             <!-- END: Topbar -->			
          </div>
       </div>
    </div>
 </header>
 <!-- END: Header -->