@extends('frontend.layout.body')

@section('banner')
<header class="header" style="background-image:url({{asset('public/frontend/src/imgs/headerBackground.png')}});">
   <div class="container pt-100  wow fadeInUpBig animated pb-100">
      <div class="row pt-20">
         <div class="col-md-6 col-12  wow fadeInUp animated">
            <div class="text-center">
               <img src="{{asset('public/frontend/src/imgs/logo.png')}}">
               <p class="text-white font-22 w-75 m-auto pt-30">{{$general_setting->about_sayen_shortcut}}</p>
               @if($general_setting->user_app_android_url != null || $general_setting->user_app_ios_url != null)
               <h2 class="text-second font-20 mt-30">{{trans('frontend.download_app')}}</h2>
               @endif
               <div>
                  @if($general_setting->user_app_android_url != null)
                  <button class="main-btn btn font-20 mr-15 mt-10 p-2" onclick="window.location.href='{{$general_setting->user_app_android_url}}'">
                  <img src="{{asset('public/frontend/src/imgs/google-play.png')}}" class="ml-30"> {{ trans('frontend.android_app') }}
                  </button>
                  @endif

                  @if($general_setting->user_app_ios_url != null)
                  <button class="second-btn btn font-20 p-2 mt-10 mr-15" onclick="window.location.href='{{$general_setting->user_app_ios_url}}'">
                  <i class="fab fa-apple ml-30 font-26"></i> {{ trans('frontend.ios_app') }}
                  </button>
                  @endif
               </div>
            </div>
         </div>
         <div class="col-md-6 col-12  wow fadeInUp animated">
            <img src="{{asset('public/frontend/src/imgs/headerPhone.png')}}">
         </div>
      </div>
   </div>

   @if($slider_indicators > 0)
      <div class="features  wow fadeInUp animated  pb-100">
         <h2 class="text-main fw-800 font-28 text-center  wow fadeInUp animated" style="color:#fff;">{{ trans('frontend.features') }}</h2>
         <div id="carouselExampleIndicators" class="carousel pt-50 pb-50   wow fadeInUp animated slide" delay="200ms" data-ride="carousel">
            <ol class="carousel-indicators">
               @for($i=0; $i<$slider_indicators; $i++)
                  <li data-target="#carouselExampleIndicators" data-slide-to="{{$i}}" class="{{($i == 0) ? 'active': ''}} indicator"></li>
               @endfor
            </ol>
            <div class="carousel-inner">
               @foreach($features as $index => $feature)
                     <div class="carousel-item {{($index == 0) ? 'active' : ''}}">
                        <div class="container">
                           <div class="row">
                              @foreach($feature as $slide_feature)
                              <div class="col-md-4 col-12">
                                 <div class="featureCard text-center">
                                    <img src="{{$slide_feature['image_path']}}">
                                    <h3 class="text-second mt-30 font-18">{{$slide_feature['title']}}</h3>
                                    <p class="text-black font-16">
                                       {{$slide_feature['content']}}
                                    </p>
                                 </div>
                              </div>
                              @endforeach
                           </div>
                        </div>
                     </div>
               @endforeach
            </div>
         </div>
      </div>
   @endif
</header>
@endsection

@section('about')
<section class="about" style="background-image: url({{asset('public/frontend/src/imgs/appBackground.png')}})">
   <div class="container wow fadeInUp animated pt-100">
      <div class="row">
         <div class="col-md-6 col-12 text-center">
            <img src="{{asset('public/frontend/src/imgs/appPhone.png')}}">
         </div>
         <div class="col-md-6 col-12">
            <div class="text-right">
               <h2 class="text-white font-22">{{ trans('frontend.about_app')}}</h2>
               <p class="text-white font-18 pt-50">
                  {{$about->content}}
               </p>
            </div>
         </div>
      </div>
   </div>
</section>
@endsection

{{--
@section('contact-us')
<section class="contact  wow fadeInUp animated mb-50">
   <div class="container">
      <div class="row">
         <div class="col-md-6 col-12 text-center">
            <img src="{{asset('public/frontend/src/imgs/Final.png')}}">
            <p class="text-main font-16 m-auto w-75 text-right">{{$general_setting->about_sayen_shortcut}}</p>
            <div class="w-50 pt-30 m-auto d-flex justify-content-around align-items-center">
               <span class="icon" onclick="window.location.href='{{$about->telegram}}'">
               <i class="fab fa-telegram-plane pointer text-white font-26"></i>
               </span>
               <span class="icon" onclick="window.location.href='{{$about->facebook}}'">
               <i class="fab fa-facebook-f pointer text-white font-26"></i>
               </span>
               <span class="icon" onclick="window.location.href='{{$about->twitter}}'">
               <i class="fab fa-twitter pointer text-white font-26"></i>
               </span>
               <span class="icon" onclick="window.location.href='{{$about->instagram}}'">
               <i class="fab fa-instagram pointer text-white font-26"></i>
               </span>
               <span class="icon" onclick="window.location.href='{{$about->whatsapp}}'">
               <i class="fab fa-whatsapp pointer text-white font-26"></i>
               </span>
            </div>
         </div>
         <div class="col-md-6 col-12">
            <div  class="mt-70 text-center">
               <h2 class="text-main fw-800 font-22">
                  <span class="text-second font-24">{{trans("frontend.contact_us")}}</span>
                  {{trans('frontend.send_suggesstion')}}
               </h2>
            </div>
            <form class="mt-30 form" action="{{route('send-message')}}" method="post">
               @csrf() @method('post')
               <div class="d-flex justify-content-center align-items-center">
                  <input class="form-control ml-2 shadow" name="name" placeholder="{{trans('frontend.name')}}">
                  <br><label class="name-error"></label>
                  <input class="form-control shadow" name="email" placeholder="{{trans('frontend.email')}}">
                  <br><label class="email-error"></label>
               </div>
               <textarea class="form-control mt-2 shadow" name="message" rows="5" placeholder="{{trans('frontend.message')}}"></textarea>
               <br><label class="message-error"></label>
               <button class="main-btn btn mt-20 w-100" type="submit">
                  {{trans('frontend.send')}}
                  <i class="fas fa-paper-plane mr-30 "></i>
               </button>
            </form>
         </div>
      </div>
   </div>
</section>
@endsection
--}}