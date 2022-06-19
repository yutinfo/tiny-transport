<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Amphure;
use Illuminate\Http\Request;
use App\Http\Resources\AmphureResource;
use Illuminate\Support\Arr;

class AmphureController extends Controller
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
                'id',"code","province_id"
            ]
        );
       $model =  Amphure::where($param)->get();
       return AmphureResource::collection($model);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $model =  Amphure::find($id);
        return new AmphureResource($model);
    }
}

