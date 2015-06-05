<?php

/*
 * Highy Integrated Development.
 *
 * @link      https://hidev.me/
 * @package   hidev
 * @license   BSD 3-clause
 * @copyright Copyright (c) 2015 HiQDev
 */

namespace hidev\goals;

use Yii;
use hidev\components\File as FileComponent;
use hidev\helpers\Helper;

/**
 * A File Goal.
 */
class File extends Base
{

    /**
     * @var array|File the file to be handled.
     */
    protected $_file;

    protected $_template;

    public function setTemplate($template)
    {
        $this->_template = $template;
    }

    public function getTemplate()
    {
        return Helper::file2template($this->_template ?: $this->name);
    }

    /**
     * Returns file object.
     * Instantiates it if necessary.
     *
     * @return FileComponent
     */
    public function getFile()
    {
        if (!is_object($this->_file)) {
            if (!is_array($this->_file)) {
                $this->_file = [
                    'path'  => $this->_file ?: $this->name,
                ];
            }
            $this->_file = Yii::createObject(array_merge([
                'class'     => FileComponent::className(),
                'template'  => $this->getTemplate(),
                'goal'      => $this,
            ], $this->_file));
        }

        return $this->_file;
    }

    /**
     * Sets file with given info.
     *
     * @param mixed $info could be anything that is good for FileComponent::create
     */
    public function setFile($info)
    {
        $this->_file = $info;
    }

    public function getDirname()
    {
        return $this->getFile()->getDirname();
    }

    public function getPath()
    {
        return $this->getFile()->getPath();
    }

    static public function exists($path)
    {
        return FileComponent::exists($path);
    }

    public function read()
    {
        return $this->getFile()->read();
    }

    public function readArray()
    {
        return $this->getFile()->readArray();
    }

    public function load()
    {
        $this->mset($this->getFile()->load());
    }

    public function save()
    {
        return $this->getFile()->save($this);
    }

}
