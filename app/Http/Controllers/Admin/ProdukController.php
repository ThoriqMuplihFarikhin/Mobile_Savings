<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ProdukController extends Controller
{
    public function index()
    {
        return view('pages.admin.produk');
    }
}
