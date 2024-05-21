<div id="m_aside_left" class="m-grid__item	m-aside-left  m-aside-left--skin-dark ">
	<!-- BEGIN: Aside Menu -->
    <div id="m_ver_menu" class="m-aside-menu  m-aside-menu--skin-dark m-aside-menu--submenu-skin-dark m-aside-menu--dropdown"
        data-menu-vertical="true" m-menu-dropdown="1" m-menu-scrollable="0" m-menu-dropdown-timeout="500" >
	<ul class="m-menu__nav  m-menu__nav--dropdown-submenu-arrow">

        <li class="m-menu__item" aria-haspopup="true">
            <a href="{{route('home')}}" class="m-menu__link ">
                <span class="m-menu__item-here"></span>
                <i class="m-menu__link-icon fa fa-home"></i>
                <span class="m-menu__link-text">الرئيسية</span>
            </a>
        </li>
        @foreach($modules as $module)
            <li class="m-menu__item m-menu__item--submenu" aria-haspopup="true" m-menu-submenu-toggle="hover">
                <a href="javascript:;" class="m-menu__link m-menu__toggle">
                    <i class="m-menu__link-icon {{$module['icon']}}"></i>
                    <span class="m-menu__link-text">
                        @if($module['text'] == 'الطلبات' && (checkSeenOrder() || checkSeenEmergencyOrder()))
                            {{$module['text']}}
                            <span class="blueDot"></span>
                        @else
                            {{$module['text']}}
                        @endif
                    </span>
                    <i class="m-menu__ver-arrow la la-angle-right"></i>
                </a>
                <div class="m-menu__submenu " m-hidden-height="840" style="display: none; overflow: hidden;">
                    <span class="m-menu__arrow"></span>
                    <ul class="m-menu__subnav">
                        @foreach($module['element'] as $element)
                            <li class="m-menu__item " aria-haspopup="true">
                                <a href="{{route($element->url_name)}}" class="m-menu__link ">
                                    <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                        <span></span>
                                    </i>
                                    <span class="m-menu__link-text">
                                        @if($element->prefix == 'orders' && checkSeenOrder() && $element->prefix != 'logs')<span class="dot"></span>
                                        @elseif($element->prefix == 'emergency-orders' && checkSeenEmergencyOrder() && $element->prefix != 'logs') <span class="redDot"></span> @endif
                                        {{$element->name}}
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </li>
            {{-- <li class="m-menu__item" aria-haspopup="true">
                <a href="{{route($module->url_name)}}" class="m-menu__link ">
                    <span class="m-menu__item-here"></span>
                    <i class="m-menu__link-icon {{$module->icon}}"></i>
                    <span class="m-menu__link-text">{{$module->name}}</span>
                </a>
            </li> --}}
            {{-- @endif --}}
        @endforeach

        {{--  m-menu__item--active  --}}

	</ul>
	</div>
	<!-- END: Aside Menu -->
</div>
