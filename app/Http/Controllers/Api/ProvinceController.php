<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Province;
use Illuminate\Http\Request;
use App\Http\Resources\ProvinceResource;

class ProvinceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $param = $request->only(
            [
                'id',"code","geography_id"
            ]
        );
       $model =  Province::where($param)->get();
       return ProvinceResource::collection($model);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $model =  Province::find($id);
        return new ProvinceResource($model);
    }
}

