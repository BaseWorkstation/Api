<?php

namespace App\Repositories;

use App\Http\Resources\File\FileResource;
use App\Http\Resources\File\FileCollection;
use App\Events\File\NewFileUploadedEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JD\Cloudder\Facades\Cloudder;
use Illuminate\Support\Facades\Storage;
use App\Models\File;
use App\Models\Team;
use App\Models\User;
use App\Models\Workstation;
use Carbon\Carbon;

class FileRepository
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\File\FileCollection
     */
    public function index(Request $request)
    {
        // save request details in variables
        $user_id = $request->user_id;
        $workstation_id = $request->workstation_id;
        $request->from_date? 
            $from_date = $request->from_date."T00:00:00.000Z": 
            $from_date = Carbon::createFromFormat('Y-m-d H:i:s', '2020-01-01 00:00:00');
        $request->to_date? 
            $to_date = $request->to_date."T23:59:59.000Z": 
            $to_date = Carbon::now();

        // fetch Files from db using filters when they are available in the request
        $files = File::when($from_date, function ($query, $from_date) {
                            return $query->whereDate('created_at', '>=', $from_date );
                        })
                        ->when($to_date, function ($query, $to_date) {
                            return $query->whereDate('created_at', '<=', $to_date );
                        })
                        ->when($user_id, function ($query, $user_id) {
                            return $query->where('user_id', $user_id);
                        })
                        ->when($workstation_id, function ($query, $workstation_id) {
                            return $query->where('workstation_id', $workstation_id);
                        })
                        ->latest();

        // if user asks that the result be paginated
        if ($request->filled('paginate') && $request->paginate) {
            return new FileCollection($files->paginate($request->paginate_per_page)->withPath('/'));
        }

        // return collection
        return new FileCollection($files->get());
    }

    /**
     * store a new File.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\File\FileResource
     */
    public function store(Request $request)
    {
        // creates string to match specific upload method e.g. "localUpload" or "cloudUpload"
        $upload_method = env("APP_INSTALLATION_LOCATION")."Upload";

        // check if model has file of the upload category

        // check if workstation has previous logo, then update with the new one
        if ($request->upload_category == "workstation_logo") {
            $workstation = Workstation::findOrFail($request->workstation_id);
            if ($workstation->logo !== null) {
                return $this->$upload_method($request, $workstation->logo);
            }
        }

        // check if team has previous logo, then update with the new one
        if ($request->upload_category == "team_logo") {
            $team = Team::findOrFail($request->team_id);
            if ($team->logo !== null) {
                return $this->$upload_method($request, $team->logo);
            }
        }

        // check if user has previous avatar, then update with the new one
        if ($request->upload_category == "user_avatar") {
            $user = User::findOrFail($request->user_id);
            if ($user->avatar !== null) {
                return $this->$upload_method($request, $user->avatar);
            }
        }

        // else create a new upload
        return $this->newUpload($request, $upload_method);
    }

    /**
     * upload new file
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $upload_method
     * @return App\Repository\FileRepository
     */
    public function newUpload(Request $request, $upload_method)
    {
        if ($request->file()) {

            $fileModel = new File;
            $fileModel->save();

            $fileModel = $this->$upload_method($request, $fileModel);

            // call event that a new FileUpload has been created
            event(new NewFileUploadedEvent($request, $fileModel));

            $fileModel->refresh();

            return $fileModel;
        }
    }

    /**
     * upload if app is installed on the cloud
     * @param  \Illuminate\Http\Request  $request
     * @param App\Models\File $fileModel
     * @return App\Models\File
     */
    public function cloudUpload(Request $request, File $fileModel)
    {
        $fileName = time().'_'.$request->upload_category;
        $fileNameWithExtension = $fileName.'.'.$request->file->getClientOriginalExtension();
        $file = $request->file;
        $path = $file->getPathname();
        $options = array("public_id" => $fileName);
        $tags = array( env('APP_NAME'), $request->upload_category);

        // delete user previous avatar on cloud if there's any
        if ($request->upload_category == "user_avatar") {
            // get user instance
            $user = User::findOrFail($request->user_id);

            if ($user->avatar !== null) {
                Cloudder::destroyImage($user->avatar->file_path);
            }
        }

        // delete workstation previous logo on cloud if there's any
        if ($request->upload_category == "workstation_logo") {
            // get workstation instance
            $workstation = Workstation::findOrFail($request->workstation_id);

            if ($workstation->logo !== null) {
                Cloudder::destroyImage($workstation->logo->file_path);
            }
        }

        // delete team previous logo on cloud if there's any
        if ($request->upload_category == "team_logo") {
            // get team instance
            $team = Team::findOrFail($request->team_id);

            if ($team->logo !== null) {
                Cloudder::destroyImage($team->logo->file_path);
            }
        }

        // upload new image on cloud
        $cloudder = Cloudder::upload($path, $fileName, $options, $tags);
        $image_result = Cloudder::getResult();

        $fileModel->name = $fileNameWithExtension;
        $fileModel->file_path = str_replace('http://', 'https://', $image_result['url']);
        $fileModel->storage_environment = env("APP_INSTALLATION_LOCATION");
        $fileModel->save();
        $fileModel->refresh();

        return $fileModel;
    }

    /**
     * upload if app is installed locally
     * @param  \Illuminate\Http\Request  $request
     * @param App\Models\File $fileModel
     * @return App\Models\File
     */
    public function localUpload(Request $request, File $fileModel)
    {
        // delete user previous avatar locally if there's any
        if ($request->upload_category == "user_avatar") {
            // get user instance
            $user = User::findOrFail($request->user_id);

            if ($user->avatar !== null) {
                Storage::delete($user->avatar->file_path);
            }
        }

        // delete workstation previous logo locally if there's any
        if ($request->upload_category == "workstation_logo") {
            // get workstation instance
            $workstation = Workstation::findOrFail($request->workstation_id);

            if ($workstation->logo !== null) {
                Storage::delete($workstation->logo->file_path);
            }
        }

        // delete team previous logo locally if there's any
        if ($request->upload_category == "team_logo") {
            // get team instance
            $team = Team::findOrFail($request->team_id);

            if ($team->logo !== null) {
                Storage::delete($team->logo->file_path);
            }
        }

        // proceed with uploading
        $fileName = time().'_'.$request->upload_category.'.'.$request->file->getClientOriginalExtension();

        $filePath = $request->file('file')->storeAs('uploads/'.$request->upload_category, $fileName, 'public');
        $fileModel->name = $fileName;
        $fileModel->file_path = 'public/' . $filePath;
        $fileModel->storage_environment = env("APP_INSTALLATION_LOCATION");
        $fileModel->save();
        $fileModel->refresh();

        return $fileModel;
    }

    /**
     * handle the polymorphic relationship of the newly uploaded file, i.e. whether its an avatar for a user or logo for a workstation
     * @param  \Illuminate\Http\Request  $request
     * @param App\Models\File $fileModel
     * @return App\Models\File
     */
    public function handlePolymorphicRelationship(Request $request, File $file)
    {
        if ($request->upload_category == "team_logo") {
            $team = Team::findOrFail($request->team_id);
            $team->logo()->save($file);

            return $file;
        };

        if ($request->upload_category == "workstation_logo") {
            $workstation = Workstation::findOrFail($request->workstation_id);
            $workstation->logo()->save($file);

            return $file;
        };

        if ($request->upload_category == "workstation_image") {
            $workstation = Workstation::findOrFail($request->workstation_id);
            $workstation->images()->save($file);

            return $file;
        };

        if ($request->upload_category == "user_avatar") {
            $user = User::findOrFail($request->user_id);
            $user->avatar()->save($file);

            return $file;
        };
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Resources\File\FileResource
     */
    public function show($id)
    {
        // return resource
        return new FileResource(File::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Resources\File\FileResource
     */
    public function update(Request $request, $id)
    {
        // find the instance
        $file = $this->getFileById($id);

        // remove or filter null values from the request data then update the instance
        $file->update(array_filter($request->all()));

        // return resource
        return new FileResource($file);
    }

    /**
     * find a specific File using ID.
     *
     * @param  int  $id
     * @return \App\Models\File
     */
    public function getFileById($id)
    {
        // find and return the instance
        return File::findOrFail($id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return void
     */
    public function destroy($id)
    {
        // softdelete instance
        $this->getFileById($id)->delete();
    }
}
