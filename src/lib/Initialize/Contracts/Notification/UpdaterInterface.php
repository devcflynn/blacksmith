<?php namespace Contracts\Notification;

/**
 * Handles creator events
 */

use Contracts\Instances\InstanceInterface;
use Validators\Validator;

interface UpdaterInterface
{
    /**
     * Handle the updateSucceeded event
     *
     * @param InstanceInterface $instance
     *
     * @return mixed
     */
    public function updateSucceeded(InstanceInterface $instance);

    /**
     * Handle the updateFailed event
     *
     * @param InstanceInterface $instance
     * @param Validator         $validator
     *
     * @return mixed
     */
    public function updateFailed(InstanceInterface $instance, Validator $validator);
} 