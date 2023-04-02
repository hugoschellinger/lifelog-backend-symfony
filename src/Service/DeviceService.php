<?php

namespace App\Service;

use App\Repository\DeviceRepository;

class DeviceService extends AbstractService
{
    public function __construct(DeviceRepository $DeviceRepository)
    {
        parent::__construct($DeviceRepository);
    }
}