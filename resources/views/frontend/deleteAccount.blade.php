@extends('frontend.layout.body')
<div style="background-image:url({{asset('public/frontend/src/imgs/headerBackground.png')}});padding: 100px 15px;">
    <div class="text-center">
        <img style="max-width:200px;text-align:center;" src="{{asset('public/frontend/src/imgs/logo.png')}}">
    </div>
    <div class="row justify-content-center">
        <div class="col-8" style="">
            @if(session()->has('messageDeleted'))
                <div class="alert alert-primary" role="alert">
                {{ session()->get('messageDeleted') }}
                </div>
            @endif
            <form method="post" action="{{ url('delete_account_request') }}">
                {{ csrf_field() }}
            <div class="form-group">
                <label style="color:#fff" for="exampleInputName">Name</label>
                <input type="text" class="form-control" name="name" id="exampleInputName" >
            </div>
            <div class="form-group">
                <label style="color:#fff" for="exampleInputEmail">Email address</label>
                <input type="email" class="form-control" name="email" id="exampleInputEmail" >
            </div>
            <div class="form-group">
                <label style="color:#fff" for="exampleInputPhone">Phone</label>
                <input type="text" class="form-control" name="phone" id="exampleInputPhone" >
            </div>
            <div class="form-group">
                <label style="color:#fff" for="exampleInputPassword">Password</label>
                <input type="password" class="form-control" name="password" id="exampleInputPassword">
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
</div>