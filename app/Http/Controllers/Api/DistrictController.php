<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Http\Request;
use App\Http\Resources\DistrictResource;

class DistrictController extends Controller
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
                'id',"zip_code","amphure_id"
            ]
        );
       $model =  District::where($param)->get();
       return DistrictResource::collection($model);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $model =  District::find($id);
        return new DistrictResource($model);
    }
}

