<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    /**
     * Mass assignable attributes.
     *
     * @var array
     */
    protected $fillable = ['message'];

    /**
     * The tags that belong to the note.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany('App\Tag', 'note_tag', 'note_id', 'tag_id');
    }

    /**
     * The user that owns the note.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function toJson($options = 0)
    {
        $array = json_decode(parent::toJson(), true);
        $tags = $this->tags->toArray();
        $array['tags'] = $tags;

        return json_encode($array, $options);
    }
}
