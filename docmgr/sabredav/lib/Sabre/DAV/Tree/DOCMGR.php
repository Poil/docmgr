<?php

namespace Sabre\DAV\Tree;

use Sabre\DAV;

/**
 * FileSystem Tree
 *
 * This class is an alternative to the standard ObjectTree. This tree can only
 * use Sabre\DAV\DOCMGR\Directory and File classes, but as a result it allows for a few
 * optimizations that otherwise wouldn't be possible.
 *
 * Specifically copying and moving are much, much faster.
 *
 * @copyright Copyright (C) 2007-2012 Rooftop Solutions. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class DOCMGR extends DAV\Tree {

    /**
     * Base url on the filesystem.
     *
     * @var string
     */
    protected $basePath;

    /**
     * Creates this tree
     *
     * Supply the path you'd like to share.
     *
     * @param string $basePath
     */
    public function __construct($basePath) {

        $this->basePath = $basePath;

    }

    /**
     * Returns a new node for the given path
     *
     * @param string $path
     * @return DAV\DOCMGR\Node
     */
    public function getNodeForPath($path) {

        $realPath = $this->getRealPath($path);

        $d = new \EDAV($realPath);

        if ($d->objectId==null)
        {
            throw new DAV\Exception\NotFound('File at location ' . $realPath . ' not found');
        }


        if ($d->objectInfo["object_type"]=="collection")
        {
            return new DAV\DOCMGR\Directory($realPath);
        } 
        else 
        {
            return new DAV\DOCMGR\File($realPath);
        }

    }

    /**
     * Returns the real filesystem path for a webdav url.
     *
     * @param string $publicPath
     * @return string
     */
    protected function getRealPath($publicPath) {

        return rtrim($this->basePath,'/') . '/' . trim($publicPath,'/');

    }

    /**
     * Copies a file or directory.
     *
     * This method must work recursively and delete the destination
     * if it exists
     *
     * @param string $source
     * @param string $destination
     * @return void
     */
    public function copy($source,$destination) 
    {

        $source = $this->getRealPath($source);
        $destination = $this->getRealPath($destination);
        $this->realCopy($source,$destination);

    }

    /**
     * Used by self::copy
     *
     * @param string $source
     * @param string $destination
     * @return void
     */
    protected function realCopy($source,$destination) {

        if (is_file($source)) {
            copy($source,$destination);
        } else {
            mkdir($destination);
            foreach(scandir($source) as $subnode) {

                if ($subnode=='.' || $subnode=='..') continue;
                $this->realCopy($source.'/'.$subnode,$destination.'/'.$subnode);

            }
        }

    }

    /**
     * Moves a file or directory recursively.
     *
     * If the destination exists, delete it first.
     *
     * @param string $source
     * @param string $destination
     * @return void
     */
    public function move($source,$destination) 
    {

        $time1 = microtime();
        
        $source = $this->getRealPath($source);
        $destination = $this->getRealPath($destination);

        if (dirname($source) == dirname($destination))
        {

          $opt = null;
          $opt["path"] = $source;
          $opt["name"] = basename($destination);
          
          $d = new \EDAV_OBJECT($opt);
          $d->save();

          if ($d->getError())
          {
            throw new DAV\Exception\Conflict($d->getError());
          }
          
        }
        else
        {

          $opt = null; 
          $opt["path"] = $source;
          $opt["source_parent_path"] = dirname($source);
          $opt["dest_parent_path"] = dirname($destination);
          
          $d = new \EDAV_OBJECT($opt);
          $d->move();

          if ($d->getError())
          {
            throw new DAV\Exception\Conflict($d->getError());
          }
          
        
        }

        $time2 = microtime();
        
        $diff = $time2 - $time1;
        
        file_put_contents("/tmp/time",$diff);
    
    }

}

