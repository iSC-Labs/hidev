<?php

/*
 * Task runner, code generator and build tool for easier continuos integration
 *
 * @link      https://github.com/hiqdev/hidev
 * @package   hidev
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2014-2016, HiQDev (http://hiqdev.com/)
 */

namespace hidev\controllers;

use hidev\helpers\Helper;
use Yii;

/**
 * Abstract controller.
 */
abstract class AbstractController extends \hidev\base\Controller
{
    protected $_before = [];
    protected $_after  = [];
    protected $_make   = ['load', 'save'];

    /**
     * @var array list of performed actions
     */
    protected $_done = [];

    /**
     * {@inheritdoc}
     */
    public function options($actionId)
    {
        return array_merge(parent::options($actionId), array_keys(Helper::getPublicVars(get_called_class())));
    }

    public function perform()
    {
        return $this->runActions(['before', 'make', 'after']);
    }

    public function actionBefore()
    {
        return $this->runRequests($this->getBefore());
    }

    public function actionMake()
    {
        return $this->runActions($this->getMake());
    }

    public function actionAfter()
    {
        return $this->runRequests($this->getAfter());
    }

    public function actionLoad()
    {
        Yii::trace("Loading nothing for '$this->id'");
    }

    public function actionSave()
    {
        Yii::trace("Saving nothing for '$this->id'");
    }

    public function setBefore($requests)
    {
        $this->_before = array_merge($this->getBefore(), $this->normalizeTasks($requests));
    }

    public function getBefore()
    {
        return $this->_before;
    }

    public function setMake($requests)
    {
        $this->_make = array_merge($this->getMake(), $this->normalizeTasks($requests));
    }

    public function getMake()
    {
        return $this->_make;
    }

    public function setAfter($requests)
    {
        $this->_after = array_merge($this->getAfter(), $this->normalizeTasks($requests));
    }

    public function getAfter()
    {
        return $this->_after;
    }

    public function normalizeTasks($tasks)
    {
        if (!$tasks) {
            return [];
        } elseif (!is_array($tasks)) {
            $tasks = [(string) $tasks => 1];
        }
        $res = [];
        foreach ($tasks as $dep => $enabled) {
            $res[(string) (is_int($dep) ? $enabled : $dep)] = (bool) (is_int($dep) ? 1 : $enabled);
        }

        return $res;
    }

    /**
     * Runs array of requests. Stops on failure and returns exit code.
     * @param null|string|array $requests
     * @return int|Response exit code
     */
    public function runRequests($requests)
    {
        foreach ($this->normalizeTasks($requests) as $request => $enabled) {
            if ($enabled) {
                $res = $this->runRequest($request);
                if (static::isNotOk($res)) {
                    return $res;
                }
            }
        }

        return 0;
    }

    public function runRequest($request)
    {
        return $request === null ? null : $this->module->runRequest($request);
    }

    public static function isNotOk($res)
    {
        return is_object($res) ? $res->exitStatus : $res;
    }

    /**
     * Runs list of actions.
     * TODO: think to redo with runRequests.
     * @param null|string|array $actions
     * @return int|Response exit code
     */
    public function runActions($actions)
    {
        foreach ($this->normalizeTasks($actions) as $action => $enabled) {
            if ($enabled) {
                $res = $this->runAction($action);
                if (static::isNotOk($res)) {
                    return $res;
                }
            }
        }

        return 0;
    }

    public function runAction($id, $params = [])
    {
        if ($this->isDone($id)) {
            return;
        }
        $result = parent::runAction($id, $params);
        $this->markDone($id);

        return $result;
    }

    public function isDone($action, $timestamp = null)
    {
        if ($this->_done[$action]) {
            Yii::trace("Already done: '$this->id/$action'");

            return true;
        }

        return false;
    }

    /**
     * Mark action as already done.
     *
     * @param string $action action id
     * @param int $time microtime when action was done, false for action was not done
     */
    public function markDone($action, $time = null)
    {
        $this->_done[$action] = ($time === null || $time === true) ? microtime(1) : $time;
    }

    /**
     * Runs given binary with given arguments. Returns exit code.
     * @param string $name
     * @param string $args
     * @return int exit code
     */
    public function passthru($name, $args = '')
    {
        return $this->takeGoal('binaries')->passthru($name, $args);
    }

    /**
     * Runs given binary with given arguments. Returns stdout array.
     * @param string $name
     * @param string $args
     * @return array stdout
     */
    public function exec($name, $args = '')
    {
        return $this->takeGoal('binaries')->exec($name, $args);
    }

    public function takeGoal($id)
    {
        return Yii::$app->get('config')->getGoal($id);
    }

    public function takeConfig()
    {
        return Yii::$app->get('config');
    }

    public function takeVendor()
    {
        return $this->takeGoal('vendor');
    }

    public function takePackage()
    {
        return $this->takeGoal('package');
    }

    public function takeVcs()
    {
        return $this->takeConfig()->getVcs();
    }
}
