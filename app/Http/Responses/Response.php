<?php

namespace App\Http\Responses;

class Response
{
    public static function Success($data,$message,$code=200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status'=> 1,
            'data' => $data,
            'message'=> $message
        ],$code);
    }


    public static function Error($data,$message,$code=500): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status'=> 0,
            'data' => $data,
            'message'=> $message
        ],$code);
    }


    public static function Validation($data,$message,$code=422): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status'=> 1,
            'data' => $data,
            'message'=> $message
        ],$code);
    }
}
