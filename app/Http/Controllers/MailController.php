<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Session;
use App\Http\Requests;
use Mail;
use App\Slider;
use App\CatePost;
use App\Customer;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
session_start();

class MailController extends Controller
{
    public function quen_mat_khau(Request $request){
       //post 
       $category_post=CatePost::orderby('cate_post_id','DESC')->get();
        //slide
        $slider = Slider::orderBy('slider_id','DESC')->where('slider_status','1')->take(4)->get();
        //seo 
        $meta_desc = "Quên mật khẩu"; 
        $meta_keywords = "Quên mật khẩu";
        $meta_title = "Quên mật khẩu";
        $url_canonical = $request->url();
        //--seo
        
    	$cate_product = DB::table('tbl_category_product')->where('category_status','0')->orderby('category_id','desc')->get(); 
        $brand_product = DB::table('tbl_brand')->where('brand_status','0')->orderby('brand_id','desc')->get(); 

        // $all_product = DB::table('tbl_product')
        // ->join('tbl_category_product','tbl_category_product.category_id','=','tbl_product.category_id')
        // ->join('tbl_brand','tbl_brand.brand_id','=','tbl_product.brand_id')
        // ->orderby('tbl_product.product_id','desc')->get();
        
        $all_product = DB::table('tbl_product')->where('product_status','0')->orderby(DB::raw('RAND()'))->paginate(6); 

    	return view('pages.checkout.forget_pass')->with('category',$cate_product)
        ->with('brand',$brand_product)->with('all_product',$all_product)
        ->with('meta_desc',$meta_desc)->with('meta_keywords',$meta_keywords)
        ->with('meta_title',$meta_title)->with('url_canonical',$url_canonical)
        ->with('slider',$slider)->with('category_post',$category_post); //1
        // return view('pages.home')->with(compact('cate_product','brand_product','all_product')); //2
    }
    public function recover_pass(Request $request) {
    $data = $request->all();
    $title_mail = "Lấy lại mật khẩu ";
    $customer = Customer::where('customer_email', '=', $data['email_account'])->get();
    $count_customer = $customer->count();
    foreach($customer as $key => $value){
        $customer_id=$value->customer_id;
    }
    if ($count_customer == 0) {
        return redirect()->back()->with('error', 'Email chưa được đăng ký để khôi phục mật khẩu');
    } else {
        $token_random = Str::random();
        $customer = Customer::find($customer_id);
        $customer->customer_token = $token_random;
        $customer->save();

        // Gửi mail
        $to_email = $data['email_account'];
        $link_reset_pass = url('/update-new-pass?email=' . $to_email . '&token=' . $token_random);
        $data = array("name" => $title_mail, "body" => $link_reset_pass, 'email' => $data['email_account']);

        Mail::send('pages.checkout.forget_pass_notify', ['data' => $data], function($message) use ($title_mail, $data) {
            $message->to($data['email'])->subject($title_mail);
            $message->from($data['email'], $title_mail);
        });

        return redirect()->back()->with('message', 'Gửi mail thành công,vui lòng vào email để reset password');
    }
    }
    public function update_new_pass(Request $request){
       //post 
       $category_post=CatePost::orderby('cate_post_id','DESC')->get();
        //slide
        $slider = Slider::orderBy('slider_id','DESC')->where('slider_status','1')->take(4)->get();
        //seo 
        $meta_desc = "Quên mật khẩu"; 
        $meta_keywords = "Quên mật khẩu";
        $meta_title = "Quên mật khẩu";
        $url_canonical = $request->url();
        //--seo
        
    	$cate_product = DB::table('tbl_category_product')->where('category_status','0')->orderby('category_id','desc')->get(); 
        $brand_product = DB::table('tbl_brand')->where('brand_status','0')->orderby('brand_id','desc')->get(); 

        // $all_product = DB::table('tbl_product')
        // ->join('tbl_category_product','tbl_category_product.category_id','=','tbl_product.category_id')
        // ->join('tbl_brand','tbl_brand.brand_id','=','tbl_product.brand_id')
        // ->orderby('tbl_product.product_id','desc')->get();
        
        $all_product = DB::table('tbl_product')->where('product_status','0')->orderby(DB::raw('RAND()'))->paginate(6); 

    	return view('pages.checkout.new_pass')->with('category',$cate_product)
        ->with('brand',$brand_product)->with('all_product',$all_product)
        ->with('meta_desc',$meta_desc)->with('meta_keywords',$meta_keywords)
        ->with('meta_title',$meta_title)->with('url_canonical',$url_canonical)
        ->with('slider',$slider)->with('category_post',$category_post); //1
        // return view('pages.home')->with(compact('cate_product','brand_product','all_product')); //2
    }
    public function reset_new_pass(Request $request){
    $data = $request->all();
    $token_random = Str::random();
    $customer = Customer::where('customer_email', $data['email'])
                        ->where('customer_token', $data['token'])
                        ->get();
    $count = $customer->count();
    
    if($count > 0){
        foreach($customer as $cus){
            $customer_id = $cus->customer_id;
        }
        $reset = Customer::find($customer_id);
        $reset->customer_password = bcrypt($data['password_account']);
        $reset->customer_token = $token_random;
        $reset->save();
        return redirect('dang-nhap')->with('message', 'Mật khẩu đã cập nhật mới. Quay lại trang đăng nhập');
    } else {
        return redirect('quen-mat-khau')->with('error', 'Vui lòng nhập lại email vì link đã quá hạn');
    }
    }


}
