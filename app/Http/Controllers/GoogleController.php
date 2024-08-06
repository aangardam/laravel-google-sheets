<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Sheets;
use App\Models\Employee;

class GoogleController extends Controller
{
    public function index(){
        // $values = Sheets::spreadsheet('1TmwsIeeQFxzxokS6qKS_TecDUlojGm-CQIYKnMa_U4U')->sheet('Sheet1')->all();
        // $values = Sheets::spreadsheet('16X5J7nsFLBpz8Hnx1ezVIpZOcnKDtdJUKKuqrJ7FnxI')->sheet('Sheet1')->all();
        // return $values;
        $data = Employee::all();
        return view('index', compact('data'));
    }

    public function save(Request $request){
        $clientId = $request->clientId;
        $sheetName = $request->name;
        $rows  = Sheets::spreadsheet($clientId)->sheet($sheetName)->get();

        // Check Header
        $expectedHeaders = ["No", "NIK", "Name", "Address", "Gender"];
        $headers = $rows[0];

        if(count($headers) != 5){
            $data = [
                'code'=>500,
                'message'=>'Gagal melakukan singkronisasi, karena file tidak sesuai'
            ];
            return $data;
        }

        // Verify headers
        foreach ($expectedHeaders as $index => $expectedHeader) {
            if ($headers[$index] != $expectedHeader) {
                return response()->json([
                    'code' => 500,
                    'message' => 'Gagal melakukan singkronisasi, karena file tidak sesuai'
                ]);
            }
        }

        $header = $rows->pull(0);
        $values = Sheets::collection($header, $rows);
        $data = array_values($values->toArray());
        foreach ($data as $key => $value) {
            Employee::create([
                'nik' => $value['NIK'],
                'name' => $value['Name'],
                'address' => $value['Address'],
                'gender' => $value['Gender']
            ]);
        }
        return response()->json([
            'code' => 201,
            'message' => 'Berhasil melakukan singkronisasi',
        ]);
    }
}
