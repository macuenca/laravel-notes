<?php

namespace App\Http\Controllers;

use App\Note;
use App\Tag;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

/**
 * Notes endpoint
 *
 * Used to perform all operations on notes
 */
class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::guard('api')->user();
        $notes = $user->notes()->get();

        return $notes->toJson();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'message' => 'required',
            'tags' => 'max:255'
        ]);

        $user = Auth::guard('api')->user();
        $note = $user->notes()->create([
            'message' => $request->message,
        ]);

        // Tags are comma separated
        $tags = explode(',', $request->tags);
        foreach ($tags as $tag) {
            $tagObject = Tag::updateOrCreate(['name' => trim($tag)]);
            $note->tags()->attach($tagObject->id);
        }

        // Return the newly created object
        return $note->toJson();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Only retrieve notes for the authenticated user
        $user = Auth::guard('api')->user();
        $note = Note::where(['id' => $id, 'user_id' => $user->id])->firstOrFail();

        return $note->toJson();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'message' => 'required',
            'tags' => 'max:255'
        ]);

        // Only retrieve notes for the authenticated user
        $user = Auth::guard('api')->user();
        $note = Note::where(['id' => $id, 'user_id' => $user->id])->firstOrFail();
        $note->message = $request->message;
        $note->save();

        // Remove existing tags from the note
        Note::find($note->id)->tags()->detach();

        // Tags are comma separated
        $tags = explode(',', $request->tags);
        foreach ($tags as $tag) {
            $tagObject = Tag::updateOrCreate(['name' => trim($tag)]);
            $note->tags()->attach($tagObject->id);
        }

        // Return the updated object
        return $note->toJson();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Only delete notes for the authenticated user
        $user = Auth::guard('api')->user();
        $note = Note::where(['id' => $id, 'user_id' => $user->id])->firstOrFail();
        $note->delete();

        // Return the list of notes if successful
        return $this->index();
    }
}
