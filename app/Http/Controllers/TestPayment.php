<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PaymentService ;
use Moyasar\Facades\Payment;
use Moyasar\Facades\Invoice;
use View;
use Config;

class TestPayment extends Controller
{

    public function payForm()
    {
        return view('online_pay.form-test');
    }



    
}
