<?php

namespace App\Http\Livewire;

use App\Pesanan;
use App\PesananDetail;
use App\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProductDetail extends Component
{
    public $product, $nama, $jumlah_pesanan, $nomor;

    public function mount($id)
    {
        $productDetail = Product::find($id);

        if($productDetail) {
            $this->product = $productDetail;
        }
    }

    public function masukkanKeranjang()
    {
        $this->validate([
            'jumlah_pesanan' => 'required'
        ]);
        // Validasi jika belum login
        if(!Auth::user()) {
            return redirect()->route('login');
        }
        // Menghitung Total Harga
        if(!empty($this->nama)) {
            $total_harga = $this->jumlah_pesanan*$this->product->harga+$this->product->harga_nameset;
        }else{
            $total_harga = $this->jumlah_pesanan*$this->product->harga;
        }
        // Mengecek apakah user punya data pesanan utama yang statusnya nol
        $pesanan = Pesanan::where('user_id', Auth::user()->id)->where('status',0)->first();
        // Menyimpan atau update data pesanan utama
        if(empty($pesanan))
        {
            Pesanan::create([
                'user_id' => Auth::user()->id,
                'total_harga' => $total_harga,
                'status' => 0,
                'kode_unik' => mt_rand(100, 999)
            ]);

            $pesanan = Pesanan::where('user_id', Auth::user()->id)->where('status',0)->first();
            $pesanan->kode_pemesanan = 'DC-'.$pesanan->id;
            $pesanan->update();
        }else{
            $pesanan->total_harga = $pesanan->total_harga+$total_harga;
            $pesanan->update();
        }
        // Menyimpan Pesanan Detail
        PesananDetail::create([
            'product_id' => $this->product->id,
            'pesanan_id' => $pesanan->id,
            'jumlah_pesanan' => $this->jumlah_pesanan,
            'namaset' => $this->nama ? true : false,
            'nama' => $this->nama,
            'nomor' => $this->nomor,
            'total_harga' => $total_harga
        ]);
        $this->emit('masukKeranjang');
        session()->flash('message', 'Sukses Masuk Keranjang');
        return redirect()->back();
    }

    public function render()
    {
        return view('livewire.product-detail');
    }
}
