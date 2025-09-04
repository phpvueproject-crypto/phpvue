<?php

namespace App\Repositories;

use Symfony\Component\Process\Process;

class DeviceRepository {
    public function startAllProcess(): void {
        $processNames = [
            'amdr-status-sync-continuous:*',
            'amdr-map-sync-continuous:*',
            'amdr-mission-sync-continuous:*',
            'amdr-queue-sync-continuous:*',
            'amdr-process-status-change-listener:*'
        ];
        $command = array_merge(['supervisorctl', 'start'], $processNames);
        $process = new Process($command);
        $process->run();
    }

    public function stopAllProcess(): void {
        $processNames = [
            'amdr-status-sync-continuous:*',
            'amdr-map-sync-continuous:*',
            'amdr-mission-sync-continuous:*',
            'amdr-queue-sync-continuous:*',
            'amdr-process-status-change-listener:*'
        ];
        $command = array_merge(['supervisorctl', 'stop'], $processNames);
        $process = new Process($command);
        $process->run();
    }
}
