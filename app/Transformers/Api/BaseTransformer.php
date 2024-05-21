<?php

namespace App\Transformers;
// use App\Traits\Restable;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class BaseTransformer
{

    // use Restable;
    /**
     * Method used to transform a collection of items.
     *
     * @param Collection $items The items in a collection.
     *
     * @return Collection The transformed collection.
     */
    public function transformCollection(Collection $items, $method = 'index' ) : Collection
    {
        // return $items->map(function ($item, $method){
        //     return $this->transform($item, $method);
        // });

        $return = [];

        foreach($items as $item)
        {
            $return[] =  $this->transform($item, $method);
        }

        return collect($return);
    }

    /**
     * Method used to transform an item.
     *
     * @param $item mixed The item to be transformed.
     *
     * @return array The transformed item.
     */
    abstract public function transform( $item ) : array;
}