<?php

const INGREDIENT_BASE_UNIT = [
    'g',
    'ml',
    'piece',
    'stalk',
    'bottle',
    'pack',
];

const PACKAGE_STATUS = [
    'open',
    'used_up',
    'expired',
    'discarded'
];

const RECORD_STATUS = [
    'active',
    'inactive',
];

const RECORD_STATUS_DEFAULT = 'active';

const INVENTORY_ACTION_TYPES = [
    'receive',
    'open',
    'discard',
    'expire'
];

const ORDER_STATUSES = [
    'draft',
    'confirmed',
    'paid',
    'completed',
    'cancelled'
];

const ORDER_STATUS_DEFAULT = 'draft';
