<?php

namespace App\Http\Controllers\file;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\FileRepository;
use Illuminate\Validation\Rule;
use App\Models\File;

class FileController extends Controller
{
    /**
     * declaration of File repository
     *
     * @var FileRepository
     */
    private $fileRepository;

    /**
     * Dependency Injection of FileRepository.
     *
     * @param  \App\Repositories\FileRepository  $fileRepository
     * @return void
     */
    public function __construct(FileRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Repositories\FileRepository
     */
    public function index(Request $request)
    {
        // run in the repository
        return $this->fileRepository->index($request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Repositories\FileRepository
     * 
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request)
    {
        // authorization
        $this->authorize('create', File::class);

        // validation
        $request->validate([
            "upload_category" => ["required", Rule::in(config('enums.upload_category'))],
            "user_id" => [
                            "required_if:upload_category,user_avatar", 
                            "integer"
                        ],
            "workstation_id" => [
                            "required_if:upload_category,workstation_logo", 
                            "integer"
                        ],
            "team_id" => [
                            "required_if:upload_category,team_logo", 
                            "integer"
                        ],
            "file" => "required|file|mimes:jpeg,png,jpg,gif,svg|max:5048",
        ]);

        // run in the repository
        return $this->fileRepository->store($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \App\Http\Repositories\FileRepository
     */
    public function show($id)
    {
        // run in the repository
        return $this->fileRepository->show($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \App\Http\Repositories\FileRepository
     * 
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, $id)
    {
        // authorization
        $this->authorize('update', File::findOrFail($id));

        // validation
        $request->validate([
            'file_id' => 'required|integer',
            "file" => "required|file|mimes:jpeg,png,jpg,gif,svg|max:5048",
        ]);

        // run in the repository
        return $this->fileRepository->update($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \App\Http\Repositories\FileRepository
     * 
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy($id)
    {
        // authorization
        $this->authorize('delete', File::findOrFail($id));

        // run in the repository
        return $this->fileRepository->destroy($id);
    }
}
