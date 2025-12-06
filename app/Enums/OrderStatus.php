<?php

namespace App\Enums;

class OrderStatus
{
    const PENDING = 'В обработке';
    const CONFIRMED = 'Подтвержден';
    const COOKING = 'Готовится';
    const READY = 'Готов к выдаче';
    const COMPLETED = 'Выдан';
    const CANCELLED = 'Отменен';

    /**
     * Получить все статусы
     *
     * @return array
     */
    public static function all(): array
    {
        return [
            self::PENDING,
            self::CONFIRMED,
            self::COOKING,
            self::READY,
            self::COMPLETED,
            self::CANCELLED,
        ];
    }

    /**
     * Получить активные статусы (не отмененные)
     *
     * @return array
     */
    public static function active(): array
    {
        return [
            self::PENDING,
            self::CONFIRMED,
            self::COOKING,
            self::READY,
            self::COMPLETED,
        ];
    }

    /**
     * Проверить, является ли статус отмененным
     *
     * @param string $status
     * @return bool
     */
    public static function isCancelled(string $status): bool
    {
        return $status === self::CANCELLED;
    }

    /**
     * Проверить, можно ли отменить заказ (восстановить ингредиенты)
     *
     * @param string $status
     * @return bool
     */
    public static function canRestoreIngredients(string $status): bool
    {
        return in_array($status, [self::PENDING, self::CONFIRMED]);
    }

    /**
     * Карта допустимых переходов статусов (state machine)
     */
    public static array $allowedTransitions = [
        self::PENDING   => [self::CONFIRMED, self::CANCELLED],
        self::CONFIRMED => [self::COOKING, self::CANCELLED],
        self::COOKING   => [self::READY, self::CANCELLED],
        self::READY     => [self::COMPLETED, self::CANCELLED],
        self::COMPLETED => [],
        self::CANCELLED => [], // Можно добавить восстановление в PENDING/CONFIRMED если разрешено
    ];

    /**
     * Проверка допустимости перехода статуса
     */
    public static function isAllowedTransition(string $from, string $to): bool
    {
        return in_array($to, self::$allowedTransitions[$from] ?? [], true);
    }
}

