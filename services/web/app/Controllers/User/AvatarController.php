<?php

namespace App\Controllers\User;

class AvatarController extends BaseUserController
{
    public function show($filename)
    {
        $path = WRITEPATH . 'uploads/avatars/' . $filename;
        
        if (!file_exists($path)) {
            // Provide a default avatar or 404
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $mime = mime_content_type($path);
        
        return $this->response
                    ->setContentType($mime)
                    ->setBody(file_get_contents($path));
    }
}
