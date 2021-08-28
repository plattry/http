<?php

declare(strict_types = 1);

namespace Plattry\Http\Session;

/**
 * Interface DriverInterface
 * @package Plattry\Http\Session
 */
interface DriverInterface
{
    /**
     * Read session data.
     * @param string $id
     * @return array
     */
    public function read(string $id): array;

    /**
     * Save session data.
     * @param string $id
     * @param array $data
     * @param int $expire
     * @return void
     */
    public function write(string $id, array $data, int $expire): void;

    /**
     * Destroy session data.
     * @param string $id
     * @return void
     */
    public function destroy(string $id): void;
}
