<?php

namespace App\Repositories\Contracts\Admin;

interface OrderRepositoryInterface
{
    /**
     * Get all orders with pagination
     *
     * @param int $perPage
     * @return mixed
     */
    public function getPaginated($perPage = 15);

    /**
     * Get all orders
     *
     * @return mixed
     */
    public function getAll();

    /**
     * Find order by ID
     *
     * @param int $id
     * @return mixed
     */
    public function find($id);

    /**
     * Get order with related data
     *
     * @param int $id
     * @return mixed
     */
    public function getWithDetails($id);

    /**
     * Get orders by status
     *
     * @param string $status
     * @param int $perPage
     * @return mixed
     */
    public function getByStatus($status, $perPage = 15);

    /**
     * Get orders by user
     *
     * @param int $userId
     * @param int $perPage
     * @return mixed
     */
    public function getByUser($userId, $perPage = 15);

    /**
     * Update order status
     *
     * @param int $id
     * @param string $status
     * @return mixed
     */
    public function updateStatus($id, $status);

    /**
     * Update order
     *
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function update($id, array $data);

    /**
     * Get order items
     *
     * @param int $id
     * @return mixed
     */
    public function getItems($id);

    /**
     * Get pending orders count
     *
     * @return int
     */
    public function getPendingCount();

    /**
     * Get orders statistics
     *
     * @return array
     */
    public function getStatistics();

    /**
     * Search orders
     *
     * @param string $query
     * @param int $perPage
     * @return mixed
     */
    public function search($query, $perPage = 15);
}
