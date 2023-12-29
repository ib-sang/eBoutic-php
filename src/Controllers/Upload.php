<?php

namespace Controllers;

use Intervention\Image\ImageManager;
use Psr\Http\Message\UploadedFileInterface;

class Upload
{

    
    /**
     * path
     *
     * @var mixed
     */
    protected $path;
        
    /**
     * format
     *
     * @var array
     */
    protected $format=[];

    public function __construct(?string $path = null)
    {
        if ($path) {
            $this->path = $path;
        }
    }
    
    /**
     * upload
     *
     * @param  UploadedFileInterface $file
     * @param  string/null $oldfile
     * @return void
     */
    public function upload(UploadedFileInterface $file, ?string $oldfile = null):?string
    {
        if ($file->getError()===UPLOAD_ERR_OK) {
            $this->delete($oldfile);
            $targetpath=$this->addCopySuffix($this->path.DIRECTORY_SEPARATOR.$file->getClientFilename());
            $dirname=pathinfo($targetpath, PATHINFO_DIRNAME);
            if (!file_exists($dirname)) {
                mkdir($dirname, 777, true);
            }
            $file->moveTo($targetpath);
            $this->generateFormats($targetpath);

            return pathinfo($targetpath)['basename'];
        }
        return null;
    }

    
    /**
     * delete
     *
     * @param  string/null $oldfile
     * @return void
     */
    public function delete(?string $oldfile = null)
    {
        if ($oldfile) {
            $oldfile=$this->path.'/'.$oldfile;
            if (file_exists($oldfile)) {
                unlink($oldfile);
            }
            foreach ($this->format as $format => $_) {
                $oldfileformat=$this->getPathWithSuffix($oldfile, $format);
                if (file_exists($this->getPathWithSuffix($oldfile, $format))) {
                    unlink($oldfileformat);
                }
            }
        }
    }
    
    /**
     * getPathWithSuffix
     *
     * @param  string $path
     * @param  string $suffix
     * @return string
     */
    public function getPathWithSuffix(string $path, string $suffix):string
    {
        $info=pathinfo($path);
        return $info['dirname'].'/'.$info['filename'].'_'.$suffix.'.'.$info['extension'];
    }
    
    /**
     * generateFormats
     *
     * @param  string $targetpath
     * @return void
     */
    public function generateFormats(string $targetpath)
    {
        $copySuffix = [];
        foreach ($this->format as $format => $size) {
            $manager=new ImageManager(['driver'=>'gd']);
            $destination=$this->getPathWithSuffix($targetpath, $format);
            [$width,$height]=$size;
            $manager->make($targetpath)->fit($width, $height)->save($destination);
            $this->addCopySuffix($targetpath);
        }
    }
    
    /**
     * addCopySuffix
     *
     * @param  string $targetpath
     * @return string
     */
    public function addCopySuffix(string $targetpath):string
    {
        if (file_exists($targetpath)) {
            return $this->addCopySuffix($this->getPathWithSuffix($targetpath, 'copy'));
        }
        return $targetpath;
    }
}
