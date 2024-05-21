<!DOCTYPE html>

<html lang="ar" >
	{{--  Header  --}}
	@include('admin.layout.header')
	{{--  End Header  --}}
	
	<body  class="m-page--fluid m--skin- m-content--skin-light2 m-header--fixed m-header--fixed-mobile m-aside-left--enabled m-aside-left--skin-dark m-aside-left--offcanvas m-footer--push m-aside--offcanvas-default"  >
	
		{{--  Top nav  --}}
		@include('admin.layout.top_header')
		{{--  End To Nav  --}}
		
		<div class="m-grid m-grid--hor m-grid--root m-page">
			<div class="m-grid__item m-grid__item--fluid m-grid m-grid--ver-desktop m-grid--desktop m-body">
				
				{{--  Side Nav  --}}
				@include('admin.layout.side_menu')
				{{--  End Side Nav  --}}
				
				<div class="m-grid__item m-grid__item--fluid m-wrapper">
					<div class="m-subheader ">
						

						@yield('content')

					</div>
					
					<div class="m-content">
						<div id="m_scroll_top" class="m-scroll-top">
							<i class="la la-arrow-up"></i>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- <audio id="xyz" src="https://media.geeksforgeeks.org/wp-content/uploads/20190531135120/beep.mp3" preload="auto"></audio> -->
		<audio id="xyz" src="{{ url('uploads/so-proud-notification.mp3') }}" preload="auto"></audio>
		<audio id="xyz2" src="{{ url('uploads/relax-message-tone.mp3') }}" preload="auto"></audio>
		<script>var base_url = "{{URL::to('/').'/'}}";</script>
		<script src="{{asset('public/admin/assets/vendors/base/vendors.bundle.js')}}" type="text/javascript"></script>
		<script src="{{asset('public/admin/assets/demo/demo3/base/scripts.bundle.js')}}" type="text/javascript"></script>
		<script src="{{asset('public/admin/assets/vendors/custom/fullcalendar/fullcalendar.bundle.js')}}" type="text/javascript"></script>
		<script src="{{asset('public/admin/assets/app/js/dashboard.js').'?time='.time()}}" type="text/javascript"></script>
		<script src="{{asset('public/admin/assets/app/js/bootstrap-datetimepicker.min.js')}}" type="text/javascript"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.26.9/sweetalert2.all.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
		@if(\Request::route()->getName() == 'home')
			<script src="{{asset('public/admin/assets/plugins/calendar/calendar.js').'?time='.time()}}"></script>
		@endif
		<script src="https://rawgit.com/unconditional/jquery-table2excel/master/src/jquery.table2excel.js"></script>

		<script src="{{asset('public/js/admin.js').'?time='.time()}}" type="text/javascript"></script>
		<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.js"></script>
		<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
		<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/buttons/2.1.0/js/dataTables.buttons.min.js"></script>
		<script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
		<script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
		<script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
		<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/buttons/2.1.0/js/buttons.html5.min.js"></script>
		<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/buttons/2.1.0/js/buttons.print.min.js"></script>
		<script type="text/javascript" language="javascript" src="{{ URL::to('public/admin/assets/app/js/jquery.PrintArea.js') }}"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js" ></script>
		@if(checkSeenOrder())
			
			<script>
				setInterval(function(){
					document.getElementById('xyz').play();
					console.log('order');
				},120000);
			</script>
		@endif
		
		@if(checkSeenEmergencyOrder())
			<!-- <script>
				setInterval(function(){
					document.getElementById('xyz2').play();
					console.log('sound');
				},180000);
			</script> -->
		@endif
		<script>
			if($('#map').length)
			{
				$.getScript('https://maps.googleapis.com/maps/api/js?key=AIzaSyD0lvyEMmVw-jCqobmghYJaopzaks9M83A&amp;libraries=places&callback=initMap')
			}
			@if(Request::segment(2) == 'orders')
			$('.image-set').lightbox();
			@endif
				
			console.log($(".2023-02-05").remove());
		</script>			

	</body>
</html>