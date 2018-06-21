<?php

namespace App\Transformers;

use App\Joke;
use App\Transformers\UserTransformer;

use League\Fractal\TransformerAbstract;

class JokeTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Joke $joke)
    {
        return [
            'id'=>$joke->id,
            'title' => $joke->title,
            'joke' => $joke->joke,
            'created_at' => $joke->created_at->toFormattedDateString(),
            'user' => fractal($joke->user, new UserTransformer)
                
        ];
    }
}
