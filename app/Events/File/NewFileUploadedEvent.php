<?php

namespace App\Events\File;

use Illuminate\Http\Request;
use App\Models\File;

class NewFileUploadedEvent
{

    /**
     * Public declaration of variables.
     *
     * @var Request $request
     * @var  File $file
     */
    public $request;
    public $file;

    /**
     * Dependency Injection of variables
     *
     * @param Request $request
     * @param File $file
     * @return void
     */
    public function __construct(Request $request, File $file)
    {
        $this->request = $request;
        $this->file = $file;
    }
}