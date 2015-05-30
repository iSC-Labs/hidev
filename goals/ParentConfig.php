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
use yii\base\InvalidParamException;
use hidev\helpers\Helper;

/**
 * The Config. Keeps the Goals.
 */
class ParentConfig extends File
{
    use \Robo\Task\Vcs\loadTasks;

    public $name;

    protected $_file = '@parent/config.json'; 

    protected $_defined;

    protected $_github;

    public function setGithub($github)
    {
        if ($this->_defined) {
            throw new InvalidParamException("Already defined: " . $this->_defined);
        }
        $this->_github  = $github;
        $this->_defined = 'github';
        if (File::exists('@parent')) {
            return;
        }
        $task = $this->taskGitStack()
            ->stopOnFail()
            ->cloneRepo("git@github.com:$parent", $path)
        ;
        if (!$task->run()->wasSuccessful()) {
            throw new InvalidParamException("Failed clone parent");
        }
    }

    public function getGithub()
    {
        return $this->_github;
    }

    public function getDefined()
    {
        return $this->_defined;
    }

}
