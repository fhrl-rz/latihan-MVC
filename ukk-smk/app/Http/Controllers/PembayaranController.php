<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Pembayaran;
use App\Siswa;
use App\Spp;
use App\User;

class PembayaranController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //ambil data max 10
        $data = Pembayaran::paginate(10);
        //membuat variabel tampil yang diisi dengan data
        foreach ($data as $item) {
            $item->siswa = Siswa::find($item->nisn);
            $item->spp = Spp::find($item->id_spp);
            $item->user = User::find($item->id_user);
        }
        $tampil['data'] = $data;
        //tampilkan resources/views/pembayaran/index.blade.php
        return view("pembayaran.index", $tampil);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //tampilkan resources/views/siswa/create.blade.php
        $siswa = Siswa::get();
        foreach ($siswa as $item) {
            $spp = Spp::find($item->id_spp);
            $item->options = $item->nisn." ".$item->nama." | SPP ".$spp->tahun." - ".$spp->nominal;
        }
        $data['siswa'] = $siswa;
        return view("pembayaran.create", $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //ambil data spp
        $id_spp = Siswa::find($request->nisn)->id_spp;
        $spp = Spp::find($id_spp);
        //validasi inputan
        $this->validate($request, [
        'tgl_bayar' => 'required|date',
        'nisn' => 'required',
        'jumlah_bayar' => 'required|lt:'.$spp->nominal,
        ]);
        //isi id_user dengan id user yang login
        $id_user = Auth::user()->id;
        $request->merge(['id_user' => $id_user ]);
        //isi id_spp dengan id_spp milik siswa
        $id_spp = Siswa::find($request->nisn)->id_spp;
        $request->merge(['id_spp' => $id_spp ]);
        $data = Pembayaran::create($request->all());
        return redirect()->route("pembayaran.index")->with(
            "success",
            "Data berhasil disimpan."
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $pembayaran
     * @return \Illuminate\Http\Response
     */
    public function show($pembayaran)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $pembayaran
     * @return \Illuminate\Http\Response
     */
    public function edit($pembayaran)
    {
        $data = Pembayaran::findOrFail($pembayaran);
        $siswa = Siswa::get();
        foreach ($siswa as $item) {
            $spp = Spp::find($item->id_spp);
            $item->options = $item->nisn." ".$item->nama." | SPP ".$spp->tahun." - ".$spp->nominal;
        }
        $data->siswa = $siswa;

        //tampilkan resources/views/pembayaran/edit.blade.php
        return view("pembayaran.edit", $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $pembayaran
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $pembayaran)
    {
        //ambil data spp
        $id_spp = Siswa::find($request->nisn)->id_spp;
        $spp = Spp::find($id_spp);
        //validasi inputan
        $this->validate($request, [
        'tgl_bayar' => 'required|date',
        'nisn' => 'required',
        'jumlah_bayar' => 'required|lt:'.$spp->nominal,
        ]);
        $data = Pembayaran::findOrFail($pembayaran);
        $data->nisn = $request->nisn;
        $data->tgl_bayar = $request->tgl_bayar;
        //isi id_spp dengan id_spp milik siswa
        $id_spp = Siswa::find($request->nisn)->id_spp;
        $data->id_spp = $id_spp;
        $data->jumlah_bayar = $request->jumlah_bayar;
        $data->save();

        return redirect()->route("pembayaran.index")->with(
            "success",
            "Data berhasil diubah."
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $pembayaran
     * @return \Illuminate\Http\Response
     */
    public function destroy($pembayaran)
    {
        $data = Pembayaran::findOrFail($pembayaran);
        $data->delete();
    }
    public function history()
    {
        //cek user
        $user = Auth::user();
        if ($user->hak_akses=="siswa") {
            //ambil data berdasarkan nisn maksimal 10 data
            $siswa = Siswa::where("id_user", $user->id)->first();
            $nisn = $siswa->nisn;
            $data = Pembayaran::where("nisn", $nisn)->paginate(10);
        } else {
            //ambil data maksimal 10 data
            $data = Pembayaran::paginate(10);
        }

        //membuat variabel tampil yang diisi dengan data
        foreach ($data as $item) {
            $item->siswa = Siswa::find($item->nisn);
            $item->spp = Spp::find($item->id_spp);
            $item->user = User::find($item->id_user);
        }
        $tampil['data'] = $data;
        //tampilkan resources/views/pembayaran/history.blade.php beserta variabel tampil
        return view("pembayaran.histori", $tampil);
    }
}