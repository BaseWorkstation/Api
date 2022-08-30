<?php

namespace App\Repositories;

use App\Repositories\Interfaces\UtilityInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UtilityRepository
{
    /**
     * get app enums
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function getEnums(Request $request)
    {
        $categories_data = config('enums');

        $sorted_categories = [];

        foreach ($categories_data as $category_key => $category_value) {
            asort($category_value);
            $sorted_categories[$category_key] = $category_value;
        }

        $result['data'] = $sorted_categories;
        
        return response()->json($result);
    }
}