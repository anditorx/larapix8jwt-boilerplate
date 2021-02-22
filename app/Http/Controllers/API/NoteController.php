<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $notes = Note::all();
        // dd($notes);
        return ResponseFormatter::success($notes, 'File successfully uploaded');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        if ($request->file('picture_path')) {
        $data['picture_path'] = $request->file('picture_path')->store('assets/note','public');
        }

        Note::create($data);

        $note = Note::where('title', $request->title)->first();
        return ResponseFormatter::success([
            'note' => $note, 
        ],'Successfully create note');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $note = Note::find($id);
  
        if (is_null($note)) {
            return ResponseFormatter::error([
                'message' => 'Not Found',
            ], 'Not Found', 404);
        }
   
        return ResponseFormatter::success([
            'note' => $note, 
        ],'Successfully show note');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Note $note)
    {
        $input = $request->all();
        
        $validator = Validator::make($input, [
            'title' => 'required',
            'description' => 'required'
        ]);
        
        if($validator->fails()){
            return ResponseFormatter::error([
                'error' => $validator->errors()
            ], 'Validation Error', 401);       
        }

        // $note->title = $input['title'];
        // $note->description = $input['description'];
        // $note->save();
        $note->update($input);

        return ResponseFormatter::success($note, 'Successfully updated note');
    }

    public function updatePhoto(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|max:2048'
        ]);

        if($validator->fails()){
            return ResponseFormatter::error([
                'error' => $validator->errors()
            ], 'Update photo failed', 401);
        }

        if($request->file()){
            $file = $request->file->store('assets/user', 'public');

            // save url photo to database
            $note = Note::find($id);
            // dd($note);

            $note->picture_path = $file;
            $note->update();

            return ResponseFormatter::success([$file], 'File successfully uploaded');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Note $note)
    {
        // dd($note);
        $note->delete();
        return ResponseFormatter::success(null, 'Successfully delete note');

    }
}
